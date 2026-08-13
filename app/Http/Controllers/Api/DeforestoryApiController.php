<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeforestoryCard;
use App\Models\DeforestoryCase;
use App\Models\DeforestoryLaporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

class DeforestoryApiController extends Controller
{
    /**
     * Daftar kasus aktif (sindikasi publik, read-only).
     * GET /api/deforestory/cases?locale=id|en
     */
    public function casesIndex(Request $request)
    {
        $locale = $this->locale($request);

        $cases = DeforestoryCase::where('status', 'active')
            ->orderBy('sort')
            ->with('translations')
            ->get();

        return response()->json([
            'data' => $cases->map(fn ($c) => $this->caseSummary($c, $locale))->values(),
            'locale' => $locale,
        ]);
    }

    /**
     * Satu kasus + daftar laporan-nya (data halaman arsip /deforestory/{slug}).
     * GET /api/deforestory/cases/{slug}?locale=id|en
     */
    public function caseShow(Request $request, string $slug)
    {
        $locale = $this->locale($request);

        $case = DeforestoryCase::where('slug', $slug)
            ->where('status', 'active')
            ->with('translations')
            ->first();

        if (! $case) {
            return response()->json(['message' => 'Case not found'], 404);
        }

        $laporans = $case->activeLaporans($locale);

        return response()->json([
            'data' => [
                ...$this->caseSummary($case, $locale),
                'laporan' => $laporans->map(fn ($l) => $this->laporanSummary($l, $case, $locale))->values(),
            ],
            'links' => [
                'index' => route('deforestory', ['locale' => $locale]),
                'archive' => route('deforestory.case', ['locale' => $locale, 'slug' => $case->slug]),
            ],
            'locale' => $locale,
        ]);
    }

    /**
     * Daftar laporan sebuah kasus (laporan terkini, urut sort).
     * GET /api/deforestory/cases/{slug}/laporan?locale=id|en
     */
    public function laporanIndex(Request $request, string $slug)
    {
        $locale = $this->locale($request);

        $case = DeforestoryCase::where('slug', $slug)->where('status', 'active')->first();

        if (! $case) {
            return response()->json(['message' => 'Case not found'], 404);
        }

        $laporans = $case->activeLaporans($locale);

        return response()->json([
            'data' => $laporans->map(fn ($l) => $this->laporanSummary($l, $case, $locale))->values(),
            'links' => [
                'archive' => route('deforestory.case', ['locale' => $locale, 'slug' => $case->slug]),
            ],
            'locale' => $locale,
        ]);
    }

