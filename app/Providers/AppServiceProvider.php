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
        // Cloudflare Turnstile: key real (0x...) hanya mau render di hostname
        // yang di-whitelist di dashboard Cloudflare — library.test/localhost
        // gak terima, jadi widget error 110200/400 di dev. Cloudflare menyediakan
        // "test key" yang selalu lolos di domain manapun (termasuk localhost &
        // IP) persis untuk dev lokal. Override dilakukan di sini (bukan di
        // config/services.php) karena env binding baru tersedia setelah
        // bootstrap. Key real di .env tetap dipakai di production.
        if ($this->app->environment('local')) {
            config([
                'services.turnstile.site_key' => '1x00000000000000000000AA',
                'services.turnstile.secret_key' => '1x0000000000000000000000000000000AA',
            ]);
        }

        view()->composer(['layouts.app', 'layouts.deforestory'], function ($view) {
            $yearPosts = Fellowship::with('translations')
                ->where('status', 'active')
                ->orderBy('start_date', 'desc')
                ->take(5)
                ->get()
                ->groupBy(fn($item) => $item->start_date?->year);

            $view->with('yearPosts', $yearPosts);
        });
    }
}
