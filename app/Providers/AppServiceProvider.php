<?php

namespace App\Providers;

use App\Models\Fellowship;
use App\Services\ProfanityFilter;
use App\Services\SeoService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('seo', function () {
            return new SeoService;
        });

        $this->app->singleton(ProfanityFilter::class, function () {
            return new ProfanityFilter;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cloudflare Turnstile: key asli (0x...) hanya mau render di hostname
        // yang di-whitelist di dashboard Cloudflare, jadi di mesin pengembangan
        // widget-nya bisa error 110200/400. Cloudflare menyediakan "test key"
        // yang selalu lolos di hostname mana pun untuk keperluan itu.
        //
        // Penggantinya kini harus diminta sengaja (TURNSTILE_TEST_KEYS=true),
        // bukan mengikuti APP_ENV: dengan aturan lama, server yang APP_ENV-nya
        // tertinggal di `local` diam-diam memakai captcha yang selalu lolos —
        // spanduk "Hanya untuk pengujian" muncul di situs hidup dan
        // perlindungan botnya sebenarnya mati. Bawaannya sekarang key asli.
        if (config('services.turnstile.test_keys')) {
            config([
                // Varian "invisible" dari test key Cloudflare (…BB, bukan …AA):
                // sama-sama selalu lolos, tetapi tidak menampilkan kotak widget
                // beserta spanduk "Hanya untuk pengujian".
                'services.turnstile.site_key' => '1x00000000000000000000BB',
                'services.turnstile.secret_key' => '1x0000000000000000000000000000000AA',
            ]);
        }

        // Ditempelkan pada partial navigasinya, bukan hanya pada layout:
        // halaman /fire memakai layout sendiri (pasopati.layout) dengan salinan
        // navbar yang juga menampilkan menu Fellowship, sehingga tanpa ini
        // $yearPosts tidak terisi dan navbarnya menggagalkan seluruh halaman.
        view()->composer([
            'layouts.app',
            'layouts.deforestory',
            'pasopati.nav',
        ], function ($view) {
            $yearPosts = Fellowship::with('translations')
                ->where('status', 'active')
                ->orderBy('start_date', 'desc')
                ->take(5)
                ->get()
                ->groupBy(fn ($item) => $item->start_date?->year);

            $view->with('yearPosts', $yearPosts);
        });
    }
}
