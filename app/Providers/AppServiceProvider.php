<?php

namespace App\Providers;

use App\Models\Fellowship;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        view()->composer('layouts.app', function ($view) {
            $yearPosts = Fellowship::with('translations')
                ->selectRaw('YEAR(start_date) as year, id, slug')
                ->orderBy('year', 'desc')
                ->get()
                ->groupBy('year');

            $view->with('yearPosts', $yearPosts);
        });
    }
}
