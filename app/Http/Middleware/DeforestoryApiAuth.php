<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autentikasi token untuk seluruh endpoint API Deforestory.
 *
 * Token dibandingkan dengan DEFORESTORY_API_KEY (config services.deforestory_api.key).
 * Bisa dikirim via header `Authorization: Bearer xxx` atau query string `?token=xxx`.
 * Berlaku untuk endpoint sindikasi (GET) maupun integrasi internal (trigger/queue).
 */
class DeforestoryApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('services.deforestory_api.key');

        // Token boleh dikirim via header Authorization: Bearer xxx
        // ATAU via query string ?token=xxx — biar web lain tinggal tempel di URL.
        $provided = $request->bearerToken() ?: $request->query('token');

        if (empty($token) || $provided !== $token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
