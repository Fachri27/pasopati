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

class DeforestorySyncTest extends TestCase
{
    use RefreshDatabase;

    private const URL = 'https://simontini.example/api/deforestory/sync';
    private const TOKEN = 'sync-token';

    private function makeCard(): DeforestoryCard
    {
        return DeforestoryCard::create([
            'uuid' => '26cd5ee6-b0dc-4a06-b9c7-82dbf6a99c10',
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

    public function test_sync_posts_payload_with_bearer_token_when_configured(): void
    {
        config([
            'services.deforestory_api.sync_url' => self::URL,
            'services.deforestory_api.sync_token' => self::TOKEN,
        ]);

        $this->makeCard();
        [$case, $laporan] = $this->makeLaporan();

        Http::fake([
            self::URL => Http::response(['ok' => true], 200),
        ]);

        DeforestorySyncJob::dispatchSync($case, $laporan, 'on');

        Http::assertSent(function ($request) use ($laporan) {
            if ($request->url() !== self::URL || $request->method() !== 'POST') {
                return false;
            }
            if ($request->header('Authorization')[0] !== 'Bearer '.self::TOKEN) {
                return false;
            }

            $data = $request->data();
            return $data['external_id'] === 'pasopati-update-'.$laporan->id
                && $data['deforestory_id'] === '26cd5ee6-b0dc-4a06-b9c7-82dbf6a99c10'
                && $data['title_id'] === 'Dampak di luar peta'
                && $data['title_en'] === 'Impact beyond the map'
                && $data['description_id'] === 'Desc dampak'
                && $data['description_en'] === 'Desc impact'
                && $data['published_at'] === '2025-06-03'
                && $data['status'] === 'on'
                && str_starts_with($data['image_url'], 'http')
                && str_contains($data['image_url'], 'storage/deforestory/laporans/dampak.jpg')
                && str_contains($data['target_url'], '/id/deforestory/mayawana/dampak-di-luar-peta');
        });
    }

    public function test_sync_off_payload_when_unpublishing(): void
    {
        config([
            'services.deforestory_api.sync_url' => self::URL,
            'services.deforestory_api.sync_token' => self::TOKEN,
        ]);

        $this->makeCard();
        [$case, $laporan] = $this->makeLaporan();

        Http::fake([self::URL => Http::response([], 200)]);

        DeforestorySyncJob::dispatchSync($case, $laporan, 'off');

        Http::assertSent(fn ($r) => $r->url() === self::URL && $r->data()['status'] === 'off');
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

        DeforestorySyncJob::dispatchSync($case, $laporan, 'on');

        Http::assertNothingSent();
    }

    public function test_sync_skips_when_case_has_no_matching_card(): void
    {
        config([
            'services.deforestory_api.sync_url' => self::URL,
            'services.deforestory_api.sync_token' => self::TOKEN,
        ]);

        [$case, $laporan] = $this->makeLaporan();

        Http::fake();

        DeforestorySyncJob::dispatchSync($case, $laporan, 'on');

        Http::assertNothingSent();
    }

    public function test_sync_skips_when_card_has_no_uuid(): void
    {
        config([
            'services.deforestory_api.sync_url' => self::URL,
            'services.deforestory_api.sync_token' => self::TOKEN,
        ]);

        DeforestoryCard::create([
            'slug' => 'mayawana',
            'status' => 'publish',
            'title_id' => 'Mayawana',
        ]);
        [$case, $laporan] = $this->makeLaporan();

        Http::fake();

        DeforestorySyncJob::dispatchSync($case, $laporan, 'on');

        Http::assertNothingSent();
    }

    public function test_sync_fails_job_when_target_returns_non_2xx(): void
    {
        config([
            'services.deforestory_api.sync_url' => self::URL,
            'services.deforestory_api.sync_token' => self::TOKEN,
        ]);

        $this->makeCard();
        [$case, $laporan] = $this->makeLaporan();

        Http::fake([self::URL => Http::response(['err' => 'nope'], 500)]);

        $this->expectException(\RuntimeException::class);
        (new DeforestorySyncJob($case, $laporan, 'on'))->handle();
    }

    public function test_publishing_via_form_dispatches_sync_job_on(): void
    {
        config([
            'services.deforestory_api.sync_url' => self::URL,
            'services.deforestory_api.webhook_url' => self::URL,
        ]);

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

        // Publish (status active dari baru) → sync 'on' ikut ke-antrian.
        Queue::assertPushed(DeforestorySyncJob::class, fn ($job) => $job->status === 'on');
        Queue::assertPushed(DeforestoryNotificationJob::class);
        Queue::assertPushed(DeforestoryWebhookJob::class);
    }

    public function test_unpublishing_via_form_dispatches_sync_job_off(): void
    {
        config(['services.deforestory_api.sync_url' => self::URL]);

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
            ->set('title_id', 'Laporan aktif')
            ->set('title_en', 'Active report')
            ->set('status', 'inactive')
            ->set('sort', 1)
            ->call('save');

        // Unpublish (active → inactive) → sync 'off' ke-antrian.
        Queue::assertPushed(DeforestorySyncJob::class, fn ($job) => $job->status === 'off');
    }

    public function test_editing_active_laporan_does_not_dispatch_sync(): void
    {
        config(['services.deforestory_api.sync_url' => self::URL]);

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
