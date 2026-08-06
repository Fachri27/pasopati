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
use Illuminate\Support\Str;

/**
 * Kirim laporan Deforestory ke endpoint simontini (sync keluar) saat laporan
 * di-publish atau di-unpublish.
 *
 * Beda dari DeforestoryWebhookJob (webhook keluar generik, HMAC + shape sindikasi
 * lama): simontini pakai Bearer token (bukan HMAC) + shape body sendiri
 * (external_id, deforestory_id, title_id/en, description_id/en, image_url,
 * target_url, published_at, status).
 *
 * `deforestory_id` = UUID card simontini. Case pasopati di-match ke card
 * simontini via slug (case.slug == card.slug). Kalau case gak punya card
 * simontini (atau card gak punya uuid) → skip diam-diam: laporan ini bukan
 * bagian deforestory simontini, jadi gak ada yang dituju.
 *
 * `external_id` = "pasopati-update-{laporan.id}" — id unik laporan versi
 * pasopati, dipakai simontini untuk dedup/upsert update.
 *
 * `status` = 'on' (publish — laporan jadi active) atau 'off' (unpublish —
 * turun dari active). Edit biasa laporan aktif gak memicu job ini (cuma
 * transisi active↔non-active, lihat DeforestoryLaporanForm::save).
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
        public DeforestoryLaporan $laporan,
        public string $status = 'on' // 'on' | 'off'
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

        // deforestory_id = uuid card simontini (case di-match via slug). Kalau
        // case gak punya card / card gak punya uuid → bukan case simontini, skip.
        $card = DeforestoryCard::where('slug', $this->case->slug)->first();
        if (! $card || ! $card->uuid) {
            return;
        }

        $payload = $this->payload($card);

        $response = Http::timeout($timeout)
            ->withToken($token)
            ->post($url, $payload);

        // Lempar supaya Laravel retry (tries=3) kalau simontini gagal 2xx.
        if (! $response->successful()) {
            throw new \RuntimeException(
                "Sync laporan ke simontini ({$url}) gagal: HTTP {$response->status()}"
            );
        }
    }

    /**
     * Shape body simontini (deforestory/sync). Field per-locale (id + en) diisi
     * dari DeforestoryLaporanTranslation. image_url & target_url tunggal — pakai
     * locale default 'id' (dengan fallback). description_* = excerpt laporan.
     */
    protected function payload(DeforestoryCard $card): array
    {
        $idTrans = $this->laporan->translation('id');
        $enTrans = $this->laporan->translation('en');

        // image_url tunggal: prioritas image locale id → image laporan lama →
        // cover case. Ubah path storage jadi URL absolut.
        $imagePath = $idTrans?->image ?: $this->laporan->image ?: $this->case->featured_image;

        // target_url: URL publik laporan di pasopati (locale id).
        $targetUrl = route('deforestory.case.laporan', [
            'locale' => 'id',
            'slug' => $this->case->slug,
            'laporanSlug' => $this->laporan->slug,
        ]);

        $publishedAt = ($this->laporan->published_at ?? $this->laporan->created_at)?->toDateString();

        return [
            'external_id' => 'pasopati-update-' . $this->laporan->id,
            'deforestory_id' => $card->uuid,
            'title_id' => $idTrans?->title,
            'title_en' => $enTrans?->title,
            'description_id' => $idTrans?->excerpt,
            'description_en' => $enTrans?->excerpt,
            'image_url' => $this->imageUrl($imagePath),
            'target_url' => $targetUrl,
            'published_at' => $publishedAt,
            'status' => $this->status,
        ];
    }

    /**
     * Path storage (relatif) → URL absolut. URL absolut (http/https) dilewat
     * apa adanya (mis. gambar yang sudah URL penuh).
     */
    protected function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://'])
            ? $path
            : asset('storage/' . $path);
    }
}