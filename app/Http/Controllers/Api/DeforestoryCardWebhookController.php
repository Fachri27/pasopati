<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\DeforestoryCardNotificationJob;
use App\Models\DeforestoryCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Terima push daftar kartu kasus Deforestory dari web lain (inbound webhook).
 *
 * Web lain POST card ke CMS. Tiap POST **menambah / memperbarui** card (upsert)
 * — gak menghapus card lain yang sudah ada. Jadi web lain tinggal kirim card
 * baru/berubah; card yang sudah tersimpan tetap utuh. Idempotensi lewat
 * X-Deforestory-Delivery supaya kirim ulang aman.
 *
 * Key upsert: `uuid` kalau dikirim caller (stabil & portabel antar env), fallback
 * `slug` kalau uuid gak ada (payload lama tetap jalan). uuid DIMILIKI caller —
 * BUKAN di-auto-generate server. slug jadi field biasa yang boleh berubah saat
 * title berubah. PUT /cards/{uuid} alamat by uuid — jadi caller dianjurkan
 * kirim uuid tiap push supaya card bisa di-PUT by uuid (card yang di-push tanpa
 * uuid punya uuid null & gak bisa di-PUT by uuid sampai di-push ulang dgn uuid).
 *
 * Catatan: penghapusan card TIDAK ditangani endpoint ini. Kalau nanti perlu
 * hapus, tambahkan field event=deleted per card (atau endpoint DELETE terpisah).
 *
 * Tiap card BARU (key belum pernah ada) memicu DeforestoryCardNotificationJob
 * (queue) → email subscriber aktif type `all` lewat DeforestoryCardMail. Update
 * card yang sudah ada gak memicu email (biar gak spam). Butuh `queue:work` jalan.
 *
 * Endpoint: POST /api/deforestory/cards (route di routes/api.php, middleware
 * `api` saja). NOTE: auth sementara DIMATIKAN untuk testing — endpoint publik.
 * Nyalakan `deforestory.api` (Bearer = DEFORESTORY_API_KEY) sebelum produksi.
 */
class DeforestoryCardWebhookController extends Controller
{
    /** Nilai `status` yang dikenal. 'publish' = tampil di publik, 'draft' = sembunyi. */
    private const STATUSES = ['publish', 'draft'];

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

        // Upsert: key = uuid kalau dikirim caller (stabil & portabel), fallback slug
        // kalau uuid gak ada (payload lama tetap jalan). slug wajib (kolom NOT NULL
        // unique) & jadi field biasa. Gak hapus card lain (mode tambah/update, bukan
        // full-list replace). Card BARU (wasRecentlyCreated) dikumpulkan utk email.
        $stored = 0;
        $seen = [];
        $newCards = [];
        $storedCards = [];
        DB::transaction(function () use ($cards, &$stored, &$seen, &$newCards, &$storedCards) {
            foreach ($cards as $card) {
                $uuid = $card['uuid'] ?? null;
                $slug = $card['slug'] ?? null;
                $hasUuid = is_string($uuid) && $uuid !== '';

                // slug wajib (NOT NULL unique). Tanpa slug → gak bisa simpan.
                if (! is_string($slug) || $slug === '') {
                    continue;
                }

                // Key upsert: uuid kalau ada, fallback slug. Dedup per key.
                $seenKey = $hasUuid ? "u:{$uuid}" : "s:{$slug}";
                if (isset($seen[$seenKey])) {
                    continue;
                }
                $seen[$seenKey] = true;

                // Upsert by uuid: pastikan slug gak dipakai card lain (uuid beda /
                // null) → hindari pelanggaran unique slug. Skip bila bentrok.
                if ($hasUuid && DeforestoryCard::where('slug', $slug)
                        ->where(fn ($q) => $q->where('uuid', '!=', $uuid)->orWhereNull('uuid'))
                        ->exists()) {
                    continue;
                }

                $key = $hasUuid ? ['uuid' => $uuid] : ['slug' => $slug];
                $attributes = [
                    'slug'       => $slug,
                    'category'   => $card['category']   ?? null,
                    'year'       => $card['year']       ?? null,
                    'image_id'   => $card['image_id']   ?? null,
                    'image_en'   => $card['image_en']   ?? null,
                    'title_id'   => $card['title_id']   ?? null,
                    'title_en'   => $card['title_en']   ?? null,
                    'excerpt_id' => $card['excerpt_id'] ?? null,
                    'excerpt_en' => $card['excerpt_en'] ?? null,
                    'sort'       => $card['sort']       ?? 0,
                ];
                if ($hasUuid) {
                    $attributes['uuid'] = $uuid;
                }
                // status opsional di POST: kirim hanya kalau caller minta & valid.
                // Kalau gak dikirim → create dapat default 'publish', update mempertahankan
                // status lama (visibility gak ke-reset oleh push konten biasa).
                if (array_key_exists('status', $card) && in_array($card['status'], self::STATUSES, true)) {
                    $attributes['status'] = $card['status'];
                }

                $model = DeforestoryCard::updateOrCreate($key, $attributes);

                // Hanya card BARU yang PUBLISH yang memicu email — card draft
                // (di-push tersembunyi) gak boleberitau subscriber sebelum tampil.
                // refresh() supaya baca status persisten (default 'publish' dari DB
                // kalau caller gak kirim status — in-memory model belum tau itu).
                if ($model->wasRecentlyCreated) {
                    $model->refresh();
                    if ($model->status === 'publish') {
                        $newCards[] = $model;
                    }
                }

                // Echo balik slug + uuid tiap card tersimpan (uuid null bila
                // caller gak kirim — card itu gak bisa di-PUT by uuid).
                $storedCards[] = ['slug' => $model->slug, 'uuid' => $model->uuid];

                $stored++;
            }
        });

