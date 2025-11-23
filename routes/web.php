<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HandbookController;
use App\Http\Controllers\PhotoController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/editor', function () {
    return Inertia::render('EditorDemo');
})->middleware(['auth', 'verified'])->name('editor');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // 後台管理路由
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/handbooks', [HandbookController::class, 'index'])->name('handbooks');
        Route::get('/handbooks/create', [HandbookController::class, 'create'])->name('handbooks.create');
        Route::post('/handbooks', [HandbookController::class, 'store'])->name('handbooks.store');
        Route::get('/handbooks/{id}', [HandbookController::class, 'show'])->name('handbooks.show');
        Route::get('/handbooks/{id}/edit', [HandbookController::class, 'edit'])->name('handbooks.edit');
        Route::put('/handbooks/{id}', [HandbookController::class, 'update'])->name('handbooks.update');
        Route::delete('/handbooks/{id}', [HandbookController::class, 'destroy'])->name('handbooks.destroy');
        Route::get('/photos', [PhotoController::class, 'index'])->name('photos');
        Route::post('/photos/upload', [PhotoController::class, 'upload'])->name('photos.upload');
    });
});

require __DIR__.'/auth.php';
