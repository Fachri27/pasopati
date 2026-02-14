<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{App, Config, URL};
use Closure;
use Carbon\Carbon;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Determine locale from session/cookie or fallback to app default
        $locale = session('locale') ?? $request->cookie('locale') ?? config('app.locale');
        $supported = ['id', 'en'];

        if (! in_array($locale, $supported)) {
            $locale = config('app.locale');
        }

        // Set Laravel locale
        App::setLocale($locale);
        Config::set('app.locale', $locale);

        // Set Carbon locale
        Carbon::setLocale($locale);

        // Set PHP locale untuk tanggal/bulan
        setlocale(LC_TIME, $locale == 'id' ? 'id_ID.UTF-8' : 'en_US.UTF-8');

        // do not force locale into the URL - we manage locale via session/cookie

        return $next($request);
    }
}
