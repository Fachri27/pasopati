<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\DeforestoryCardNotificationJob;
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
 * Tiap card BARU (slug belum pernah ada) memicu DeforestoryCardNotificationJob
 * (queue) → email subscriber aktif type `all` lewat DeforestoryCardMail. Update
 * card yang sudah ada gak memicu email (biar gak spam). Butuh `queue:work` jalan.
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
        // Card yang BARU (wasRecentlyCreated) dikumpulkan untuk di-email ke subscriber.
        $stored = 0;
        $seen = [];
        $newCards = [];
        DB::transaction(function () use ($cards, &$stored, &$seen, &$newCards) {
            foreach ($cards as $card) {
                $slug = $card['slug'] ?? null;
                if (! is_string($slug) || $slug === '' || isset($seen[$slug])) {
                    continue;
                }
                $seen[$slug] = true;

                $model = DeforestoryCard::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'category'   => $card['category']   ?? null,
                        'year'       => $card['year']       ?? null,
                        'image_id'   => $card['image_id']   ?? null,
                        'image_en'   => $card['image_en']   ?? null,
                        'title_id'   => $card['title_id']   ?? null,
                        'title_en'   => $card['title_en']   ?? null,
                        'excerpt_id' => $card['excerpt_id'] ?? null,
                        'excerpt_en' => $card['excerpt_en'] ?? null,
                        'sort'       => $card['sort']       ?? 0,
                    ]
                );

                if ($model->wasRecentlyCreated) {
                    $newCards[] = $model;
                }

                $stored++;
            }
        });

        // Dispatch job notifikasi SETELAH commit (biar worker gak jalan sebelum
        // data tersimpan). Hanya card baru; update gak memicu email.
        foreach ($newCards as $newCard) {
            DeforestoryCardNotificationJob::dispatch($newCard);
        }

        if ($stored === 0) {
            return response()->json(['message' => 'Invalid payload: no card slugs'], 422);
        }

        return response()->json([
            'received' => true,
            'stored' => $stored,
            'notified' => count($newCards),
        ], 200);
    }

    /**
     * Perbarui satu kartu kasus by ID (update murni — tidak membuat baru).
     * PUT|PATCH /api/deforestory/cards/{id}
     *
     * Pakai ID (primary key) — BUKAN slug — sebagai identifier, karena slug
     * boleh berubah (mis. diturunkan dari title). Kalau slug dipakai sebagai
     * address, address jadi stale begitu title/slug berubah. ID stabil.
     *
     * Berbeda dari POST /cards yang upsert (buat + update) sekaligus memicu
     * email untuk card BARU: endpoint ini HANYA meng-update card yang sudah
     * ada. Bila id belum pernah ada → 404 (tidak dibuat, tidak ada notifikasi).
     * Update tidak pernah memicu email supaya tidak spam (sama seperti POST
     * ketika meng-update card yang sudah ada).
     *
     * Partial update: hanya field yang dikirim yang ditimpa; field yang tidak
     * dikirim tetap. Kirim `null` eksplisit untuk mengosongkan sebuah field.
     *
     * Field yang diterima: slug, category, year, image_id, image_en, title_id,
     * title_en, excerpt_id, excerpt_en, sort. slug sekarang updatable (identifier
     * = id, bukan slug). Alias singkat `title` → title_id dan `excerpt` →
     * excerpt_id (locale default 'id'), supaya caller yang membaca shape
     * {title, excerpt} dari toCardArray() bisa PUT balik apa adanya. Bila tidak
     * ada field yang dikenali sama sekali → 422 (bukan silent success).
     *
     * Mendukung DUA bentuk body, biar konsisten dengan POST /cards:
     *   1. Flat:     {"title_id": "...", "category": "..."}   (field di top level)
     *   2. Wrapped:  {"cards": [{ "slug": "...", ... }]}     (shape sama dengan POST)
     * Bentuk wrapped: pakai entry pertama — card target ditentukan oleh {id} di
     * URL, bukan slug di body.
     *
     * NOTE: auth sementara DIMATIKAN untuk testing — sejajar dengan POST /cards.
     * Nyalakan `->middleware('deforestory.api')` (Bearer = DEFORESTORY_API_KEY)
     * sebelum dipakai beneran di produksi.
     */
    public function update(Request $request, string $id)
    {
        $card = DeforestoryCard::where('id', $id)->first();

        if (! $card) {
            return response()->json(['message' => 'Card not found'], 404);
        }

        // Field yang boleh diubah. identifier = id di URL; slug kini updatable
        // (slug boleh ikut berubah saat title berubah).
        $fields = ['slug', 'category', 'year', 'image_id', 'image_en', 'title_id', 'title_en', 'excerpt_id', 'excerpt_en', 'sort'];

        $input = $request->input();

        // Bentuk wrapped {"cards": [...]} (shape sama dengan POST). Pakai entry
        // pertama — card target ditentukan oleh {id} di URL.
        if (isset($input['cards']) && is_array($input['cards'])) {
            $input = $input['cards'][0] ?? [];
        }

        // Pakai array_key_exists supaya nilai `null` eksplisit tetap diterapkan
        // (membedakan "field tidak dikirim" dari "field dikirim null").
        $updates = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $input)) {
                $updates[$field] = $input[$field];
            }
        }

        // Alias singkat → varian _id (locale default 'id'). Hanya berlaku bila
        // varian _id eksplisit TIDAK dikirim, supaya _id eksplisit selalu menang.
        if (array_key_exists('title', $input) && ! array_key_exists('title_id', $input)) {
            $updates['title_id'] = $input['title'];
        }
        if (array_key_exists('excerpt', $input) && ! array_key_exists('excerpt_id', $input)) {
            $updates['excerpt_id'] = $input['excerpt'];
        }

        // slug unik & NOT NULL — validasi sebelum update supaya gak 500.
        if (array_key_exists('slug', $updates)) {
            if (blank($updates['slug'])) {
                return response()->json(['message' => 'Slug cannot be empty'], 422);
            }
            if ($updates['slug'] !== $card->slug
                && DeforestoryCard::where('slug', $updates['slug'])->whereKeyNot($card->id)->exists()) {
                return response()->json(['message' => 'Slug already in use'], 422);
            }
        }

        // Tidak ada field yang dikenali → tolak keras, jangan pura-pura sukses.
        if (! $updates) {
            return response()->json([
                'message' => 'No updatable fields provided',
                'accepted' => [
                    'slug', 'category', 'year', 'image_id', 'image_en',
                    'title', 'title_id', 'title_en',
                    'excerpt', 'excerpt_id', 'excerpt_en',
                    'sort',
                ],
            ], 422);
        }

        $card->update($updates);

        return response()->json([
            'received' => true,
            'updated' => true,
            'card' => $card->fresh()->only([
                'id', 'slug', 'category', 'year', 'image_id', 'image_en',
                'title_id', 'title_en', 'excerpt_id', 'excerpt_en',
                'sort', 'updated_at',
            ]),
        ], 200);
    }
}