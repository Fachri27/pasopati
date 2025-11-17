<?php

use App\Http\Controllers\EditorController;
use App\Http\Controllers\FellowshipController;
use App\Http\Controllers\PageController;
use App\Livewire\Auth\LoginForm;
use App\Livewire\Fellowship\FellowshipForm;
use App\Livewire\Fellowship\FellowshipTable;
use App\Livewire\KategoriForm;
use App\Livewire\KategoriTable;
use App\Livewire\Pages\PageForm;
use App\Livewire\Pages\PageTable;
use App\Livewire\Users\UserForm;
use App\Livewire\Users\UserTable;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/id'); // default ke Indonesia
});

Route::group([
    'prefix' => '{locale}',
    'middleware' => ['setlocale'],
    'where' => ['locale' => 'en|id'],
], function () {
    Route::get('/', [PageController::class, 'indexUser'])
        ->name('home.locale');

    Route::get('/artikel/{expose_type}', [PageController::class, 'artikel'])->name('artikel.expose');

    Route::get('/fellowship', [FellowshipController::class, 'indexUser'])->name('fellowship.user');
    Route::get('/fellowship/{slug}', [FellowshipController::class, 'preview'])->name('fellowship.preview');

    Route::get('/artikel-fellowship', function () {
        return view('front.page-fellowship');
    })->name('artikel-fellowship');

    Route::get('/artikel-landing', function () {
        return view('front.page-expose');
    })->name('artikel-landing');

    Route::get('/content', [PageController::class, 'show'])
        ->where('type', 'expose|fellowship|ngopini|static')
        ->name('artikel.landing');

    Route::get('/{page_type}/{slug}', [PageController::class, 'preview'])->name('show-page');

    Route::get('/ngopini/{slug}', [PageController::class, 'showNgopini'])->name('ngopini-show');

    Route::get('/ngopini-artikel', function () {
        return view('front.page-ngopini');
    })->name('ngopini');

    Route::get('/ngopini', [PageController::class, 'indexNgopini'])->name('ngopini.index');
});

Route::get('/dashboard', function () {
    return view('pages.admin.dashboard-admin');
})->middleware('auth')->name('dashboard');

Route::middleware(['auth', 'role:admin,editor'])->group(function () {
    Route::get('/kategori', KategoriTable::class)->name('kategori.index');
    Route::get('/kategori/create', KategoriForm::class)->name('kategori.create');
    Route::get('/kategori/{kategoriId}/edit', KategoriForm::class)->name('kategori.edit');

    // fellowship
    Route::get('/fellowship', FellowshipTable::class)->name('fellowship.index');
    Route::get('/fellowship/create', FellowshipForm::class)->name('fellowship.create');
    Route::get('/fellowship/{fellowshipId}/edit', FellowshipForm::class)->name('fellowship.edit');
    Route::get('/{locale}/{slug}/preview-fellowship', [FellowshipController::class, 'preview'])->name('fellowship.preview');

    // Pages
    Route::get('/pages', PageTable::class)->name('pages.index');
    Route::get('/pages/create', PageForm::class)->name('pages.create');
    Route::get('/pages/{pageId}/edit', PageForm::class)->name('pages.edit');
    Route::get('/{locale}/{page_type}/{slug}/preview', [PageController::class, 'preview'])->name('page.preview');

    // User
    Route::get('/user/{userId}/edit', UserForm::class)->name('user.edit');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/user', UserTable::class)->name('user.index');
    Route::get('/user/create', UserForm::class)->name('user.create');
});

Route::get('/login', LoginForm::class)->name('login')->middleware('guest');

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
