<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Key Turnstile yang dipakai aplikasi.
 *
 * Dulu penggantinya mengikuti APP_ENV=local, jadi server yang APP_ENV-nya
 * tertinggal di `local` diam-diam memakai "test key" Cloudflare yang selalu
 * lolos: spanduk "Hanya untuk pengujian" muncul di situs hidup dan
 * perlindungan botnya sebenarnya mati. Sekarang key asli adalah bawaannya, dan
 * test key harus diminta sengaja.
 */
class TurnstileKeySourceTest extends TestCase
{
    private const TEST_SITE_KEY = '1x00000000000000000000AA';

    public function test_real_key_is_used_by_default(): void
    {
        config(['services.turnstile.site_key' => '0xKEY-ASLI', 'services.turnstile.test_keys' => false]);

        $this->app->register(\App\Providers\AppServiceProvider::class, true);

        $this->assertSame('0xKEY-ASLI', config('services.turnstile.site_key'));
    }

    /** Termasuk di local: APP_ENV tidak lagi ikut menentukan. */
    public function test_local_environment_alone_does_not_swap_in_test_keys(): void
    {
        $this->app['env'] = 'local';
        config(['services.turnstile.site_key' => '0xKEY-ASLI', 'services.turnstile.test_keys' => false]);

        $this->app->register(\App\Providers\AppServiceProvider::class, true);

        $this->assertNotSame(self::TEST_SITE_KEY, config('services.turnstile.site_key'));
    }

    public function test_test_keys_are_used_when_asked_for_explicitly(): void
    {
        config(['services.turnstile.site_key' => '0xKEY-ASLI', 'services.turnstile.test_keys' => true]);

        $this->app->register(\App\Providers\AppServiceProvider::class, true);

        $this->assertSame(self::TEST_SITE_KEY, config('services.turnstile.site_key'));
    }
}
