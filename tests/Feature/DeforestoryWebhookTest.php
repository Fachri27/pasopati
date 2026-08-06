<?php

namespace Tests\Feature;

use App\Jobs\DeforestoryWebhookJob;
use App\Jobs\DeforestoryNotificationJob;
use App\Livewire\Deforestory\DeforestoryLaporanForm;
use App\Models\DeforestoryCase;
use App\Models\DeforestoryCaseTranslation;
use App\Models\DeforestoryLaporan;
use App\Models\DeforestoryLaporanTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class DeforestoryWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'webhook-secret';
    private const TARGET = 'https://other-site.example/webhook/deforestory';

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

        return [$case->fresh(), $laporan->fresh(['translations'])];
    }

    public function test_webhook_posts_payload_with_signature_when_configured(): void
    {
        config([
            'services.deforestory_api.webhook_url' => self::TARGET,
            'services.deforestory_api.webhook_secret' => self::SECRET,
        ]);

        [$case, $laporan] = $this->makeLaporan();

        Http::fake([
            self::TARGET => Http::response(['ok' => true], 200),
        ]);

        DeforestoryWebhookJob::dispatchSync($case, $laporan, 'created');

        Http::assertSent(function ($request) {
            if ($request->url() !== self::TARGET) {
                return false;
            }
            if ($request->method() !== 'POST') {
                return false;
            }
            if ($request->header('X-Deforestory-Event')[0] !== 'created') {
                return false;
            }

            // Signature header ada dan berbentuk sha256=...
            $sig = $request->header('X-Deforestory-Signature')[0] ?? '';
            if (! str_starts_with($sig, 'sha256=')) {
                return false;
            }

            // Signature cocok dengan HMAC body pakai secret.
            $expected = 'sha256='.hash_hmac('sha256', $request->body(), self::SECRET);
            if (! hash_equals($expected, $sig)) {
                return false;
            }

            // Payload berisi identitas kasus + laporan shape slim.
            $data = json_decode($request->body(), true);
            return $data['event'] === 'created'
                && $data['case']['slug'] === 'mayawana'
                && $data['laporan']['slug'] === 'dampak-di-luar-peta'
                && $data['laporan']['title'] === 'Dampak di luar peta'
                && $data['laporan']['date'] === '2025-06-03'
                && isset($data['laporan']['link'], $data['laporan']['image'], $data['laporan']['desc'])
                && ! array_key_exists('content', $data['laporan']);
        });
    }

    public function test_webhook_noop_when_url_not_configured(): void
    {
        config(['services.deforestory_api.webhook_url' => null]);

        [$case, $laporan] = $this->makeLaporan();

        Http::fake();

        DeforestoryWebhookJob::dispatchSync($case, $laporan, 'created');

        Http::assertNothingSent();
    }

    public function test_webhook_supports_multiple_urls_comma_separated(): void
    {
        config([
            'services.deforestory_api.webhook_url' => 'https://a.example/hook, https://b.example/hook',
            'services.deforestory_api.webhook_secret' => self::SECRET,
        ]);

        [$case, $laporan] = $this->makeLaporan();

        Http::fake([
            'https://a.example/hook' => Http::response([], 200),
            'https://b.example/hook' => Http::response([], 200),
        ]);

        DeforestoryWebhookJob::dispatchSync($case, $laporan, 'created');

        Http::assertSent(fn ($r) => $r->url() === 'https://a.example/hook');
        Http::assertSent(fn ($r) => $r->url() === 'https://b.example/hook');
    }

    public function test_webhook_omits_signature_header_when_no_secret(): void
    {
        config([
            'services.deforestory_api.webhook_url' => self::TARGET,
            'services.deforestory_api.webhook_secret' => null,
        ]);

        [$case, $laporan] = $this->makeLaporan();

        Http::fake([self::TARGET => Http::response([], 200)]);

        DeforestoryWebhookJob::dispatchSync($case, $laporan, 'created');

        Http::assertSent(fn ($r) => empty($r->header('X-Deforestory-Signature')));
    }

    public function test_webhook_fails_job_when_target_returns_non_2xx(): void
    {
        config([
            'services.deforestory_api.webhook_url' => self::TARGET,
            'services.deforestory_api.webhook_secret' => self::SECRET,
        ]);

        [$case, $laporan] = $this->makeLaporan();

        Http::fake([self::TARGET => Http::response(['err' => 'nope'], 500)]);

        // dispatchSync + expectsJobs gagal jika job throw. Kita jalankan langsung
        // dan tangkap exception untuk konfirmasi retry nanti.
        $this->expectException(\RuntimeException::class);
        (new DeforestoryWebhookJob($case, $laporan, 'created'))->handle();
    }

    public function test_publishing_via_form_dispatches_email_and_webhook_jobs(): void
    {
        config(['services.deforestory_api.webhook_url' => self::TARGET]);

        $case = DeforestoryCase::create([
            'slug' => 'mayawana', 'status' => 'active',
            'featured_image' => null, 'category' => 'pulp', 'year' => '2025', 'sort' => 1,
        ]);
        DeforestoryCaseTranslation::create([
            'case_id' => $case->id, 'locale' => 'id', 'title' => 'Mayawana',
        ]);

        Queue::fake();

        $component = Livewire::test(DeforestoryLaporanForm::class, ['caseSlug' => 'mayawana'])
            ->set('title_id', 'Laporan baru')
            ->set('title_en', 'New report')
            ->set('slug', 'laporan-bara')
            ->set('status', 'active')
            ->set('sort', 1)
            ->call('save');

        $this->assertTrue($component->errors()->isEmpty(), 'Save should pass validation');

        // Publish (status active dari baru) → kedua job ke-antrian.
        Queue::assertPushed(DeforestoryNotificationJob::class);
        Queue::assertPushed(DeforestoryWebhookJob::class);
    }

    public function test_publishing_via_form_without_slug_derives_from_title(): void
    {
        config(['services.deforestory_api.webhook_url' => self::TARGET]);

        $case = DeforestoryCase::create([
            'slug' => 'mayawana', 'status' => 'active', 'sort' => 1,
        ]);
        DeforestoryCaseTranslation::create([
            'case_id' => $case->id, 'locale' => 'id', 'title' => 'Mayawana',
        ]);

        Queue::fake();

        $component = Livewire::test(DeforestoryLaporanForm::class, ['caseSlug' => 'mayawana'])
            ->set('title_id', 'Laporan Baru Tanpa Slug')
            ->set('title_en', 'New report')
            ->set('status', 'active')
            ->set('sort', 1)
            ->call('save');

        // Validasi lolos walau slug tidak diisi (diturunkan dari judul ID).
        $this->assertTrue($component->errors()->isEmpty(), 'Save should pass validation without slug');

        $saved = DeforestoryLaporan::where('slug', 'laporan-baru-tanpa-slug')->first();
        $this->assertNotNull($saved, 'Slug harus diturunkan otomatis dari judul ID');

        Queue::assertPushed(DeforestoryNotificationJob::class);
    }

    public function test_editing_active_laporan_does_not_dispatch_jobs(): void
    {
        config(['services.deforestory_api.webhook_url' => self::TARGET]);

        $case = DeforestoryCase::create([
            'slug' => 'mayawana', 'status' => 'active', 'sort' => 1,
        ]);
        DeforestoryCaseTranslation::create([
            'case_id' => $case->id, 'locale' => 'id', 'title' => 'Mayawana',
        ]);

        // Laporan sudah aktif sejak awal → "edit", bukan publish.
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

        // Edit laporan yang sudah aktif TIDAK memicu job apa pun.
        Queue::assertNotPushed(DeforestoryNotificationJob::class);
        Queue::assertNotPushed(DeforestoryWebhookJob::class);
    }
}