<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            : asset('storage/' . $path);
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
}
