<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

/**
 * Pembuat tautan di dalam halaman pratinjau Deforestory.
 *
 * Halaman pratinjau dibuka dua jenis orang, dan tautan di dalamnya harus
 * berbeda untuk masing-masing. Editor CMS masuk lewat sesinya, jadi URL polos
 * sudah cukup. Peninjau luar masuk hanya karena tanda tangan — bagi mereka
 * setiap tautan lanjutan harus ikut ditandatangani, kalau tidak halaman
 * berikutnya menolak mereka.
 *
 * Kelas ini ada karena kekeliruannya sulit terlihat: satu tautan polos yang
 * terlewat baru ketahuan saat ada yang menekannya, dan hanya bagi peninjau
 * luar — di layar editor semuanya tampak baik-baik saja.
 */
class DeforestoryPreviewLink
{
    /**
     * Masa berlaku yang diwarisi dari tautan yang sedang dibuka.
     *
     * Menghitung ulang akan membuat setiap klik memperpanjang umur tautan
     * yang sudah dibagikan — pratinjau yang mestinya mati dalam seminggu jadi
     * hidup selama masih ada yang membukanya.
     */
    public function kedaluwarsa(): Carbon
    {
        $expires = request()?->integer('expires');

        return $expires ? Carbon::createFromTimestamp($expires) : now()->addDays(7);
    }

    /**
     * Tautan lanjutan perlu tanda tangan?
     *
     * Diputuskan dari cara pengunjung ini masuk, bukan dari perannya: yang
     * datang membawa tanda tangan harus terus membawanya.
     */
    public function perluTandaTangan(): bool
    {
        return (bool) request()?->hasValidSignature();
    }

    public function kasus(string $caseSlug, string $locale): string
    {
        $parameter = ['caseSlug' => $caseSlug, 'locale' => $locale];

        return $this->perluTandaTangan()
            ? URL::temporarySignedRoute('deforestory.case.preview', $this->kedaluwarsa(), $parameter)
            : route('deforestory.case.preview', $parameter);
    }

    public function laporan(string $caseSlug, string $laporanSlug, string $locale): string
    {
        $parameter = ['caseSlug' => $caseSlug, 'laporanSlug' => $laporanSlug, 'locale' => $locale];

        return $this->perluTandaTangan()
            ? URL::temporarySignedRoute('deforestory.laporan.preview', $this->kedaluwarsa(), $parameter)
            : route('deforestory.laporan.preview', $parameter);
    }

    /**
     * URL unlock — selalu bertanda tangan.
     *
     * Berbeda dari yang lain: gerbang password justru hanya ditemui pengunjung
     * yang belum terbukti apa-apa, jadi tidak ada keadaan di mana URL polos
     * pantas dipakai di sini.
     */
    public function unlock(string $caseSlug, string $laporanSlug): string
    {
        return URL::temporarySignedRoute('deforestory.laporan.preview.unlock', $this->kedaluwarsa(), [
            'caseSlug' => $caseSlug,
            'laporanSlug' => $laporanSlug,
        ]);
    }

    /** Tautan untuk dibagikan dari CMS. Selalu baru, selalu bertanda tangan. */
    public function bagikan(string $caseSlug, ?string $laporanSlug = null, string $locale = 'id', int $hari = 7): string
    {
        $kedaluwarsa = now()->addDays($hari);

        return $laporanSlug === null
            ? URL::temporarySignedRoute('deforestory.case.preview', $kedaluwarsa, [
                'caseSlug' => $caseSlug, 'locale' => $locale,
            ])
            : URL::temporarySignedRoute('deforestory.laporan.preview', $kedaluwarsa, [
                'caseSlug' => $caseSlug, 'laporanSlug' => $laporanSlug, 'locale' => $locale,
            ]);
    }
}