    /**
     * Laporan terbaru sebuah kasus (sort tertinggi).
     * GET /api/deforestory/cases/{slug}/laporan/latest?locale=id|en
     */
    public function laporanLatest(Request $request, string $slug)
    {
        $locale = $this->locale($request);

        $case = DeforestoryCase::where('slug', $slug)->where('status', 'active')->first();

        if (! $case) {
            return response()->json(['message' => 'Case not found'], 404);
        }

        $laporan = $case->laporans()
            ->where('status', 'active')
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('locale', $locale)->orWhere('locale', 'id');
            }])
            ->reorder()
            ->orderByRaw('COALESCE(published_at, created_at) DESC, sort DESC')
            ->first();

        if (! $laporan) {
            return response()->json(['message' => 'No laporan yet'], 404);
        }

        return response()->json([
            'data' => $this->laporanSummary($laporan, $case, $locale),
            'locale' => $locale,
        ]);
    }

    /**
     * Satu laporan (metadata saja: gambar, judul, tanggal, desc, link).
     * GET /api/deforestory/cases/{slug}/laporan/{laporanSlug}?locale=id|en
     */
    public function laporanShow(Request $request, string $slug, string $laporanSlug)
    {
        $locale = $this->locale($request);

        $case = DeforestoryCase::where('slug', $slug)->where('status', 'active')->first();

        if (! $case) {
            return response()->json(['message' => 'Case not found'], 404);
        }

        $laporan = $case->laporanBySlug($locale, $laporanSlug);

        if (! $laporan) {
            return response()->json(['message' => 'Laporan not found'], 404);
        }

        return response()->json([
            'data' => $this->laporanSummary($laporan, $case, $locale),
            'locale' => $locale,
        ]);
    }

    /**
     * Satu laporan + translations id & en sekaligus (satu GET, dua locale).
     * GET /api/deforestory/cases/{slug}/laporan/{laporanSlug}/translations
     *
     * Shape mirror payload webhook keluar (DeforestoryWebhookJob::payload):
     * translations.{id|en} = {title, excerpt, link}.
     */
    public function laporanTranslations(Request $request, string $slug, string $laporanSlug)
    {
        $case = DeforestoryCase::where('slug', $slug)->where('status', 'active')->first();

        if (! $case) {
            return response()->json(['message' => 'Case not found'], 404);
        }

        // Load semua translations (id + en) sekali.
        $laporan = $case->laporans()
            ->where('status', 'active')
            ->where('slug', $laporanSlug)
            ->with('translations')
            ->first();

        if (! $laporan) {
            return response()->json(['message' => 'Laporan not found'], 404);
        }

        $translations = [];
        foreach (['id', 'en'] as $locale) {
            $t = $laporan->translation($locale);
            $translations[$locale] = [
                'title' => $t?->title,
                'excerpt' => $t?->excerpt,
                'image' => $this->laporanImage($laporan, $case, $locale),
                'link' => route('deforestory.case.laporan', [
                    'locale' => $locale,
                    'slug' => $case->slug,
                    'laporanSlug' => $laporan->slug,
                ]),
            ];
        }

        return response()->json([
            'data' => [
                'slug' => $laporan->slug,
                'sort' => $laporan->sort,
                'date' => ($laporan->published_at ?? $laporan->created_at)?->toDateString(),
                'image' => $this->laporanImage($laporan, $case, 'id'),
                'case' => [
                    'slug' => $case->slug,
                    'category' => $case->category,
                    'year' => $case->year,
                ],
                'translations' => $translations,
            ],
        ]);
    }

    /**
     * Daftar laporan sebuah kasus, diidentifikasi via UUID kartu simontini.
     * GET /api/deforestory/by-uuid/laporan/{uuid}
     *
     * Web lain (simontini) mengenal kasus via uuid card, bukan slug. Endpoint
     * ini jembatan: uuid → card → case (slug match) → daftar laporan aktif.
     *
     * Response = JSON array berisi tiap laporan dalam shape yang SAMA dengan
     * payload sync (DeforestorySyncJob::payload): {title_id, title_en,
     * description_id, description_en, image_id, image_en, target_url_id,
     * target_url_en, published_at}. Jadi consumer simontini pakai satu shape
     * untuk push & pull.
     */
    public function laporanByUuid(Request $request, string $uuid)
    {
        $card = DeforestoryCard::where('uuid', $uuid)->first();
        if (! $card) {
            return response()->json(['message' => 'Card not found for uuid'], 404);
        }

        $case = DeforestoryCase::where('slug', $card->slug)
            ->where('status', 'active')
            ->first();

        $laporans = $case
            ? $case->laporans()
                ->where('status', 'active')
                ->with('translations')
                ->orderBy('sort')
                ->get()
            : collect();

        return response()->json(
            $laporans->map(fn ($l) => $this->laporanSyncShape($l, $case))->values()
        );
    }

    // ---- Shaping helpers -------------------------------------------------

    protected function locale(Request $request): string
    {
        return in_array($request->query('locale'), ['id', 'en'], true)
            ? $request->query('locale')
            : 'id';
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

    protected function caseSummary(DeforestoryCase $case, string $locale): array
    {
        $t = $case->translation($locale) ?? $case->translation('id');

        return [
            'slug' => $case->slug,
            'category' => $case->category,
            'year' => $case->year,
            'image' => $this->imageUrl($case->featured_image),
            'title' => $t?->title,
            'excerpt' => $t?->excerpt,
            'url' => route('deforestory.case', ['locale' => $locale, 'slug' => $case->slug]),
        ];
    }

    protected function laporanSummary(DeforestoryLaporan $laporan, DeforestoryCase $case, string $locale): array
    {
        $t = $laporan->translation($locale);

        return [
            'slug' => $laporan->slug,
            'sort' => $laporan->sort,
            'title' => $t?->title,
            'date' => ($laporan->published_at ?? $laporan->created_at)?->toDateString(),
            'image' => $this->laporanImage($laporan, $case, $locale),
            'desc' => $t?->excerpt,
            'link' => route('deforestory.case.laporan', [
                'locale' => $locale,
                'slug' => $case->slug,
                'laporanSlug' => $laporan->slug,
            ]),
        ];
    }

    /**
     * Image laporan per-locale. Fallback: translation($locale)->image →
     * laporan->image (legacy) → case->featured_image.
     */
    protected function laporanImage(DeforestoryLaporan $laporan, DeforestoryCase $case, string $locale): ?string
    {
        $image = $laporan->translation($locale)?->image
            ?: $laporan->image
            ?: $case->featured_image;

        return $this->imageUrl($image);
    }

    /**
     * Balikkan jumlah job pending di queue default.
     *
     * Berguna bagi web lain yang ingin memantau beban queue CMS.
     */
    public function queueLength(Request $request)
    {
        $length = Queue::size();

        return response()->json([
            'queue' => config('queue.default'),
            'length' => $length,
        ]);
    }

    /**
     * Shape payload sync simontini (DeforestorySyncJob::payload) — 9 field:
     * title_*, description_* (= excerpt), image_* (gambar laporan per-locale,
     * fallback translation->image → laporan->image legacy → case->featured_image,
     * diresolve ke URL absolut), target_url_* (URL publik laporan per locale),
     * published_at. Dipakai endpoint by-uuid GET supaya response identik dengan
     * payload yang simontini terima via push (sync), jadi consumer gak perlu
     * kode terpisah.
     *
     * Catatan: translation('id'/'en') di model fallback ke translation pertama
     * kalau locale itu gak ada — sama persis dengan behavior sync job.
     */
    protected function laporanSyncShape(DeforestoryLaporan $laporan, DeforestoryCase $case): array
    {
        $idTrans = $laporan->translation('id');
        $enTrans = $laporan->translation('en');

        return [
            'title_id' => $idTrans?->title,
            'title_en' => $enTrans?->title,
            'description_id' => $idTrans?->excerpt,
            'description_en' => $enTrans?->excerpt,
            'image_id' => $this->laporanImage($laporan, $case, 'id'),
            'image_en' => $this->laporanImage($laporan, $case, 'en'),
            'target_url_id' => route('deforestory.case.laporan', [
                'locale' => 'id',
                'slug' => $case->slug,
                'laporanSlug' => $laporan->slug,
            ]),
            'target_url_en' => route('deforestory.case.laporan', [
                'locale' => 'en',
                'slug' => $case->slug,
                'laporanSlug' => $laporan->slug,
            ]),
            'published_at' => ($laporan->published_at ?? $laporan->created_at)?->toDateString(),
        ];
    }
}
