<?php

namespace Tests\Feature;

use App\Jobs\DeforestorySyncJob;
use App\Jobs\DeforestoryNotificationJob;
use App\Jobs\DeforestoryWebhookJob;
use App\Livewire\Deforestory\DeforestoryLaporanForm;
use App\Models\DeforestoryCard;
use App\Models\DeforestoryCase;
use App\Models\DeforestoryCaseTranslation;
use App\Models\DeforestoryLaporan;
use App\Models\DeforestoryLaporanTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Sync keluar laporan Deforestory ke endpoint simontini (deforestory/sync).
 * Publish-only — dipicu form saat laporan jadi active. Unpublish/edit gak di-sync.
 *
 * Endpoint = POST {sync_url}/{uuid} — uuid card simontini di URL path. Body 7
 * field: title_id/en, description_id/en, target_url_id/en, published_at.
 * Job skip diam-diam kalau case gak punya card / card gak punya uuid, atau
 * simontini belum dikonfigurasi.
 */
class DeforestorySyncTest extends TestCase
{
    use RefreshDatabase;

    private const URL = 'https://simontini.example/api/deforestory/sync';
    private const TOKEN = 'sync-token';
    private const UUID = '26cd5ee6-b0dc-4a06-b9c7-82dbf6a99c10';

    private function syncEndpoint(): string
    {
        return self::URL . '/' . self::UUID;
    }

    private function configSync(): void
    {
        config([
            'services.deforestory_api.sync_url' => self::URL,
            'services.deforestory_api.sync_token' => self::TOKEN,
        ]);
    }

    private function makeCard(): DeforestoryCard
    {
        return DeforestoryCard::create([
            'uuid' => self::UUID,
            'slug' => 'mayawana',
            'status' => 'publish',
            'category' => 'pulp',
            'year' => '2021-2025',
            'title_id' => 'Mayawana',
        ]);
    }

    private function makeLaporan(): array
    {
        $case = DeforestoryCase::create([
            'slug' => 'mayawana', 'status' => 'active',
            'featured_image' => 'deforestory/cover.jpg',
            'category' => 'pulp', 'year' => '2021-2025', 'sort' => 1,
        ]);
        DeforestoryCaseTranslation::create([
            'case_id' => $case->id, 'locale' => 'id',
            'title' => 'Mayawana', 'excerpt' => 'Ringkasan',
        ]);

        $laporan = DeforestoryLaporan::create([
            'case_id' => $case->id,
            'slug' => 'dampak-di-luar-peta',
            'image' => 'deforestory/laporans/dampak.jpg',
            'sort' => 2,
            'status' => 'active',
            'published_at' => '2025-06-03',
        ]);
        DeforestoryLaporanTranslation::create([
            'laporan_id' => $laporan->id, 'locale' => 'id',
            'title' => 'Dampak di luar peta',
            'excerpt' => 'Desc dampak',
            'content' => '<p>Isi</p>',
        ]);
        DeforestoryLaporanTranslation::create([
            'laporan_id' => $laporan->id, 'locale' => 'en',
            'title' => 'Impact beyond the map',
            'excerpt' => 'Desc impact',
            'content' => '<p>Body</p>',
        ]);

        return [$case->fresh(), $laporan->fresh(['translations'])];
    }

    public function test_sync_posts_seven_field_payload_with_bearer_token(): void
    {
        $this->configSync();
        $this->makeCard();
        [$case, $laporan] = $this->makeLaporan();

        Http::fake([$this->syncEndpoint() => Http::response(['ok' => true], 200)]);

        DeforestorySyncJob::dispatchSync($case, $laporan);

        Http::assertSent(function ($request) {
            if ($request->url() !== $this->syncEndpoint() || $request->method() !== 'POST') {
                return false;
            }
            if ($request->header('Authorization')[0] !== 'Bearer ' . self::TOKEN) {
                return false;
            }

            $data = $request->data();

            // 7 field, persis kontrak simontini (urutan alphabetical setelah sort).
            $keys = array_keys($data);
            sort($keys);
            $expected = ['description_en', 'description_id', 'published_at', 'target_url_en', 'target_url_id', 'title_en', 'title_id'];
            if ($keys !== $expected) {
                return false;
            }

            return $data['title_id'] === 'Dampak di luar peta'
                && $data['title_en'] === 'Impact beyond the map'
                && $data['description_id'] === 'Desc dampak'
                && $data['description_en'] === 'Desc impact'
                && $data['published_at'] === '2025-06-03'
                && str_contains($data['target_url_id'], '/id/deforestory/mayawana/dampak-di-luar-peta')
                && str_contains($data['target_url_en'], '/en/deforestory/mayawana/dampak-di-luar-peta');
        });
    }

    public function test_sync_noop_when_url_not_configured(): void
    {
        config([
            'services.deforestory_api.sync_url' => null,
            'services.deforestory_api.sync_token' => null,
        ]);

        $this->makeCard();
        [$case, $laporan] = $this->makeLaporan();

        Http::fake();

        DeforestorySyncJob::dispatchSync($case, $laporan);

        Http::assertNothingSent();
    }

    public function test_sync_skips_when_case_has_no_matching_card(): void
    {
        $this->configSync();

        [$case, $laporan] = $this->makeLaporan();

        Http::fake();

        DeforestorySyncJob::dispatchSync($case, $laporan);

        Http::assertNothingSent();
    }

