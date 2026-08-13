<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sepulang login Google, pengunjung harus kembali ke halaman tempat ia menekan
 * "Masuk" — bukan terlempar ke beranda.
 *
 * Dulu URL kembalinya dibandingkan dengan config('app.url'), jadi begitu host
 * yang dibuka berbeda dari APP_URL (server dev, staging, akses lewat IP,
 * http vs https) tujuannya dibuang diam-diam. Sekarang pembandingnya host
 * permintaan itu sendiri.
 */
class GoogleCommentLoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function intendedSetelah(string $intended): ?string
    {
        // Jalur relatif, bukan route() yang absolut: URL absolut menetapkan
        // host permintaan dan menimpa HTTP_HOST yang dipasang di tes.
        $this->get('/comment/login/google?intended='.urlencode($intended));

        return session()->pull('url.intended');
    }

    public function test_relative_path_is_kept(): void
    {
        $this->assertSame('/id/fire?laporan=12', $this->intendedSetelah('/id/fire?laporan=12'));
    }

    /**
     * Inti perbaikannya: URL penuh ke host yang sedang dibuka tetap dipakai,
     * walau host itu bukan APP_URL.
     */
    /**
     * Jalur inilah yang memperbaiki bug: URL penuh diterima karena host-nya
     * sama dengan host permintaan, bukan karena cocok dengan APP_URL.
     *
     * Keadaan aslinya (APP_URL berbeda dari host yang dibuka) tidak bisa
     * ditirukan di sini — host permintaan pada test harness selalu diambil
     * dari APP_URL, dan withServerVariables() tidak menimpanya. Yang diuji
     * cabang kodenya, bukan pemisahan host-nya.
     */
    public function test_absolute_url_on_the_browsing_host_is_kept(): void
    {
        $asal = url('/id/fire?laporan=12');

        $this->assertSame($asal, $this->intendedSetelah($asal));
    }

    public function test_other_hosts_fall_back_to_home(): void
    {
        $this->assertSame('/', $this->intendedSetelah('https://situs-lain.example/curi'));
    }

    /** "//host" dibaca peramban sebagai URL berskema-sama, jadi ikut ditolak. */
    public function test_protocol_relative_url_falls_back_to_home(): void
    {
        $this->assertSame('/', $this->intendedSetelah('//situs-lain.example/curi'));
    }

    public function test_logout_returns_to_the_page_it_was_called_from(): void
    {
        $this->get('/comment/logout?intended='.urlencode('/id/fire'))
            ->assertRedirect('/id/fire');

        $this->get('/comment/logout?intended='.urlencode('https://situs-lain.example'))
            ->assertRedirect('/');
    }

    /**
     * Callback dengan `state` yang tidak cocok tidak boleh berakhir sebagai
     * galat 500. Penyebab lazimnya sepele — sesi kedaluwarsa, callback dimuat
     * ulang, atau alur dimulai di host yang berbeda dengan GOOGLE_REDIRECT_URI
     * (cookie sesi terikat host, jadi localhost dan 127.0.0.1 tidak berbagi).
     */
    public function test_mismatched_state_returns_to_the_page_instead_of_erroring(): void
    {
        $this->withSession(['url.intended' => '/id/fire'])
            ->get('/comment/login/google/callback?code=palsu&state=tidak-cocok')
            ->assertRedirect('/id/fire');

        $this->assertGuest();
    }
}
