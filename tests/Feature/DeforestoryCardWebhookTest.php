<?php

namespace Tests\Feature;

use App\Models\DeforestoryCard;
use App\Services\DeforestoryApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DeforestoryCardWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'card-webhook-secret';
    private const ENDPOINT = '/api/deforestory/cards';

    /** Payload contoh: 2 card (id + en lengkap). */
    private function cardsPayload(): array
    {
        return [
            'cards' => [
                [
                    'slug' => 'mayawana',
                    'category' => 'pulp',
                    'year' => '2021–2025',
                    'image' => 'https://pasopati.id/storage/deforestory/mayawana.jpg',
                    'title_id' => 'Mayawana: jejak deforestasi',
                    'title_en' => 'Mayawana: deforestation trail',
                    'excerpt_id' => 'Analisis spasial Mayawana.',
                    'excerpt_en' => 'Spatial analysis of Mayawana.',
                    'sort' => 1,
                ],
                [
                    'slug' => 'pulau-laut',
                    'category' => 'sawit',
                    'year' => '2022–2024',
                    'image' => 'https://pasopati.id/storage/deforestory/pulau-laut.jpg',
                    'title_id' => 'Pulau Laut: sawit di balik hutan lindung',
                    'title_en' => 'Pulau Laut: palm oil behind protected forest',
                    'excerpt_id' => 'Pembukaan lahan sawit Pulau Laut.',
                    'excerpt_en' => 'Palm land clearing in Pulau Laut.',
                    'sort' => 2,
                ],
            ],
        ];
    }

    private function signedPost(array $payload, ?string $secret = self::SECRET, ?string $delivery = null, ?string $signatureOverride = null)
    {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $sig = $signatureOverride ?? ('sha256=' . hash_hmac('sha256', $body, $secret));

        $headers = [
            'X-Deforestory-Signature' => $sig,
            'Content-Type' => 'application/json',
        ];
        if ($delivery) {
            $headers['X-Deforestory-Delivery'] = $delivery;
        }

        return $this->call('POST', self::ENDPOINT, [], [], [], $this->transformHeadersToServerVars($headers), $body);
    }

    public function test_valid_signature_stores_cards_full_list_replace(): void
    {
        config(['services.deforestory_api.card_webhook_secret' => self::SECRET]);

        // Seed card lama yang TIDAK ada di payload → harus terhapus (full-list replace).
        DeforestoryCard::create(['slug' => 'lama-dihapus', 'sort' => 0]);

        $response = $this->signedPost($this->cardsPayload(), delivery: Str::uuid()->toString());

        $response->assertStatus(200)->assertJson(['received' => true, 'stored' => 2]);

        // Card lama terhapus; 2 card baru tersimpan by slug.
        $this->assertDatabaseMissing('deforestory_cards', ['slug' => 'lama-dihapus']);
        $this->assertSame(2, DeforestoryCard::count());

        $m = DeforestoryCard::where('slug', 'mayawana')->first();
        $this->assertSame('pulp', $m->category);
        $this->assertSame('Mayawana: jejak deforestasi', $m->title_id);
        $this->assertSame('Mayawana: deforestation trail', $m->title_en);
    }

    public function test_upsert_updates_existing_card_by_slug(): void
    {
        config(['services.deforestory_api.card_webhook_secret' => self::SECRET]);

        // Push pertama.
        $this->signedPost($this->cardsPayload(), delivery: Str::uuid()->toString());

        // Push kedua: ubah category mayawana, hapus pulau-laut dari payload.
        $updated = [
            'cards' => [
                [
                    'slug' => 'mayawana',
                    'category' => 'mining',
                    'year' => '2021–2025',
                    'image' => null,
                    'title_id' => 'Mayawana (baru)',
                    'title_en' => 'Mayawana (new)',
                    'excerpt_id' => 'x',
                    'excerpt_en' => 'y',
                    'sort' => 1,
                ],
            ],
        ];

        $response = $this->signedPost($updated, delivery: Str::uuid()->toString());

        $response->assertStatus(200)->assertJson(['stored' => 1]);

        // mayawana di-update, pulau-laut di-hapus (tidak ada di payload kedua).
        $this->assertSame(1, DeforestoryCard::count());
        $this->assertSame('mining', DeforestoryCard::where('slug', 'mayawana')->value('category'));
        $this->assertSame('Mayawana (baru)', DeforestoryCard::where('slug', 'mayawana')->value('title_id'));
        $this->assertDatabaseMissing('deforestory_cards', ['slug' => 'pulau-laut']);
    }

    public function test_invalid_signature_returns_401(): void
    {
        config(['services.deforestory_api.card_webhook_secret' => self::SECRET]);

        $response = $this->signedPost(
            $this->cardsPayload(),
            signatureOverride: 'sha256=' . str_repeat('0', 64),
            delivery: Str::uuid()->toString()
        );

        $response->assertStatus(401);
        $this->assertSame(0, DeforestoryCard::count());
    }

    public function test_missing_secret_returns_401(): void
    {
        config(['services.deforestory_api.card_webhook_secret' => null]);

        $response = $this->signedPost($this->cardsPayload(), delivery: Str::uuid()->toString());

        $response->assertStatus(401);
        $this->assertSame(0, DeforestoryCard::count());
    }

    public function test_idempotency_same_delivery_dedup(): void
    {
        config(['services.deforestory_api.card_webhook_secret' => self::SECRET]);

        $delivery = Str::uuid()->toString();

        // Push pertama: 2 card.
        $this->signedPost($this->cardsPayload(), delivery: $delivery)->assertStatus(200);
        $this->assertSame(2, DeforestoryCard::count());

        // Push kedua dengan delivery SAMA tapi payload berbeda → dedup, tidak diproses.
        $second = ['cards' => [
            ['slug' => 'lain', 'title_id' => 'Lain', 'title_en' => 'Other', 'excerpt_id' => 'a', 'excerpt_en' => 'b', 'sort' => 9],
        ]];
        $response = $this->signedPost($second, delivery: $delivery);

        $response->assertStatus(200)->assertJson(['received' => true, 'dedup' => true]);

        // Tabel tetap hasil push pertama (2 card mayawana + pulau-laut), 'lain' tidak masuk.
        $this->assertSame(2, DeforestoryCard::count());
        $this->assertDatabaseMissing('deforestory_cards', ['slug' => 'lain']);
    }

    public function test_getcases_reads_local_cards_after_push(): void
    {
        config(['services.deforestory_api.card_webhook_secret' => self::SECRET]);

        $this->signedPost($this->cardsPayload(), delivery: Str::uuid()->toString())->assertStatus(200);

        $idCases = app(DeforestoryApiService::class)->getCases('id');
        $enCases = app(DeforestoryApiService::class)->getCases('en');

        // Urut sort asc → mayawana (sort 1) dulu.
        $this->assertSame('mayawana', $idCases[0]['slug']);
        $this->assertSame('Mayawana: jejak deforestasi', $idCases[0]['title']);
        $this->assertSame('Analisis spasial Mayawana.', $idCases[0]['excerpt']);

        $this->assertSame('Mayawana: deforestation trail', $enCases[0]['title']);
        $this->assertSame('Spatial analysis of Mayawana.', $enCases[0]['excerpt']);

        // Shape card lengkap.
        $this->assertSame(
            ['slug', 'category', 'year', 'image', 'title', 'excerpt'],
            array_keys($idCases[0])
        );
    }

    public function test_cardbyslug_returns_match_or_null(): void
    {
        config(['services.deforestory_api.card_webhook_secret' => self::SECRET]);

        $this->signedPost($this->cardsPayload(), delivery: Str::uuid()->toString())->assertStatus(200);

        $api = app(DeforestoryApiService::class);

        $found = $api->cardBySlug('en', 'pulau-laut');
        $this->assertNotNull($found);
        $this->assertSame('Pulau Laut: palm oil behind protected forest', $found['title']);

        $this->assertNull($api->cardBySlug('id', 'tidak-ada'));
    }
}