    public function test_sync_skips_when_card_has_no_uuid(): void
    {
        $this->configSync();

        DeforestoryCard::create([
            'slug' => 'mayawana',
            'status' => 'publish',
            'title_id' => 'Mayawana',
        ]);
        [$case, $laporan] = $this->makeLaporan();

        Http::fake();

        DeforestorySyncJob::dispatchSync($case, $laporan);

        Http::assertNothingSent();
    }

    public function test_sync_fails_job_when_target_returns_non_2xx(): void
    {
        $this->configSync();
        $this->makeCard();
        [$case, $laporan] = $this->makeLaporan();

        Http::fake([$this->syncEndpoint() => Http::response(['err' => 'nope'], 500)]);

        $this->expectException(\RuntimeException::class);
        (new DeforestorySyncJob($case, $laporan))->handle();
    }

    public function test_sync_fails_when_target_redirects_unregistered_uuid(): void
    {
        // Simontini balas 302 redirect ke homepage untuk uuid yang gak terdaftar
        // (deforestory belum dibuat di sisi simontini). Job HARUS gak ngikutin
        // redirect — 302 harus keluar sebagai failure, bukan 302→200 false-success.
        $this->configSync();
        $this->makeCard();
        [$case, $laporan] = $this->makeLaporan();

        Http::fake([
            $this->syncEndpoint() => Http::response('', 302, ['Location' => 'https://simontini.example/']),
        ]);

        $this->expectException(\RuntimeException::class);
        (new DeforestorySyncJob($case, $laporan))->handle();
    }

    public function test_publishing_via_form_dispatches_sync_job(): void
    {
        $this->configSync();
        config(['services.deforestory_api.webhook_url' => self::URL]);

        $this->makeCard();
        $case = DeforestoryCase::create([
            'slug' => 'mayawana', 'status' => 'active', 'sort' => 1,
        ]);
        DeforestoryCaseTranslation::create([
            'case_id' => $case->id, 'locale' => 'id', 'title' => 'Mayawana',
        ]);

        Queue::fake();

        Livewire::test(DeforestoryLaporanForm::class, ['caseSlug' => 'mayawana'])
            ->set('title_id', 'Laporan baru')
            ->set('title_en', 'New report')
            ->set('slug', 'laporan-baru')
            ->set('status', 'active')
            ->set('sort', 1)
            ->call('save');

        // Publish (status active dari baru) → sync ikut ke-antrian.
        Queue::assertPushed(DeforestorySyncJob::class);
        Queue::assertPushed(DeforestoryNotificationJob::class);
        Queue::assertPushed(DeforestoryWebhookJob::class);
    }

    public function test_unpublishing_via_form_does_not_dispatch_sync(): void
    {
        $this->configSync();

        $case = DeforestoryCase::create([
            'slug' => 'mayawana', 'status' => 'active', 'sort' => 1,
        ]);
        DeforestoryCaseTranslation::create([
            'case_id' => $case->id, 'locale' => 'id', 'title' => 'Mayawana',
        ]);

        $laporan = DeforestoryLaporan::create([
            'case_id' => $case->id, 'slug' => 'laporan-aktif',
            'sort' => 1, 'status' => 'active', 'published_at' => '2025-06-03',
        ]);
        DeforestoryLaporanTranslation::create([
            'laporan_id' => $laporan->id, 'locale' => 'id',
            'title' => 'Laporan aktif', 'excerpt' => 'x', 'content' => '<p>x</p>',
        ]);
        DeforestoryLaporanTranslation::create([
            'laporan_id' => $laporan->id, 'locale' => 'en',
            'title' => 'Active report', 'excerpt' => 'x', 'content' => '<p>x</p>',
        ]);

        Queue::fake();

        // Laporan aktif → edit ke inactive = unpublish.
        Livewire::test(DeforestoryLaporanForm::class, ['laporanId' => $laporan->id])
            ->set('title_id', 'Laporan aktif')
            ->set('title_en', 'Active report')
            ->set('status', 'inactive')
            ->set('sort', 1)
            ->call('save');

        // Publish-only → unpublish gak memicu sync.
        Queue::assertNotPushed(DeforestorySyncJob::class);
    }

    public function test_editing_active_laporan_does_not_dispatch_sync(): void
    {
        $this->configSync();

        $case = DeforestoryCase::create([
            'slug' => 'mayawana', 'status' => 'active', 'sort' => 1,
        ]);
        DeforestoryCaseTranslation::create([
            'case_id' => $case->id, 'locale' => 'id', 'title' => 'Mayawana',
        ]);

        $laporan = DeforestoryLaporan::create([
            'case_id' => $case->id, 'slug' => 'laporan-aktif',
            'sort' => 1, 'status' => 'active', 'published_at' => '2025-06-03',
        ]);
        DeforestoryLaporanTranslation::create([
            'laporan_id' => $laporan->id, 'locale' => 'id',
            'title' => 'Laporan aktif', 'excerpt' => 'x', 'content' => '<p>x</p>',
        ]);
        DeforestoryLaporanTranslation::create([
            'laporan_id' => $laporan->id, 'locale' => 'en',
            'title' => 'Active report', 'excerpt' => 'x', 'content' => '<p>x</p>',
        ]);

        Queue::fake();

        Livewire::test(DeforestoryLaporanForm::class, ['laporanId' => $laporan->id])
            ->set('title_id', 'Laporan aktif (diubah)')
            ->set('title_en', 'Active report (edited)')
            ->set('status', 'active')
            ->set('sort', 1)
            ->call('save');

        // Edit laporan yang sudah aktif TIDAK memicu sync.
        Queue::assertNotPushed(DeforestorySyncJob::class);
    }
}