        // Dispatch job notifikasi SETELAH commit (biar worker gak jalan sebelum
        // data tersimpan). Hanya card baru; update gak memicu email.
        foreach ($newCards as $newCard) {
            DeforestoryCardNotificationJob::dispatch($newCard);
        }

        if ($stored === 0) {
            return response()->json(['message' => 'Invalid payload: no card uuids or slugs'], 422);
        }

        return response()->json([
            'received' => true,
            'stored' => $stored,
            'notified' => count($newCards),
            'cards' => $storedCards,
        ], 200);
    }

    /**
     * Perbarui satu kartu kasus by UUID (update murni — tidak membuat baru).
     * PUT|PATCH /api/deforestory/cards/{uuid}
     *
     * Pakai UUID — BUKAN slug dan BUKAN id auto-increment — sebagai identifier.
     * slug boleh berubah (mis. diturunkan dari title), jadi kalau dipakai sebagai
     * address jadi stale begitu title berubah. id auto-increment stabil di satu
     * DB, tapi beda antara dev & produksi (gak portable). UUID stabil DAN portabel
     * antar environment — pilihan yang aman untuk identifier keluar.
     *
     * Berbeda dari POST /cards yang upsert (buat + update) sekaligus memicu
     * email untuk card BARU: endpoint ini HANYA meng-update card yang sudah
     * ada. Bila uuid belum pernah ada → 404 (tidak dibuat, tidak ada notifikasi).
     * Update tidak pernah memicu email supaya tidak spam (sama seperti POST
     * ketika meng-update card yang sudah ada).
     *
     * Partial update: hanya field yang dikirim yang ditimpa; field yang tidak
     * dikirim tetap. Kirim `null` eksplisit untuk mengosongkan sebuah field.
     *
     * Field yang diterima: slug, status, category, year, image_id, image_en,
     * title_id, title_en, excerpt_id, excerpt_en, sort. slug sekarang updatable
     * (identifier = uuid, bukan slug). status kendalikan visibilitas ('publish' /
     * 'draft') — PUT inilah jalur utama publish/unpublish card. Alias singkat
     * `title` → title_id dan `excerpt` → excerpt_id (locale default 'id'), supaya
     * caller yang membaca shape
     * {title, excerpt} dari toCardArray() bisa PUT balik apa adanya. Bila tidak
     * ada field yang dikenali sama sekali → 422 (bukan silent success).
     *
     * AUTO-SLUG: bila title_id (atau alias `title`) BERUBAH dan slug tidak dikirim
     * eksplisit, slug otomatis = Str::slug(title_id) — slug mengikuti title terbaru
     * (konsisten dengan form Page/Fellowship/Petition). Kirim `slug` eksplisit
     * (flat, tanpa title) bila mau paksa rename slug tertentu. Update field lain
     * (mis. category saja) TIDAK mengubah slug. Bentrok slug → 422.
     *
     * Mendukung DUA bentuk body, biar konsisten dengan POST /cards:
     *   1. Flat:     {"title_id": "...", "category": "..."}   (field di top level)
     *   2. Wrapped:  {"cards": [{ "slug": "...", ... }]}     (shape sama dengan POST)
     * Bentuk wrapped: pakai entry pertama — card target ditentukan oleh {uuid} di
     * URL. slug di entry DIABAIKAN bila entry membawa title (slug di POST =
     * identifier, bukan intent set slug) supaya slug mengikuti title; slug dihormati
     * hanya bila entry TANPA title (rename eksplisit).
     *
     * NOTE: auth sementara DIMATIKAN untuk testing — sejajar dengan POST /cards.
     * Nyalakan `->middleware('deforestory.api')` (Bearer = DEFORESTORY_API_KEY)
     * sebelum dipakai beneran di produksi.
     */
    public function update(Request $request, string $uuid)
    {
        $card = DeforestoryCard::where('uuid', $uuid)->first();

        if (! $card) {
            return response()->json(['message' => 'Card not found'], 404);
        }

        // Field yang boleh diubah. identifier = uuid di URL; slug kini updatable
        // (slug boleh ikut berubah saat title berubah). status kendalikan visibilitas.
        $fields = ['slug', 'status', 'category', 'year', 'image_id', 'image_en', 'title_id', 'title_en', 'excerpt_id', 'excerpt_en', 'sort'];

        $input = $request->input();

        // Bentuk wrapped {"cards": [...]} (shape sama dengan POST). Pakai entry
        // pertama — card target ditentukan oleh {id} di URL.
        if (isset($input['cards']) && is_array($input['cards'])) {
            $entry = $input['cards'][0] ?? [];

            // slug di wrapped POST-shape adalah IDENTIFIER (untuk upsert POST),
            // bukan intent "set slug" di PUT-by-id. Kalau entry juga membawa
            // title, ignore slug-nya supaya slug mengikuti title terbaru. Kalau
            // entry HANYA bawa slug (tanpa title) → anggap rename eksplisit.
            if (array_key_exists('title_id', $entry) || array_key_exists('title', $entry)) {
                unset($entry['slug']);
            }

            $input = $entry;
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

        // AUTO-SLUG: bila title_id BERUBAH (nilai baru ≠ title_id lama) dan slug
        // tidak dikirim eksplisit, slug mengikuti title terbaru = Str::slug(title_id),
        // konsisten dengan form Page/Fellowship/Petition. Kirim `slug` eksplisit
        // (flat) bila mau paksa slug tertentu. Update field lain (mis. category
        // saja) TIDAK mengubah slug.
        if (array_key_exists('title_id', $updates)
            && ! array_key_exists('slug', $updates)
            && $updates['title_id'] !== $card->title_id) {
            $updates['slug'] = Str::slug($updates['title_id']);
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

        // status harus nilai yang dikenal (active/inactive) — cegah typo sembunyiin
        // card tanpa sengaja.
        if (array_key_exists('status', $updates) && ! in_array($updates['status'], self::STATUSES, true)) {
            return response()->json([
                'message' => 'Invalid status',
                'accepted' => self::STATUSES,
            ], 422);
        }

        // Tidak ada field yang dikenali → tolak keras, jangan pura-pura sukses.
        if (! $updates) {
            return response()->json([
                'message' => 'No updatable fields provided',
                'accepted' => [
                    'slug', 'status', 'category', 'year', 'image_id', 'image_en',
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
                'id', 'uuid', 'slug', 'status', 'category', 'year', 'image_id', 'image_en',
                'title_id', 'title_en', 'excerpt_id', 'excerpt_en',
                'sort', 'updated_at',
            ]),
        ], 200);
    }
}