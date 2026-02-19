<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{EditorController, FellowshipController, PageController};
use App\Livewire\Auth\LoginForm;
use App\Livewire\Fellowship\{FellowshipForm, FellowshipTable};
use App\Livewire\{KategoriForm, KategoriTable};
use App\Livewire\Pages\{PageForm, PageTable};
use App\Livewire\Users\{UserForm, UserTable};

Route::middleware(['setlocale'])->prefix('{locale}')->where(['locale' => 'id|en'])->group(function () {
    // home (no locale in URL)
    Route::get('/', [PageController::class, 'indexUser'])->name('home');

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

    // public catch-all page preview (at end)
    Route::get('/{page_type}/{slug}', [PageController::class, 'preview'])->name('show-page');
});

Route::get('/dashboard', function () {
    return view('pages.admin.dashboard-admin');
})->middleware('auth')->name('dashboard');

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

Route::post('/upload-editor-image', [EditorController::class, 'uploadEditorImage']);

Route::fallback(function () {
    return redirect('/');
});
