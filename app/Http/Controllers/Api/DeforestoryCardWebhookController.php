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
 * Web lain POST card ke CMS. Tiap POST **menambah / memperbarui** card by slug
 * (upsert) — gak menghapus card lain yang sudah ada. Jadi web lain tinggal
 * kirim card baru/berubah; card yang sudah tersimpan tetap utuh. Idempotensi
 * lewat X-Deforestory-Delivery supaya kirim ulang aman.
 *
 * Catatan: penghapusan card TIDAK ditangani endpoint ini. Kalau nanti perlu
 * hapus, tambahkan field event=deleted per card (atau endpoint DELETE terpisah).
 *
 * Endpoint: POST /api/deforestory/cards (route di routes/api.php, middleware
 * `api` saja). NOTE: auth sementara DIMATIKAN untuk testing — endpoint publik.
 * Nyalakan `deforestory.api` (Bearer = DEFORESTORY_API_KEY) sebelum produksi.
 */
class DeforestoryCardWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Autentikasi (Bearer token = DEFORESTORY_API_KEY) sudah divalidasi oleh
        // middleware `deforestory.api`. Di sini cuma proses payload.

        // Idempotensi — cegah proses dobel kalau web lain retry.
        // X-Deforestory-Delivery = UUID pengiriman; unik per POST.
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

        // Upsert by slug: tambah kalau slug baru, update kalau sudah ada.
        // Gak hapus card lain (mode tambah/update, bukan full-list replace).
        $stored = 0;
        $seen = [];
        DB::transaction(function () use ($cards, &$stored, &$seen) {
            foreach ($cards as $card) {
                $slug = $card['slug'] ?? null;
                if (! is_string($slug) || $slug === '' || isset($seen[$slug])) {
                    continue;
                }
                $seen[$slug] = true;

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

        if ($stored === 0) {
            return response()->json(['message' => 'Invalid payload: no card slugs'], 422);
        }

        return response()->json(['received' => true, 'stored' => $stored], 200);
    }
}