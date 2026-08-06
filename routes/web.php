<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{DeforestoryController, DeforestoryMockApiController, EditorController, FellowshipController, PageController, PetitionController, SearchController};
use App\Http\Controllers\Admin\PetitionExportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Livewire\Auth\LoginForm;
use App\Livewire\Fellowship\{FellowshipForm, FellowshipTable};
use App\Livewire\Deforestory\{DeforestoryCaseTable, DeforestoryLaporanForm, DeforestoryLaporanTable, DeforestorySubscriberTable};
use App\Livewire\{KategoriForm, KategoriTable};
use App\Livewire\Pages\{PageForm, PageTable};
use App\Livewire\Petition\{PetitionForm, PetitionTable, PetitionSignatureList};
use App\Livewire\Users\{UserForm, UserTable};

Route::middleware(['setlocale'])->prefix('{locale}')->where(['locale' => 'id|en'])->group(function () {
    // home (no locale in URL)
    Route::get('/', [PageController::class, 'indexUser'])->name('home');

    // load more articles (infinite scroll)
    Route::get('/load-more-articles', [PageController::class, 'loadMoreArticles'])->name('articles.load-more');

    Route::get('/artikel/{expose_type}', [PageController::class, 'artikel'])->name('artikel.expose');

    Route::get('/fellowship', [FellowshipController::class, 'indexUser'])->name('fellowship-user');
    Route::get('/fellowship/{slug}', [FellowshipController::class, 'preview'])->name('fellowship.preview');

    Route::get('/artikel-fellowship', function () {
        return view('front.page-fellowship');
    })->name('artikel-fellowship');

    Route::get('/artikel-landing', function () {
        return view('front.page-expose');
    })->name('artikel-landing');

    

    Route::get('/ngopini/{slug}', [PageController::class, 'showNgopini'])->name('ngopini-show');

    Route::get('/ngopini-artikel', function () {
        return view('front.page-ngopini');
    })->name('ngopini');

    Route::get('/ngopini', [PageController::class, 'indexNgopini'])->name('ngopini.index');
    Route::get('/cbi', function () {
        return view('front.cbi');
    })->name('cbi');

    // Arsip kasus Deforestory (CMS-driven)
    Route::get('/deforestory', [DeforestoryController::class, 'index'])->name('deforestory');
    Route::get('/deforestory/{slug}', [DeforestoryController::class, 'show'])->name('deforestory.case');
    Route::get('/deforestory/{slug}/{laporanSlug}', [DeforestoryController::class, 'laporan'])->name('deforestory.case.laporan');
    Route::get('/deforestory-unsubscribe/{token}', [DeforestoryController::class, 'unsubscribe'])->name('deforestory.unsubscribe');

    // Petition
    Route::get('/petisi', [PetitionController::class, 'index'])->name('petition.index');
    Route::get('/petisi/{slug}', [PetitionController::class, 'show'])->name('petition.show');
    Route::post('/petisi/{slug}/sign', [PetitionController::class, 'sign'])
        ->middleware('throttle:5,1')
        ->name('petition.sign');
    Route::get('/petisi/verify/{token}', [PetitionController::class, 'verify'])->name('petition.verify');

    // Search route - harus sebelum catch-all route
    Route::get('/search', [SearchController::class, 'search'])->name('search');

    // public catch-all page preview (at end)
    Route::get('/{page_type}/{slug}', [PageController::class, 'preview'])->name('show-page');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

// MOCK API daftar kartu kasus Deforestory (lihat DeforestoryMockApiController).
// Hapus saat API eksternal sudah dipakai (ganti DEFORESTORY_API_URL).
Route::get('/api/deforestory-cases', [DeforestoryMockApiController::class, 'index']);

Route::middleware(['auth', 'role:admin,editor'])->group(function () {
    Route::get('/kategori', KategoriTable::class)->name('kategori.index');
    Route::get('/kategori/create', KategoriForm::class)->name('kategori.create');
    Route::get('/kategori/{kategoriId}/edit', KategoriForm::class)->name('kategori.edit');

    // fellowship (admin)
    Route::get('/admin/fellowship', FellowshipTable::class)->name('fellowship.index');
    Route::get('/admin/fellowship/create', FellowshipForm::class)->name('fellowship.create');
    Route::get('/admin/fellowship/edit/{fellowshipId}', FellowshipForm::class)->name('fellowship.edit');
    Route::get('/admin/fellowship/preview/{locale}/{slug}', [FellowshipController::class, 'preview'])->name('fellowship.preview.admin');

    // Pages
    Route::get('/pages', PageTable::class)->name('pages.index');
    Route::get('/pages/create', PageForm::class)->name('pages.create');
    Route::get('/pages/{pageId}/edit', PageForm::class)->name('pages.edit');
    Route::get('{locale}/{page_type}/{slug}/preview', [PageController::class, 'preview'])->name('page.preview');

    // User
    Route::get('/user/{userId}/edit', UserForm::class)->name('user.edit');

    // Petition admin
    Route::get('/admin/petisi', PetitionTable::class)->name('petition.admin.index');

    // Deforestory (admin CMS)
    Route::get('/admin/deforestory', DeforestoryCaseTable::class)->name('deforestory.index');
    Route::get('/admin/deforestory/{caseSlug}/laporan', DeforestoryLaporanTable::class)->name('deforestory.laporan.index');
    Route::get('/admin/deforestory/{caseSlug}/laporan/create', DeforestoryLaporanForm::class)->name('deforestory.laporan.create');
    Route::get('/admin/deforestory/laporan/{laporanId}/edit', DeforestoryLaporanForm::class)->name('deforestory.laporan.edit');
    Route::get('/admin/deforestory/subscribers', DeforestorySubscriberTable::class)->name('deforestory.subscribers');
    Route::get('/admin/petisi/create', PetitionForm::class)->name('petition.admin.create');
    Route::get('/admin/petisi/{petitionId}/edit', PetitionForm::class)->name('petition.admin.edit');
    Route::get('/admin/petisi/{petitionId}/signatures', PetitionSignatureList::class)->name('petition.admin.signatures');
    Route::get('/admin/petisi/{petitionId}/export-pdf', [PetitionExportController::class, 'exportPdf'])->name('petition.admin.export-pdf');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/user', UserTable::class)->name('user.index');
    Route::get('/user/create', UserForm::class)->name('user.create');
});

Route::get('/login', LoginForm::class)->name('login');

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect('/login');
})->name('logout');

Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth']], function () {
    \UniSharp\LaravelFilemanager\Lfm::routes();
});

// Route::post('/upload-editor-image', [EditorController::class, 'uploadEditorImage']);

Route::fallback(function () {
    return redirect('/');
});
