<?php

namespace Tests\Feature;

use App\Jobs\DeforestoryCardNotificationJob;
use App\Mail\DeforestoryCardMail;
use App\Models\DeforestoryCard;
use App\Models\DeforestorySubscriber;
use App\Services\DeforestoryApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class DeforestoryCardWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const KEY = 'card-api-key';
    private const ENDPOINT = '/api/deforestory/cards';

    /** uuid caller-owned (stabil & portabel) — dipakai sebagai key upsert. */
    private const UUID_MAYAWANA = '11111111-1111-4111-8111-111111111111';
    private const UUID_PULAU_LAUT = '22222222-2222-4222-8222-222222222222';

    /** Payload contoh: 2 card (id + en lengkap). */
    private function cardsPayload(): array
    {
        return [
            'cards' => [
                [
                    'uuid' => self::UUID_MAYAWANA,
                    'slug' => 'mayawana',
                    'category' => 'pulp',
                    'year' => '2021–2025',
                    'image_id' => 'https://pasopati.id/storage/deforestory/mayawana-id.jpg',
                    'image_en' => 'https://pasopati.id/storage/deforestory/mayawana-en.jpg',
                    'title_id' => 'Mayawana: jejak deforestasi',
                    'title_en' => 'Mayawana: deforestation trail',
                    'excerpt_id' => 'Analisis spasial Mayawana.',
                    'excerpt_en' => 'Spatial analysis of Mayawana.',
                    'sort' => 1,
                ],
                [
                    'uuid' => self::UUID_PULAU_LAUT,
                    'slug' => 'pulau-laut',
                    'category' => 'sawit',
                    'year' => '2022–2024',
                    'image_id' => 'https://pasopati.id/storage/deforestory/pulau-laut-id.jpg',
                    'image_en' => 'https://pasopati.id/storage/deforestory/pulau-laut-en.jpg',
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

    public function test_upsert_updates_existing_card_by_uuid(): void
    {
        config(['services.deforestory_api.key' => self::KEY]);

        $this->postCards($this->cardsPayload(), Str::uuid()->toString());

        // Push kedua: ubah mayawana (uuid SAMA, slug bisa beda), TANPA kirim pulau-laut.
        // Upsert by uuid → row lama di-update, bukan jadi row baru.
        $updated = [
            'cards' => [
                [
                    'uuid' => self::UUID_MAYAWANA,
                    'slug' => 'mayawana',
                    'category' => 'mining',
                    'year' => '2021–2025',
                    'image_id' => null,
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

        // mayawana di-update (uuid sama → 1 row); pulau-laut TETAP ADA.
        $this->assertSame(2, DeforestoryCard::count());
        $this->assertSame('mining', DeforestoryCard::where('slug', 'mayawana')->value('category'));
        $this->assertSame('Mayawana (baru)', DeforestoryCard::where('slug', 'mayawana')->value('title_id'));
        $this->assertDatabaseHas('deforestory_cards', ['slug' => 'pulau-laut']);
    }

    public function test_upsert_by_uuid_updates_same_row_when_slug_changes(): void
    {
        config(['services.deforestory_api.key' => self::KEY]);

        $this->postCards($this->cardsPayload(), Str::uuid()->toString());
        $this->assertSame(2, DeforestoryCard::count());

        // Push ulang mayawana dengan uuid SAMA tapi slug & title baru (slug berubah
        // karena title berubah). Upsert by uuid → row yang sama di-update, bukan
        // row baru. Inilah alasan uuid jadi key (slug gak stabil).
        $renamed = [
            'cards' => [
                [
                    'uuid' => self::UUID_MAYAWANA,
                    'slug' => 'mayawana-baru',
                    'title_id' => 'Mayawana Baru',
                    'title_en' => 'Mayawana New',
                    'excerpt_id' => 'x', 'excerpt_en' => 'y', 'sort' => 1,
                ],
            ],
        ];

        $this->postCards($renamed, Str::uuid()->toString())->assertStatus(200)->assertJson(['stored' => 1]);

        // Masih 2 row (mayawana di-rename, bukan ditambah).
        $this->assertSame(2, DeforestoryCard::count());
        $this->assertSame('mayawana-baru', DeforestoryCard::where('uuid', self::UUID_MAYAWANA)->value('slug'));
        $this->assertDatabaseMissing('deforestory_cards', ['slug' => 'mayawana']);
    }

    public function test_works_without_auth(): void
    {
        // Auth sementara dimatikan — POST tanpa token pun harus sukses.
        $response = $this->postCards($this->cardsPayload(), Str::uuid()->toString(), key: null);

        $response->assertStatus(200)->assertJson(['received' => true, 'stored' => 2]);
    }

    public function test_post_response_echoes_uuid_per_card(): void
    {
        config(['services.deforestory_api.key' => self::KEY]);

        $response = $this->postCards($this->cardsPayload(), Str::uuid()->toString())->assertStatus(200);

        // Response sertakan slug + uuid tiap card tersimpan — supaya caller tahu
        // uuid-nya untuk PUT-by-uuid selanjutnya (uuid milik caller, di-echo balik).
        $cards = collect($response->json('cards'))->keyBy('slug');
        $this->assertSame(self::UUID_MAYAWANA, $cards['mayawana']['uuid']);
        $this->assertSame(self::UUID_PULAU_LAUT, $cards['pulau-laut']['uuid']);
    }

    public function test_card_without_uuid_falls_back_to_slug_upsert(): void
    {
        config(['services.deforestory_api.key' => self::KEY]);

        // Payload lama (slug, tanpa uuid) tetap jalan — upsert by slug, uuid null.
        // Card ini gak bisa di-PUT by uuid sampai di-push ulang dgn uuid.
        $payload = ['cards' => [
            ['slug' => 'tanpa-uuid', 'title_id' => 'Tanpa UUID', 'sort' => 1],
        ]];

        $response = $this->postCards($payload, Str::uuid()->toString());

        $response->assertStatus(200)->assertJson(['stored' => 1]);
        $this->assertDatabaseHas('deforestory_cards', ['slug' => 'tanpa-uuid']);
        $this->assertNull(DeforestoryCard::where('slug', 'tanpa-uuid')->value('uuid'));
    }

    public function test_card_without_slug_and_uuid_is_rejected(): void
    {
        config(['services.deforestory_api.key' => self::KEY]);

        // Tanpa slug DAN tanpa uuid → gak ada key upsert → 422.
        $payload = ['cards' => [
            ['title_id' => 'Tanpa slug & uuid', 'sort' => 1],
        ]];

        $response = $this->postCards($payload, Str::uuid()->toString());

        $response->assertStatus(422)->assertJson(['message' => 'Invalid payload: no card uuids or slugs']);
    }

    public function test_idempotency_same_delivery_dedup(): void
    {
        config(['services.deforestory_api.key' => self::KEY]);

        $delivery = Str::uuid()->toString();

        $this->postCards($this->cardsPayload(), $delivery)->assertStatus(200);
        $this->assertSame(2, DeforestoryCard::count());

        // Push kedua dengan delivery SAMA tapi payload berbeda → dedup.
        $second = ['cards' => [
            ['uuid' => '33333333-3333-4333-8333-333333333333', 'slug' => 'lain', 'title_id' => 'Lain', 'title_en' => 'Other', 'excerpt_id' => 'a', 'excerpt_en' => 'b', 'sort' => 9],
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

    public function test_new_card_dispatches_notification_job(): void
    {
        Queue::fake();

        // Push 2 card baru → 2 job di-dispatch.
        $this->postCards($this->cardsPayload(), Str::uuid()->toString())
            ->assertStatus(200)
            ->assertJson(['received' => true, 'stored' => 2, 'notified' => 2]);

        Queue::assertPushed(DeforestoryCardNotificationJob::class, 2);
    }

    public function test_existing_card_update_does_not_dispatch_job(): void
    {
        Queue::fake();

        // Push pertama: 2 card baru → 2 job.
        $this->postCards($this->cardsPayload(), Str::uuid()->toString())->assertStatus(200);
        Queue::assertPushed(DeforestoryCardNotificationJob::class, 2);

        // Push kedua: mayawana di-update (uuid sama → update, gak dispatch), 1 card
        // baru (uuid baru → dispatch). Delivery beda supaya gak kena dedup.
        $updated = ['cards' => [
            ['uuid' => self::UUID_MAYAWANA, 'slug' => 'mayawana', 'category' => 'mining', 'title_id' => 'Mayawana (baru)',
             'title_en' => 'Mayawana (new)', 'excerpt_id' => 'x', 'excerpt_en' => 'y', 'sort' => 1],
            ['uuid' => '44444444-4444-4444-8444-444444444444', 'slug' => 'baru-lagi', 'title_id' => 'Baru', 'title_en' => 'New2',
             'excerpt_id' => 'a', 'excerpt_en' => 'b', 'sort' => 2],
        ]];
        $this->postCards($updated, Str::uuid()->toString())
            ->assertStatus(200)
            ->assertJson(['stored' => 2, 'notified' => 1]);

        // Total job = 2 (push 1) + 1 (push 2, hanya card baru) = 3.
        Queue::assertPushed(DeforestoryCardNotificationJob::class, 3);
    }

    /** PUT/PATCH satu card by UUID — partial update, hanya field yang dikirim. */
    private function updateCard($uuid, array $payload, ?string $key = self::KEY)
    {
        $server = ['CONTENT_TYPE' => 'application/json'];
        if ($key) {
            $server['HTTP_AUTHORIZATION'] = 'Bearer ' . $key;
        }

        return $this->call(
            'PUT', self::ENDPOINT . '/' . $uuid, [], [], [], $server,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    /** Ambil uuid card by slug (helper biar test pakai identifier stabil & portabel). */
    private function cardUuid(string $slug): string
    {
        return (string) DeforestoryCard::where('slug', $slug)->value('uuid');
    }

    public function test_update_endpoint_updates_existing_card_fields(): void
    {
        config(['services.deforestory_api.key' => self::KEY]);

        $this->postCards($this->cardsPayload(), Str::uuid()->toString())->assertStatus(200);

        $uuid = $this->cardUuid('mayawana');

        // Partial update: kirim hanya category + title_id. Field lain tetap.
        $response = $this->updateCard($uuid, [
            'category' => 'mining',
            'title_id' => 'Mayawana (revisi)',
        ]);

        $response->assertStatus(200)->assertJson(['received' => true, 'updated' => true]);

        $card = DeforestoryCard::where('uuid', $uuid)->first();
        $this->assertSame('mining', $card->category);
        $this->assertSame('Mayawana (revisi)', $card->title_id);
        // Field yang tidak dikirim tetap utuh.
        $this->assertSame('Mayawana: deforestation trail', $card->title_en);
        $this->assertSame('Analisis spasial Mayawana.', $card->excerpt_id);
        $this->assertSame('2021–2025', $card->year); // tidak diubah

        // Response mengembalikan field yang sudah di-update (termasuk uuid).
        $response->assertJsonPath('card.uuid', $uuid);
        $response->assertJsonPath('card.category', 'mining');
    }

    public function test_update_endpoint_allows_setting_field_to_null(): void
    {
        config(['services.deforestory_api.key' => self::KEY]);

        $this->postCards($this->cardsPayload(), Str::uuid()->toString())->assertStatus(200);

        $uuid = $this->cardUuid('mayawana');

        // image_id dikirim null eksplisit → harus benar-benar ter-set null, bukan
        // diabaikan.
        $this->updateCard($uuid, ['image_id' => null])->assertStatus(200);

        $this->assertNull(DeforestoryCard::where('uuid', $uuid)->first()->image_id);
    }

    public function test_update_endpoint_accepts_singular_title_alias(): void
    {
        config(['services.deforestory_api.key' => self::KEY]);

        $this->postCards($this->cardsPayload(), Str::uuid()->toString())->assertStatus(200);

        $uuid = $this->cardUuid('mayawana');

        // Kirim `title` (singular — shape yang dilihat caller dari toCardArray)
        // tanpa suffix _id. Harus tertulis ke title_id, DB berubah.
        $this->updateCard($uuid, ['title' => 'Mayawana via alias'])->assertStatus(200);

        $this->assertSame('Mayawana via alias', DeforestoryCard::where('uuid', $uuid)->first()->title_id);
        // title_en tidak ikut terisi (alias hanya ke _id).
        $this->assertSame('Mayawana: deforestation trail', DeforestoryCard::where('uuid', $uuid)->first()->title_en);
    }

    public function test_update_endpoint_singular_excerpt_alias_writes_excerpt_id(): void
    {
        config(['services.deforestory_api.key' => self::KEY]);

        $this->postCards($this->cardsPayload(), Str::uuid()->toString())->assertStatus(200);

        $uuid = $this->cardUuid('mayawana');

        $this->updateCard($uuid, ['excerpt' => 'Ringkasan via alias'])->assertStatus(200);

        $this->assertSame('Ringkasan via alias', DeforestoryCard::where('uuid', $uuid)->first()->excerpt_id);
    }

    public function test_update_endpoint_explicit_title_id_wins_over_alias(): void
    {
        config(['services.deforestory_api.key' => self::KEY]);

        $this->postCards($this->cardsPayload(), Str::uuid()->toString())->assertStatus(200);

        $uuid = $this->cardUuid('mayawana');

        // Keduanya dikirim: title_id eksplisit menang, alias title diabaikan.
        $this->updateCard($uuid, ['title' => 'dari alias', 'title_id' => 'dari eksplisit'])->assertStatus(200);

        $this->assertSame('dari eksplisit', DeforestoryCard::where('uuid', $uuid)->first()->title_id);
    }

    public function test_update_endpoint_rejects_when_no_updatable_fields(): void
    {
        config(['services.deforestory_api.key' => self::KEY]);

        $this->postCards($this->cardsPayload(), Str::uuid()->toString())->assertStatus(200);
        $uuid = $this->cardUuid('mayawana');
        $before = DeforestoryCard::where('uuid', $uuid)->first()->title_id;

        // Field tidak dikenali → 422, bukan silent success. DB tetap.
        $response = $this->updateCard($uuid, ['unknown_field' => 'x']);
        $response->assertStatus(422)->assertJson(['message' => 'No updatable fields provided']);
        $this->assertSame($before, DeforestoryCard::where('uuid', $uuid)->first()->title_id);
        $this->assertSame('mayawana', DeforestoryCard::where('uuid', $uuid)->first()->slug);
    }

    public function test_update_endpoint_returns_404_for_unknown_uuid(): void
    {
        config(['services.deforestory_api.key' => self::KEY]);

        // UUID belum pernah ada → 404, tidak membuat card baru.
        $uuid = Str::uuid()->toString();
        $response = $this->updateCard($uuid, ['category' => 'mining']);

        $response->assertStatus(404);
        $this->assertDatabaseMissing('deforestory_cards', ['uuid' => $uuid]);
    }

    public function test_update_endpoint_does_not_dispatch_notification_job(): void
    {
        Queue::fake();

        config(['services.deforestory_api.key' => self::KEY]);

        $this->postCards($this->cardsPayload(), Str::uuid()->toString())->assertStatus(200);
        Queue::assertPushed(DeforestoryCardNotificationJob::class, 2);

        $uuid = $this->cardUuid('mayawana');

        // Update card yang sudah ada → tidak boleh dispatch job baru.
        $this->updateCard($uuid, ['category' => 'mining'])->assertStatus(200);

        Queue::assertPushed(DeforestoryCardNotificationJob::class, 2);
    }

    public function test_update_endpoint_works_without_auth(): void
    {
        // Auth sementara dimatikan — update tanpa token pun harus sukses.
        $this->postCards($this->cardsPayload(), Str::uuid()->toString(), key: null)->assertStatus(200);

        $uuid = $this->cardUuid('mayawana');

        $this->updateCard($uuid, ['category' => 'mining'], key: null)
            ->assertStatus(200)
            ->assertJson(['updated' => true]);
    }

    public function test_update_endpoint_accepts_patch_method(): void
    {
        config(['services.deforestory_api.key' => self::KEY]);

        $this->postCards($this->cardsPayload(), Str::uuid()->toString())->assertStatus(200);

        $uuid = $this->cardUuid('mayawana');

        $server = ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . self::KEY];
        $response = $this->call(
            'PATCH', self::ENDPOINT . '/' . $uuid, [], [], [], $server,
            json_encode(['category' => 'pulp-paper'], JSON_UNESCAPED_UNICODE)
        );

        $response->assertStatus(200)->assertJson(['updated' => true]);
        $this->assertSame('pulp-paper', DeforestoryCard::where('uuid', $uuid)->first()->category);
    }

    public function test_update_endpoint_accepts_wrapped_cards_shape(): void
    {
        // Shape sama dengan POST /cards: {"cards":[{...}]}. Entry pertama dipakai
        // (card target ditentukan oleh {uuid} di URL, bukan slug di body).
        config(['services.deforestory_api.key' => self::KEY]);

        $this->postCards($this->cardsPayload(), Str::uuid()->toString())->assertStatus(200);

        $uuid = $this->cardUuid('mayawana');

        $payload = ['cards' => [
            ['slug' => 'mayawana', 'category' => 'pulp', 'title_id' => 'Mayawana via wrapped'],
        ]];

        $response = $this->updateCard($uuid, $payload);
        $response->assertStatus(200)->assertJson(['updated' => true]);

        $this->assertSame('Mayawana via wrapped', DeforestoryCard::where('uuid', $uuid)->first()->title_id);
        $this->assertSame('pulp', DeforestoryCard::where('uuid', $uuid)->first()->category);
    }

    public function test_update_endpoint_auto_slug_follows_title_change(): void
    {
        // Inti: update title (tanpa kirim slug) → slug otomatis = Str::slug(title).
        config(['services.deforestory_api.key' => self::KEY]);

        $this->postCards($this->cardsPayload(), Str::uuid()->toString())->assertStatus(200);
        $uuid = $this->cardUuid('mayawana');

        $this->updateCard($uuid, ['title_id' => 'Mayawana Baru'])->assertStatus(200);

        $card = DeforestoryCard::where('uuid', $uuid)->first();
        $this->assertSame('Mayawana Baru', $card->title_id);
        $this->assertSame('mayawana-baru', $card->slug); // auto dari title
        $this->assertDatabaseMissing('deforestory_cards', ['slug' => 'mayawana']);
    }

    public function test_update_endpoint_still_addressed_by_uuid_after_slug_changes(): void
    {
        // Setelah slug berubah, address by uuid tetap valid (uuid stabil, intinya
        // permintaan pakai identifier yang gak ikut berubah saat title berubah).
        config(['services.deforestory_api.key' => self::KEY]);

        $this->postCards($this->cardsPayload(), Str::uuid()->toString())->assertStatus(200);
        $uuid = $this->cardUuid('mayawana');

        $this->updateCard($uuid, ['title_id' => 'Mayawana Baru'])->assertStatus(200);
        $this->assertSame('mayawana-baru', DeforestoryCard::where('uuid', $uuid)->first()->slug);

        // Update lagi via uuid (slug lama 'mayawana' udah gak ada) — tetap jalan.
        $this->updateCard($uuid, ['category' => 'mining'])->assertStatus(200)->assertJson(['updated' => true]);
        $this->assertSame('mining', DeforestoryCard::where('uuid', $uuid)->first()->category);
    }

    public function test_update_endpoint_wrapped_slug_ignored_when_title_present(): void
    {
        // Payload POST-shape bawa slug lama + title baru → slug lama diabaikan,
        // slug mengikuti title baru (inilah masalah yang dikeluhkan user).
        config(['services.deforestory_api.key' => self::KEY]);

        $this->postCards($this->cardsPayload(), Str::uuid()->toString())->assertStatus(200);
        $uuid = $this->cardUuid('mayawana');

        $payload = ['cards' => [
            ['slug' => 'stale-old-slug', 'title_id' => 'Mayawana Baru', 'category' => 'pulp'],
        ]];

        $this->updateCard($uuid, $payload)->assertStatus(200);

        $card = DeforestoryCard::where('uuid', $uuid)->first();
        $this->assertSame('mayawana-baru', $card->slug); // bukan 'stale-old-slug'
        $this->assertSame('Mayawana Baru', $card->title_id);
        $this->assertSame('pulp', $card->category);
    }

    public function test_update_endpoint_category_only_does_not_change_slug(): void
    {
        // Update field lain (category) dengan title TIDAK berubah → slug tetap.
        config(['services.deforestory_api.key' => self::KEY]);

        $this->postCards($this->cardsPayload(), Str::uuid()->toString())->assertStatus(200);
        $uuid = $this->cardUuid('mayawana');
        $slugBefore = DeforestoryCard::where('uuid', $uuid)->first()->slug;

        $this->updateCard($uuid, ['category' => 'mining'])->assertStatus(200);

        $this->assertSame($slugBefore, DeforestoryCard::where('uuid', $uuid)->first()->slug);
        $this->assertSame('mining', DeforestoryCard::where('uuid', $uuid)->first()->category);
    }

    public function test_update_endpoint_explicit_flat_slug_overrides_auto_slug(): void
    {
        // Kirim title + slug eksplisit (flat) → slug eksplisit menang, gak auto.
        config(['services.deforestory_api.key' => self::KEY]);

        $this->postCards($this->cardsPayload(), Str::uuid()->toString())->assertStatus(200);
        $uuid = $this->cardUuid('mayawana');

        $this->updateCard($uuid, ['title_id' => 'Mayawana Baru', 'slug' => 'custom-slug-xxx'])->assertStatus(200);

        $card = DeforestoryCard::where('uuid', $uuid)->first();
        $this->assertSame('custom-slug-xxx', $card->slug);
        $this->assertSame('Mayawana Baru', $card->title_id);
    }

    public function test_update_endpoint_rejects_duplicate_slug(): void
    {
        config(['services.deforestory_api.key' => self::KEY]);

        $this->postCards($this->cardsPayload(), Str::uuid()->toString())->assertStatus(200);
        $uuid = $this->cardUuid('mayawana');

        // Pulau-laut sudah pakai slug 'pulau-laut' → mayawana gak boleh pakai itu.
        $response = $this->updateCard($uuid, ['slug' => 'pulau-laut']);
        $response->assertStatus(422)->assertJson(['message' => 'Slug already in use']);
        $this->assertSame('mayawana', DeforestoryCard::where('uuid', $uuid)->first()->slug);
    }

    public function test_update_endpoint_rejects_empty_slug(): void
    {
        config(['services.deforestory_api.key' => self::KEY]);

        $this->postCards($this->cardsPayload(), Str::uuid()->toString())->assertStatus(200);
        $uuid = $this->cardUuid('mayawana');

        $response = $this->updateCard($uuid, ['slug' => '']);
        $response->assertStatus(422)->assertJson(['message' => 'Slug cannot be empty']);
        $this->assertSame('mayawana', DeforestoryCard::where('uuid', $uuid)->first()->slug);
    }

    public function test_card_notification_job_emails_all_subscribers_only(): void
    {
        Mail::fake();

        // 2 subscriber type=all aktif (id + en), 1 subscriber type=case (harus di-skip).
        $all = DeforestorySubscriber::create(['email' => 'all-id@x.com', 'type' => 'all', 'locale' => 'id', 'active' => true]);
        $all2 = DeforestorySubscriber::create(['email' => 'all-en@x.com', 'type' => 'all', 'locale' => 'en', 'active' => true]);
        $caseSub = DeforestorySubscriber::create(['email' => 'case@x.com', 'type' => 'case', 'locale' => 'id', 'active' => true, 'case_id' => null]);

        // Dispatch job manual (mirip yang controller lakukan).
        $card = DeforestoryCard::create([
            'slug' => 'mayawana', 'title_id' => 'Mayawana', 'title_en' => 'Mayawana (en)',
            'excerpt_id' => 'Ringkasan id', 'excerpt_en' => 'Summary en', 'sort' => 1,
        ]);
        DeforestoryCardNotificationJob::dispatchSync($card);

        // Hanya 2 email (type=all) yang terkirim; type=case di-skip.
        Mail::assertQueued(DeforestoryCardMail::class, 2);

        Mail::assertQueued(DeforestoryCardMail::class, function ($mail) use ($all) {
            return $mail->hasTo($all->email) && $mail->subscriberLocale === 'id';
        });
        Mail::assertQueued(DeforestoryCardMail::class, function ($mail) use ($all2) {
            return $mail->hasTo($all2->email) && $mail->subscriberLocale === 'en';
        });
    }

    public function test_card_mail_uses_card_locale_fields(): void
    {
        Mail::fake();

        $en = DeforestorySubscriber::create(['email' => 'en@x.com', 'type' => 'all', 'locale' => 'en', 'active' => true]);
        $id = DeforestorySubscriber::create(['email' => 'id@x.com', 'type' => 'all', 'locale' => 'id', 'active' => true]);

        $card = DeforestoryCard::create([
            'slug' => 'pulau-laut',
            'title_id' => 'Judul ID', 'title_en' => 'Title EN',
            'excerpt_id' => 'Excerpt ID', 'excerpt_en' => 'Excerpt EN',
            'sort' => 1,
        ]);
        DeforestoryCardNotificationJob::dispatchSync($card);

        // Subscriber en: subject pakai title_en; id: pakai title_id.
        Mail::assertQueued(DeforestoryCardMail::class, function ($mail) use ($en) {
            return $mail->hasTo($en->email)
                && $mail->envelope()->subject === 'New Deforestory case: Title EN';
        });
        Mail::assertQueued(DeforestoryCardMail::class, function ($mail) use ($id) {
            return $mail->hasTo($id->email)
                && $mail->envelope()->subject === 'Kasus Deforestory baru: Judul ID';
        });
    }
}