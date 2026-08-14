<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * redirect_uri yang dikirim ke Google.
 *
 * Nilai yang dipatok ke satu host sementara halaman dibuka dari host lain
 * adalah penyebab lazim InvalidStateException: cookie sesi terikat host, jadi
 * `state` tidak pernah ikut terkirim ke callback. Karena itu GOOGLE_REDIRECT_URI
 * boleh dikosongkan dan diturunkan dari host permintaan.
 */
class GoogleCallbackUriTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'uji-client-id',
            'services.google.client_secret' => 'uji-secret',
        ]);
    }

    private function redirectUriYangDikirim(): string
    {
        $tujuan = $this->get('/comment/login/google')->headers->get('Location');
        parse_str((string) parse_url($tujuan, PHP_URL_QUERY), $q);

        return $q['redirect_uri'] ?? '';
    }

    public function test_configured_uri_is_used_as_is(): void
    {
        config(['services.google.redirect' => 'https://pasopati.id/comment/login/google/callback']);

        $this->assertSame(
            'https://pasopati.id/comment/login/google/callback',
            $this->redirectUriYangDikirim()
        );
    }

    public function test_uri_falls_back_to_the_browsing_host(): void
    {
        config(['services.google.redirect' => '']);

        $this->assertSame(route('comment.google.callback'), $this->redirectUriYangDikirim());
    }
}
