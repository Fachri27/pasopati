<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeforestoryCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Terima push daftar kartu kasus Deforestory dari web lain (inbound webhook).
 *
 * Sebaliknya dulu CMS nge-GET card dari web lain (mock /api/deforestory-cases);
 * sekarang web lain yang POST seluruh daftar card ke CMS. CMS verifikasi
 * signature HMAC SHA256 (header X-Deforestory-Signature) pakai
 * DEFORESTORY_CARD_WEBHOOK_SECRET, lalu replace tabel deforestory_cards
 * (full-list sync): card yang tidak ada di payload dihapus, sisanya upsert
 * by slug. Idempotensi lewat X-Deforestory-Delivery supaya kirim ulang aman.
 *
 * Endpoint: POST /api/deforestory/cards (route di routes/api.php, middleware
 * `api` saja — tanpa CSRF & tanpa Bearer token; autentikasi = signature).
 */
class DeforestoryCardWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = config('services.deforestory_api.card_webhook_secret');

        // 1) Verifikasi signature HMAC — pakai body RAW, bukan $request->all().
        //    Inbound wajib ditandatangani; tanpa secret konfigurasi → tolak.
        $signature = $request->header('X-Deforestory-Signature', '');
        if (! $secret || empty($signature)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);
        if (! hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        // 2) Idempotensi — cegah proses dobel kalau web lain retry.
        //    X-Deforestory-Delivery = UUID pengiriman; unik per POST.
        $delivery = $request->header('X-Deforestory-Delivery');
        $lockKey = "deforestory-card-webhook:{$delivery}";
        if ($delivery && ! Cache::add($lockKey, 1, now()->addHour())) {
            return response()->json(['received' => true, 'dedup' => true]);
        }

        $payload = $request->input();
        $cards = $payload['cards'] ?? $payload['data'] ?? null;

        if (! is_array($cards)) {
            return response()->json(['message' => 'Invalid payload: cards required'], 422);
        }

        // 3) Full-list replace dalam transaksi: hapus card yang tidak ada di
        //    payload, upsert sisanya by slug.
        $slugs = [];
        foreach ($cards as $card) {
            $slug = $card['slug'] ?? null;
            if (is_string($slug) && $slug !== '') {
                $slugs[] = $slug;
            }
        }

        if (empty($slugs)) {
            return response()->json(['message' => 'Invalid payload: no card slugs'], 422);
        }

        $stored = 0;
        DB::transaction(function () use ($cards, $slugs, &$stored) {
            // Hapus card lama yang tidak ada di payload ini (full-list sync).
            DeforestoryCard::whereNotIn('slug', $slugs)->delete();

            foreach ($cards as $card) {
                $slug = $card['slug'] ?? null;
                if (! is_string($slug) || $slug === '') {
                    continue;
                }

                DeforestoryCard::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'category'   => $card['category']   ?? null,
                        'year'       => $card['year']       ?? null,
                        'image'      => $card['image']      ?? null,
                        'title_id'   => $card['title_id']   ?? null,
                        'title_en'   => $card['title_en']   ?? null,
                        'excerpt_id' => $card['excerpt_id'] ?? null,
                        'excerpt_en' => $card['excerpt_en'] ?? null,
                        'sort'       => $card['sort']       ?? 0,
                    ]
                );

                $stored++;
            }
        });

        return response()->json(['received' => true, 'stored' => $stored], 200);
    }
}