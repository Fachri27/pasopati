<?php

namespace App\Http\Controllers;

use App\Models\DeforestoryCase;
use App\Models\DeforestorySubscriber;
use App\Services\DeforestoryApiService;
use Illuminate\Http\Request;

class DeforestoryController extends Controller
{
    public function __construct(protected DeforestoryApiService $api) {}

    /**
     * Arsip index: daftar kartu kasus dari API eksternal (mock untuk now).
     * GET /{locale}/deforestory
     */
    public function index($locale)
    {
        $cases = $this->api->getCases($locale);

        seo()->setLocale($locale)
            ->set('title', ['id' => 'Deforestory — Arsip Kasus', 'en' => 'Deforestory — Case Archive'])
            ->set('description', ['id' => 'Kisah deforestasi, konflik lahan, dan kerusakan ekosistem Indonesia dari citra satelit dan catatan lapangan.', 'en' => 'Stories of deforestation, land conflict, and ecological damage in Indonesia.'])
            ->set('image', asset('img/image.png'))
            ->set('type', 'website');

        return view('front.deforestory', compact('cases'));
    }

    /**
     * Halaman arsip kasus = "rumah": judul (dari kartu API) + daftar
     * laporan (kartu laporan) → /{slug}/{laporanSlug}. Bila slug terdaftar
     * di API tapi belum ada konten CMS, tampilkan judul dari kartu API
     * saja + "Belum ada laporan".
     * GET /{locale}/deforestory/{slug}
     */
    public function show($locale, $slug)
    {
        $case = DeforestoryCase::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (! $case) {
            $card = $this->findApiCard($locale, $slug);

            if ($card) {
                return $this->previewFromApi($locale, $card);
            }

            return $this->emptyArchiveResponse($locale, $slug);
        }

        $meta = $case->getSeoData($locale);

        seo()->setLocale($locale)
            ->set('title', ['id' => $meta['title'], 'en' => $meta['title']])
            ->set('description', ['id' => $meta['description'], 'en' => $meta['description']])
            ->set('image', $meta['image'])
            ->set('type', $meta['type']);

        // Judul header selalu diambil dari kartu API (bukan CMS), bila ada.
        $apiCard = $this->findApiCard($locale, $slug);
        $apiTitle = $apiCard['title'] ?? null;

        // Daftar laporan aktif untuk locale terkini.
        $laporans = $case->activeLaporans($locale);

        return view('front.deforestory-case-archive', [
            'case' => $case,
            'apiTitle' => $apiTitle,
            'laporans' => $laporans,
        ]);
    }

    /**
     * Halaman detail satu laporan kasus (CMS lokal). Laporan di-match via
     * slug di dalam kasus. Judul kasus (breadcrumb) dari kartu API.
     * GET /{locale}/deforestory/{slug}/{laporanSlug}
     */
    public function laporan($locale, $slug, $laporanSlug)
    {
        $case = DeforestoryCase::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        // Tidak ada kasus CMS → arahkan ke arsip (yang akan tampilkan
        // preview "belum ada laporan" bila slug ada di API).
        if (! $case) {
            return redirect()->route('deforestory.case', ['locale' => $locale, 'slug' => $slug]);
        }

        $laporan = $case->laporanBySlug($locale, $laporanSlug);

        // Laporan tidak ditemukan / tidak aktif → kembali ke arsip kasus.
        if (! $laporan) {
            return redirect()->route('deforestory.case', ['locale' => $locale, 'slug' => $slug]);
        }

        $meta = $case->getSeoData($locale);

        // SEO per-laporan: pakai judul laporan bila ada, fallback kasus.
        $laporanTrans = $laporan->translation($locale);
        $laporanTitle = $laporanTrans->title ?? null;
        $laporanExcerpt = $laporanTrans->excerpt ?? null;
        $laporanImage = $laporan->image
            ? (\Illuminate\Support\Str::startsWith($laporan->image, ['http://', 'https://'])
                ? $laporan->image
                : asset('storage/' . $laporan->image))
            : $meta['image'];

        seo()->setLocale($locale)
            ->set('title', ['id' => $laporanTitle ?? $meta['title'], 'en' => $laporanTitle ?? $meta['title']])
            ->set('description', ['id' => $laporanExcerpt ?? $meta['description'], 'en' => $laporanExcerpt ?? $meta['description']])
            ->set('image', $laporanImage)
            ->set('type', 'article');

        // Judul kasus (breadcrumb) dari kartu API bila ada; fallback CMS.
        $apiCard = $this->findApiCard($locale, $slug);
        $apiTitle = $apiCard['title'] ?? null;

        return view('front.deforestory-case-laporan', [
            'case' => $case,
            'laporan' => $laporan,
            'laporanTrans' => $laporanTrans,
            'apiTitle' => $apiTitle,
        ]);
    }

    /**
     * Berhenti berlangganan Deforestory.
     */
    public function unsubscribe(Request $request, string $token)
    {
        $subscriber = DeforestorySubscriber::where('unsubscribe_token', $token)->first();

        if (! $subscriber) {
            return redirect()->route('deforestory', ['locale' => 'id'])
                ->with('error', 'Link berhenti berlangganan tidak valid.');
        }

        $subscriber->update(['active' => false]);

        return redirect()->route('deforestory', ['locale' => $subscriber->locale])
            ->with('success', 'Anda berhasil berhenti berlangganan.');
    }

    /**
     * Cari kartu kasus di API (mock) berdasarkan slug. Mengembalikan array
     * kartu {slug, title, excerpt, image, category, year} atau null.
     */
    protected function findApiCard(string $locale, string $slug): ?array
    {
        foreach ($this->api->getCases($locale) as $card) {
            if (($card['slug'] ?? null) === $slug) {
                return $card;
            }
        }

        return null;
    }

    /**
     * Slug terdaftar di API tapi belum ada konten CMS → tampilkan judul
     * dari kartu API sebagai halaman preview + "Belum ada laporan".
     */
    protected function previewFromApi(string $locale, array $card)
    {
        $title = $card['title'] ?? '';

        seo()->setLocale($locale)
            ->set('title', ['id' => $title, 'en' => $title])
            ->set('description', ['id' => $card['excerpt'] ?? '', 'en' => $card['excerpt'] ?? ''])
            ->set('image', $card['image'] ?? asset('img/image.png'))
            ->set('type', 'article');

        return view('front.deforestory-case-preview', [
            'locale' => $locale,
            'slug' => $card['slug'] ?? '',
            'title' => $title,
        ]);
    }

    /**
     * Saat slug dari API belum memiliki konten arsip/laporan di CMS.
     */
    protected function emptyArchiveResponse($locale, $slug)
    {
        seo()->setLocale($locale)
            ->set('title', ['id' => 'Arsip belum tersedia', 'en' => 'Archive not available yet'])
            ->set('type', 'website');

        return view('front.deforestory-case-empty', [
            'slug' => $slug,
            'locale' => $locale,
        ]);
    }
}