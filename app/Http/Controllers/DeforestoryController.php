<?php

namespace App\Http\Controllers;

use App\Models\DeforestoryCase;
use App\Models\DeforestorySubscriber;
use App\Services\DeforestoryApiService;
use App\Support\DeforestoryPreviewLock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class DeforestoryController extends Controller
{
    public function __construct(protected DeforestoryApiService $api) {}

    /**
     * Arsip index: daftar kartu kasus dari tabel lokal (didorong web lain
     * via inbound webhook /api/deforestory/cards).
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
    /**
     * Pratinjau arsip kasus untuk editor.
     * GET /admin/deforestory/{caseSlug}/preview/{locale?}
     *
     * Memakai view publik yang sama persis, tapi tanpa saringan status: kasus
     * dan laporan yang masih draft ikut tampil. Justru itu gunanya — yang
     * perlu diperiksa sebelum terbit adalah yang BELUM terbit, dan lewat
     * halaman publik hal itu mustahil dilihat.
     *
     * Route-nya berada di balik middleware auth, dan viewnya memasang spanduk
     * pratinjau supaya halaman ini tidak pernah tertukar dengan yang publik.
     */
    public function previewCase($caseSlug, $locale = null)
    {
        $locale = in_array($locale, ['id', 'en'], true) ? $locale : 'id';
        app()->setLocale($locale);

        $case = DeforestoryCase::where('slug', $caseSlug)->firstOrFail();

        $laporans = $case->laporans()
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('locale', $locale)->orWhere('locale', 'id');
            }])
            ->orderBy('sort')
            ->get();

        $apiCard = $this->findApiCard($locale, $caseSlug);

        return view('front.deforestory-case-archive', [
            'case' => $case,
            'apiTitle' => $apiCard['title'] ?? null,
            'laporans' => $laporans,
            'preview' => true,
        ]);
    }

    /**
     * Pratinjau satu laporan, pasangan dari previewCase().
     * GET /admin/deforestory/{caseSlug}/preview/{laporanSlug}/{locale?}
     *
     * Tanpa ini, tautan laporan draft di halaman pratinjau arsip akan
     * memantul balik ke arsip publik — pratinjau yang tidak bisa dibuka isinya
     * tidak banyak menolong.
     */
    public function previewLaporan($caseSlug, $laporanSlug, $locale = null)
    {
        $locale = in_array($locale, ['id', 'en'], true) ? $locale : 'id';
        app()->setLocale($locale);

        $case = DeforestoryCase::where('slug', $caseSlug)->firstOrFail();
        $laporan = $case->laporans()->where('slug', $laporanSlug)->with('translations')->firstOrFail();

        $kunci = app(DeforestoryPreviewLock::class);

        if ($laporan->is_locked && ! $kunci->terbuka(request())) {
            return view('front.deforestory-preview-unlock', [
                'case' => $case,
                'laporan' => $laporan,
                'locale' => $locale,
                'belumDiatur' => ! $kunci->sudahDiatur(),
                // Ikut ditandatangani: route unlock berada di grup yang sama,
                // jadi kiriman dari peninjau luar (yang tidak punya sesi CMS)
                // hanya lolos kalau membawa tanda tangan yang sah.
                'unlockUrl' => self::tautanUnlock($case->slug, $laporan->slug),
            ]);
        }

        $apiCard = $this->findApiCard($locale, $caseSlug);

        return view('front.deforestory-case-laporan', [
            'case' => $case,
            'laporan' => $laporan,
            'laporanTrans' => $laporan->translation($locale),
            'apiTitle' => $apiCard['title'] ?? null,
            'preview' => true,
        ]);
    }

    /**
     * Memeriksa password global lalu membuka kunci pratinjau untuk sesi ini.
     * POST /preview/deforestory/{caseSlug}/laporan/{laporanSlug}/unlock
     *
     * Dibatasi dua lapis: throttle:10,1 di route menahan banjir permintaan,
     * dan RateLimiter di bawah menahan tebakan password beruntun dari satu IP.
     */
    public function unlockPreview(Request $request, $caseSlug, $laporanSlug)
    {
        $case = DeforestoryCase::where('slug', $caseSlug)->firstOrFail();
        $laporan = $case->laporans()->where('slug', $laporanSlug)->firstOrFail();

        $kunci = app(DeforestoryPreviewLock::class);
        $tujuan = route('deforestory.laporan.preview', [
            'caseSlug' => $case->slug,
            'laporanSlug' => $laporan->slug,
            'locale' => app()->getLocale(),
        ]);

        if (! $laporan->is_locked || $kunci->terbuka($request)) {
            return redirect()->to($tujuan);
        }

        $data = $request->validate(['password' => ['required', 'string', 'max:100']]);

        $kunciBatas = 'deforestory-preview:'.$request->ip();

        if (RateLimiter::tooManyAttempts($kunciBatas, 5)) {
            throw ValidationException::withMessages([
                'password' => 'Terlalu banyak percobaan. Coba lagi dalam '
                    .RateLimiter::availableIn($kunciBatas).' detik.',
            ]);
        }

        if (! $kunci->cocok($data['password'])) {
            RateLimiter::hit($kunciBatas, 60);

            throw ValidationException::withMessages([
                'password' => $kunci->sudahDiatur()
                    ? 'Password salah.'
                    : 'Password pratinjau belum diatur di CMS.',
            ]);
        }

        RateLimiter::clear($kunciBatas);
        $kunci->tandaiTerbuka($request);

        return redirect()->to($tujuan);
    }

    /** URL unlock bertanda tangan, pasangan gerbang password. */
    public static function tautanUnlock(string $caseSlug, string $laporanSlug, int $jam = 6): string
    {
        return URL::temporarySignedRoute('deforestory.laporan.preview.unlock', now()->addHours($jam), [
            'caseSlug' => $caseSlug,
            'laporanSlug' => $laporanSlug,
        ]);
    }

    /**
     * Tautan pratinjau bertanda tangan yang bisa dibagikan ke luar CMS.
     *
     * Berumur terbatas dengan sengaja: tautan pratinjau beredar di email dan
     * ruang obrolan, dan yang berlaku selamanya berarti draft lama tetap
     * terbuka lama setelah peninjauannya selesai.
     */
    public static function tautanBagikan(string $caseSlug, ?string $laporanSlug = null, string $locale = 'id', int $hari = 7): string
    {
        $kedaluwarsa = now()->addDays($hari);

        return $laporanSlug === null
            ? URL::temporarySignedRoute('deforestory.case.preview', $kedaluwarsa, [
                'caseSlug' => $caseSlug,
                'locale' => $locale,
            ])
            : URL::temporarySignedRoute('deforestory.laporan.preview', $kedaluwarsa, [
                'caseSlug' => $caseSlug,
                'laporanSlug' => $laporanSlug,
                'locale' => $locale,
            ]);
    }

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
                : asset('storage/'.$laporan->image))
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
     * Cari kartu kasus di tabel lokal (didorong web lain via webhook) berdasarkan
     * slug. Mengembalikan array kartu {slug, title, excerpt, image, category,
     * year} atau null.
     */
    protected function findApiCard(string $locale, string $slug): ?array
    {
        return $this->api->cardBySlug($locale, $slug);
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
