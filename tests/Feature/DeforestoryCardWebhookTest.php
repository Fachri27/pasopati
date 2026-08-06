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

    private const KEY = 'card-api-key';
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

    private function postCards(array $payload, ?string $delivery = null, ?string $key = self::KEY)
    {
        $headers = ['Content-Type' => 'application/json'];
        $server = ['CONTENT_TYPE' => 'application/json'];
        if ($key) {
            $server['HTTP_AUTHORIZATION'] = 'Bearer ' . $key;
        }
        if ($delivery) {
            $server['HTTP_X_DEFORESTORY_DELIVERY'] = $delivery;
        }

        return $this->call(
            'POST', self::ENDPOINT, [], [], [], $server,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    public function test_valid_api_key_upserts_cards_without_deleting_others(): void
    {
        config(['services.deforestory_api.key' => self::KEY]);

        // Seed card lama yang TIDAK ada di payload → harus TETAP ADA (mode tambah/update, bukan replace).
        DeforestoryCard::create(['slug' => 'lama-tetap', 'sort' => 0]);

        $response = $this->postCards($this->cardsPayload(), Str::uuid()->toString());

        $response->assertStatus(200)->assertJson(['received' => true, 'stored' => 2]);

        // Card lama tetap utuh; 2 card baru ditambahkan → total 3.
        $this->assertDatabaseHas('deforestory_cards', ['slug' => 'lama-tetap']);
        $this->assertSame(3, DeforestoryCard::count());

        $m = DeforestoryCard::where('slug', 'mayawana')->first();
        $this->assertSame('pulp', $m->category);
        $this->assertSame('Mayawana: jejak deforestasi', $m->title_id);
        $this->assertSame('Mayawana: deforestation trail', $m->title_en);
    }

    public function test_upsert_updates_existing_card_by_slug(): void
    {
        config(['services.deforestory_api.key' => self::KEY]);

        $this->postCards($this->cardsPayload(), Str::uuid()->toString());

        // Push kedua: ubah mayawana, TANPA kirim pulau-laut.
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

        $response = $this->postCards($updated, Str::uuid()->toString());

        $response->assertStatus(200)->assertJson(['stored' => 1]);

        // mayawana di-update; pulau-laut TETAP ADA (gak ikut dihapus).
        $this->assertSame(2, DeforestoryCard::count());
        $this->assertSame('mining', DeforestoryCard::where('slug', 'mayawana')->value('category'));
        $this->assertSame('Mayawana (baru)', DeforestoryCard::where('slug', 'mayawana')->value('title_id'));
        $this->assertDatabaseHas('deforestory_cards', ['slug' => 'pulau-laut']);
    }

    public function test_works_without_auth(): void
    {
        // Auth sementara dimatikan — POST tanpa token pun harus sukses.
        $response = $this->postCards($this->cardsPayload(), Str::uuid()->toString(), key: null);

        $response->assertStatus(200)->assertJson(['received' => true, 'stored' => 2]);
    }

    public function test_idempotency_same_delivery_dedup(): void
    {
        config(['services.deforestory_api.key' => self::KEY]);

        $delivery = Str::uuid()->toString();

        $this->postCards($this->cardsPayload(), $delivery)->assertStatus(200);
        $this->assertSame(2, DeforestoryCard::count());

        // Push kedua dengan delivery SAMA tapi payload berbeda → dedup.
        $second = ['cards' => [
            ['slug' => 'lain', 'title_id' => 'Lain', 'title_en' => 'Other', 'excerpt_id' => 'a', 'excerpt_en' => 'b', 'sort' => 9],
        ]];
        $response = $this->postCards($second, $delivery);

        $response->assertStatus(200)->assertJson(['received' => true, 'dedup' => true]);
        $this->assertSame(2, DeforestoryCard::count());
        $this->assertDatabaseMissing('deforestory_cards', ['slug' => 'lain']);
    }

    public function test_getcases_reads_local_cards_after_push(): void
    {
        config(['services.deforestory_api.key' => self::KEY]);

        $this->postCards($this->cardsPayload(), Str::uuid()->toString())->assertStatus(200);

        $idCases = app(DeforestoryApiService::class)->getCases('id');
        $enCases = app(DeforestoryApiService::class)->getCases('en');

        $this->assertSame('mayawana', $idCases[0]['slug']);
        $this->assertSame('Mayawana: jejak deforestasi', $idCases[0]['title']);
        $this->assertSame('Analisis spasial Mayawana.', $idCases[0]['excerpt']);
        $this->assertSame('Mayawana: deforestation trail', $enCases[0]['title']);
        $this->assertSame('Spatial analysis of Mayawana.', $enCases[0]['excerpt']);

        $this->assertSame(
            ['slug', 'category', 'year', 'image', 'title', 'excerpt'],
            array_keys($idCases[0])
        );
    }

    public function test_cardbyslug_returns_match_or_null(): void
    {
        config(['services.deforestory_api.key' => self::KEY]);

        $this->postCards($this->cardsPayload(), Str::uuid()->toString())->assertStatus(200);

        $api = app(DeforestoryApiService::class);

        $found = $api->cardBySlug('en', 'pulau-laut');
        $this->assertNotNull($found);
        $this->assertSame('Pulau Laut: palm oil behind protected forest', $found['title']);

        $this->assertNull($api->cardBySlug('id', 'tidak-ada'));
    }
}