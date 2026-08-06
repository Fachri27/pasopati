<?php

use App\Http\Controllers\Api\DeforestoryApiController;
use App\Http\Controllers\Api\DeforestoryCardWebhookController;
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
|   GET  /api/deforestory/cases/{slug}/laporan/{laporan}/translations  satu laporan + translations id & en
|
| Integrasi internal:
|   GET  /api/deforestory/queue-length                      jumlah job pending
|
| Semua endpoint terima ?locale=id|en (default id).
|
| Inbound webhook (auth sementara DIMATIKAN — publik untuk testing):
|   POST /api/deforestory/cards                              push daftar kartu kasus dari web lain
|   PUT|PATCH /api/deforestory/cards/{uuid}                  perbarui satu kartu by uuid (slug boleh ikut berubah)
|
*/

// Inbound: web lain POST daftar card ke CMS.
// NOTE: auth sementara DIMATIKAN (testing) — endpoint jadi publik. Nyalakan
// lagi `->middleware('deforestory.api')` (Bearer token = DEFORESTORY_API_KEY)
// sebelum dipakai beneran di produksi.
Route::post('/deforestory/cards', [DeforestoryCardWebhookController::class, 'handle']);

// Update satu card by UUID (bukan slug — slug bisa berubah; bukan id — id
// auto-increment gak portable antar env). Tidak membuat baru, tidak kirim
// notifikasi. Sejajar dengan POST /cards di atas: auth dimatikan untuk testing.
Route::match(['put', 'patch'], '/deforestory/cards/{uuid}', [DeforestoryCardWebhookController::class, 'update']);

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
        Route::get('/cases/{slug}/laporan/{laporanSlug}/translations', 'laporanTranslations');

        // Monitoring (perlu Bearer token).
        Route::get('/queue-length', 'queueLength');
    });
