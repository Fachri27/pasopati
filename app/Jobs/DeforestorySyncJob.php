<?php

namespace App\Jobs;

use App\Models\DeforestoryCard;
use App\Models\DeforestoryCase;
use App\Models\DeforestoryLaporan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

/**
 * Kirim laporan Deforestory ke endpoint simontini (sync keluar) saat laporan
 * di-publish (jadi active). Publish-only — unpublish/edit gak di-sync
 * (simontini cuma perlu tau laporan baru naik, bukan laporan turun).
 *
 * Beda dari DeforestoryWebhookJob (webhook keluar generik, HMAC + shape sindikasi
 * lama): simontini pakai Bearer token (bukan HMAC) + shape body sendiri.
 *
 * Endpoint = POST {sync_url}/{uuid} — uuid card simontini ada di URL path
 * (identifier kasus di sisi simontini). Body (7 field):
 *   title_id, title_en, description_id, description_en,
 *   target_url_id, target_url_en, published_at
 * `description_*` = excerpt laporan. `target_url_*` = URL publik laporan per
 * locale di pasopati. Gak ada external_id/deforestory_id di body (uuid sudah di
 * path), gak ada image_url, gak ada status.
 *
 * `deforestory_id` (uuid card) dicari lewat case.slug == card.slug. Kalau case
 * gak punya card simontini / card gak punya uuid → skip diam-diam: laporan ini
 * bukan bagian deforestory simontini.
 *
 * Dijalankan via queue (async) supaya simpan admin gak nunggu HTTP keluar.
 * tries=3 + backoff 10s biar retry kalau simontini sempat gagal merespons 2xx.
 */
class DeforestorySyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public DeforestoryCase $case,
        public DeforestoryLaporan $laporan
    ) {}

    public function handle(): void
    {
        $url = config('services.deforestory_api.sync_url');
        $token = config('services.deforestory_api.sync_token');
        $timeout = (int) config('services.deforestory_api.webhook_timeout', 10);

        // Simontini belum dikonfigurasi → skip diam-diam (bukan error).
        if (! $url || ! $token) {
            return;
        }

        // uuid card simontini (case di-match via slug) → identifier kasus di
        // simontini, ditaruh di URL path. Kalau case gak punya card / card gak
        // punya uuid → bukan case simontini, skip.
        $card = DeforestoryCard::where('slug', $this->case->slug)->first();
        if (! $card || ! $card->uuid) {
            return;
        }

        $payload = $this->payload();

        // Endpoint simontini: POST /api/deforestory/sync/{uuid}.
        $endpoint = rtrim($url, '/') . '/' . rawurlencode($card->uuid);

        $response = Http::timeout($timeout)
            ->withToken($token)
            ->post($endpoint, $payload);

        // Lempar supaya Laravel retry (tries=3) kalau simontini gagal 2xx.
        if (! $response->successful()) {
            throw new \RuntimeException(
                "Sync laporan ke simontini ({$endpoint}) gagal: HTTP {$response->status()}"
            );
        }
    }

    /**
     * Body simontini (7 field). title/description per-locale dari
     * DeforestoryLaporanTranslation (description = excerpt). target_url per
     * locale = URL publik laporan di pasopati.
     */
    protected function payload(): array
    {
        $idTrans = $this->laporan->translation('id');
        $enTrans = $this->laporan->translation('en');

        $publishedAt = ($this->laporan->published_at ?? $this->laporan->created_at)?->toDateString();

        return [
            'title_id' => $idTrans?->title,
            'title_en' => $enTrans?->title,
            'description_id' => $idTrans?->excerpt,
            'description_en' => $enTrans?->excerpt,
            'target_url_id' => route('deforestory.case.laporan', [
                'locale' => 'id',
                'slug' => $this->case->slug,
                'laporanSlug' => $this->laporan->slug,
            ]),
            'target_url_en' => route('deforestory.case.laporan', [
                'locale' => 'en',
                'slug' => $this->case->slug,
                'laporanSlug' => $this->laporan->slug,
            ]),
            'published_at' => $publishedAt,
        ];
    }
}