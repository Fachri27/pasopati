<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Mengambil daftar kartu kasus Deforestory dari API eksternal.
 *
 * Hanya halaman index (/deforestory) yang memakai API ini. Konten arsip
 * dan laporan (/{slug} & /{slug}/laporan) tetap di CMS lokal, di-match via slug.
 *
 * - URL relatif (default "/api/deforestory-cases") → mock lokal, dipanggil
 *   via sub-request internal (tanpa network) supaya tidak butuh server jalan.
 * - URL absolut (http://...) → panggilan HTTP biasa ke web lain.
 *   Ganti DEFORESTORY_API_URL untuk pakai API asli.
 */
class DeforestoryApiService
{
    public function getCases(string $locale = 'id'): array
    {
        $cacheKey = 'deforestory_api_cases:' . $locale;
        $minutes = (int) config('services.deforestory_api.cache_minutes', 10);

        return Cache::remember($cacheKey, now()->addMinutes($minutes), function () use ($locale) {
            return $this->fetch($locale);
        });
    }

    public function refresh(string $locale = 'id'): array
    {
        Cache::forget('deforestory_api_cases:' . $locale);
        return $this->getCases($locale);
    }

    protected function fetch(string $locale): array
    {
        $url = (string) config('services.deforestory_api.url', '/api/deforestory-cases');

        try {
            $payload = preg_match('#^https?://#', $url)
                ? $this->fetchHttp($url, $locale)
                : $this->fetchInternal($url, $locale);

            return $this->normalize($payload);
        } catch (ConnectionException $e) {
            Log::warning('Deforestory API unreachable: ' . $e->getMessage());
            return [];
        } catch (\Throwable $e) {
            Log::warning('Deforestory API error: ' . $e->getMessage());
            return [];
        }
    }

    /** Panggilan HTTP ke API eksternal (web lain). */
    protected function fetchHttp(string $url, string $locale): ?array
    {
        $timeout = (int) config('services.deforestory_api.timeout', 8);

        $response = Http::timeout($timeout)->get($url, ['locale' => $locale]);

        if (! $response->successful()) {
            Log::warning('Deforestory API non-2xx', ['status' => $response->status(), 'url' => $url]);
            return null;
        }

        return $response->json();
    }

    /** Sub-request internal ke mock lokal (tanpa network). */
    protected function fetchInternal(string $path, string $locale): ?array
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $request = Request::create('/' . ltrim($path, '/') . '?locale=' . $locale, 'GET');

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        if (! $response->isSuccessful()) {
            Log::warning('Deforestory mock non-2xx', ['status' => $response->status(), 'path' => $path]);
            return null;
        }

        return json_decode($response->getContent(), true);
    }

    /** Normalisasi berbagai bentuk respons → list card. */
    protected function normalize(?array $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $list = $payload['data'] ?? $payload['cases'] ?? $payload;

        return is_array($list) ? array_values($list) : [];
    }
}