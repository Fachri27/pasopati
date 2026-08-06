<?php

use App\Http\Controllers\Api\DeforestoryApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Endpoint untuk integrasi antar-web. Semua route di sini memakai
| middleware `api` (throttle, tanpa session/csrf) + `deforestory.api`
| (token = DEFORESTORY_API_KEY). Token bisa dikirim via header
| `Authorization: Bearer xxx` atau query string `?token=xxx`.
| Tanpa token / token salah → 401.
|
| Sindikasi (read-only):
|   GET  /api/deforestory/cases                            daftar kasus aktif
|   GET  /api/deforestory/cases/{slug}                     satu kasus + daftar laporan
|   GET  /api/deforestory/cases/{slug}/laporan             daftar laporan sebuah kasus
|   GET  /api/deforestory/cases/{slug}/laporan/latest      laporan terkini (metadata)
|   GET  /api/deforestory/cases/{slug}/laporan/{laporan}   satu laporan (metadata)
|
| Integrasi internal:
|   GET  /api/deforestory/queue-length                      jumlah job pending
|
| Semua endpoint terima ?locale=id|en (default id).
|
*/

Route::prefix('deforestory')
    ->controller(DeforestoryApiController::class)
    ->middleware('deforestory.api')
    ->group(function () {
        // Sindikasi (read-only) — web lain GET halaman & laporan.
        Route::get('/cases', 'casesIndex');
        Route::get('/cases/{slug}', 'caseShow');
        Route::get('/cases/{slug}/laporan', 'laporanIndex');
        Route::get('/cases/{slug}/laporan/latest', 'laporanLatest');
        Route::get('/cases/{slug}/laporan/{laporanSlug}', 'laporanShow');

        // Monitoring (perlu Bearer token).
        Route::get('/queue-length', 'queueLength');
    });
