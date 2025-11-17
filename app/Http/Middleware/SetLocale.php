<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->route('locale') ?? config('app.locale');
        $supported = ['id', 'en'];

        if (! in_array($locale, $supported)) {
            $segments = $request->segments();
            array_shift($segments);
            $url = implode('/', $segments);

            return redirect('/' . config('app.locale') . ($url ? '/' . $url : ''));
        }

        // Set Laravel locale
        App::setLocale($locale);
        Config::set('app.locale', $locale);

        // Set Carbon locale
        Carbon::setLocale($locale);

        // Set PHP locale untuk tanggal/bulan
        setlocale(LC_TIME, $locale == 'id' ? 'id_ID.UTF-8' : 'en_US.UTF-8');

        // Set default locale untuk URL generator
        URL::defaults(['locale' => $locale]);

        return $next($request);
    }
}
