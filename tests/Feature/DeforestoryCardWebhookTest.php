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

        // Push kedua: mayawana di-update (slug sama), 1 card baru. Hanya card baru
        // yang dispatch job. Delivery beda supaya gak kena dedup.
        $updated = ['cards' => [
            ['slug' => 'mayawana', 'category' => 'mining', 'title_id' => 'Mayawana (baru)',
             'title_en' => 'Mayawana (new)', 'excerpt_id' => 'x', 'excerpt_en' => 'y', 'sort' => 1],
            ['slug' => 'baru-lagi', 'title_id' => 'Baru', 'title_en' => 'New2',
             'excerpt_id' => 'a', 'excerpt_en' => 'b', 'sort' => 2],
        ]];
        $this->postCards($updated, Str::uuid()->toString())
            ->assertStatus(200)
            ->assertJson(['stored' => 2, 'notified' => 1]);

        // Total job = 2 (push 1) + 1 (push 2, hanya card baru) = 3.
        Queue::assertPushed(DeforestoryCardNotificationJob::class, 3);
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