<?php

namespace App\Jobs;

use App\Models\DeforestoryCase;
use App\Models\DeforestoryLaporan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Kirim webhook ke web lain saat laporan Deforestory di-publish.
 *
 * CMS POST payload laporan (shape sama dengan GET API sindikasi: slug, title,
 * date, image, desc, link + slug kasus + event) ke tiap URL di
 * DEFORESTORY_WEBHOOK_URL (boleh beberapa, dipisah koma). Payload ditandatangani
 * HMAC SHA256 di header X-Deforestory-Signature supaya penerima bisa verifikasi
 * pengirimnya pakai DEFORESTORY_WEBHOOK_SECRET.
 *
 * Dijalankan via queue (async) supaya simpan admin gak nunggu HTTP keluar.
 */
class DeforestoryWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        public DeforestoryCase $case,
        public DeforestoryLaporan $laporan,
        public string $event = 'created'
    ) {}

    public function handle(): void
    {
        $url = config('services.deforestory_api.webhook_url');
        $secret = config('services.deforestory_api.webhook_secret');
        $timeout = (int) config('services.deforestory_api.webhook_timeout', 10);

        // Boleh kosong (web lain belum siap terima), atau beberapa URL dipisah koma.
        $urls = collect(explode(',', (string) $url))
            ->map(fn ($u) => trim($u))
            ->filter()
            ->unique();

        if ($urls->isEmpty()) {
            return;
        }

        $payload = $this->payload();

        foreach ($urls as $target) {
            // Encode sekali; string inilah yang dikirim DAN ditandatangani, supaya
            // signature selalu cocok dengan body yang benar-benar diterima penerima.
            $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $headers = [
                'X-Deforestory-Event' => $this->event,
                'X-Deforestory-Delivery' => $this->job?->uuid() ?? Str::uuid()->toString(),
            ];

            // Tanda tangan supaya penerima yakin pengirimnya CMS ini.
            if ($secret) {
                $headers['X-Deforestory-Signature'] = 'sha256='.hash_hmac('sha256', $body, $secret);
            }

            $response = Http::timeout($timeout)
                ->withHeaders($headers)
                ->withBody($body, 'application/json')
                ->post($target);

            // Lempar supaya Laravel retry (tries=3) kalau penerima gagal merespons 2xx.
            if (! $response->successful()) {
                throw new \RuntimeException(
                    "Webhook ke {$target} gagal: HTTP {$response->status()}"
                );
            }
        }
    }

    /**
     * Shape mirip GET API sindikasi (laporanSummary) + identitas kasus.
     * Kirim id + en sekaligus lewat `translations` supaya web lain gak
     * perlu GET ulang untuk ambil versi lain locale. Field top-level
     * `title`/`desc`/`link` tetap pakai default `id` (backward compatible).
     */
    protected function payload(): array
    {
        $image = $this->laporan->image ?: $this->case->featured_image;
        $date = ($this->laporan->published_at ?? $this->laporan->created_at)?->toDateString();

        // Bangun per-locale: title, excerpt, image (per-locale), link (locale-spesifik).
        $translations = [];
        foreach (['id', 'en'] as $locale) {
            $t = $this->laporan->translation($locale);
            $translations[$locale] = [
                'title' => $t?->title,
                'excerpt' => $t?->excerpt,
                'image' => $this->imageUrl($t?->image ?: $this->laporan->image ?: $image),
                'link' => route('deforestory.case.laporan', [
                    'locale' => $locale,
                    'slug' => $this->case->slug,
                    'laporanSlug' => $this->laporan->slug,
                ]),
            ];
        }

        // Default `id` tetap di top-level biar penerima lama gak rusak.
        $default = $translations['id'];

        return [
            'event' => $this->event,
            'locale' => 'id',
            'case' => [
                'slug' => $this->case->slug,
                'category' => $this->case->category,
                'year' => $this->case->year,
            ],
            'laporan' => [
                'slug' => $this->laporan->slug,
                'sort' => $this->laporan->sort,
                'date' => $date,
                'image' => $default['image'],
                // backward compatible (versi id)
                'title' => $default['title'],
                'desc' => $default['excerpt'],
                'link' => $default['link'],
                // id + en lengkap
                'translations' => $translations,
            ],
        ];
    }

    protected function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://'])
            ? $path
            : asset('storage/'.$path);
    }
}
