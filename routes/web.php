<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\BuilderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\Api\MediaController as ApiMediaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Template routes
    Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
    Route::get('/templates/filter', [TemplateController::class, 'filter'])->name('templates.filter');
    Route::get('/templates/user', [TemplateController::class, 'userTemplates'])->name('templates.user');
    Route::get('/templates/{template}', [TemplateController::class, 'show'])->name('templates.show');
    Route::get('/templates/{template}/preview', [TemplateController::class, 'preview'])->name('templates.preview');
    Route::post('/templates/{template}/apply', [TemplateController::class, 'apply'])->name('templates.apply');
    Route::post('/templates', [TemplateController::class, 'store'])->name('templates.store');
    Route::delete('/templates/{template}', [TemplateController::class, 'destroy'])->name('templates.destroy');

    // Page routes
    Route::get('/pages', [PageController::class, 'index'])->name('pages.index');
    Route::post('/pages', [PageController::class, 'store'])->name('pages.store');
    Route::get('/pages/{page}', [PageController::class, 'show'])->name('pages.show');
    Route::put('/pages/{page}', [PageController::class, 'update'])->name('pages.update');
    Route::delete('/pages/{page}', [PageController::class, 'destroy'])->name('pages.destroy');
    Route::post('/pages/{page}/duplicate', [PageController::class, 'duplicate'])->name('pages.duplicate');
    Route::get('/pages/{page}/versions', [PageController::class, 'versions'])->name('pages.versions');
    Route::post('/pages/{page}/versions/{version}/restore', [PageController::class, 'restoreVersion'])->name('pages.versions.restore');

    // Builder routes
    Route::get('/builder/{page}/edit', [BuilderController::class, 'edit'])->name('builder.edit');
    Route::post('/builder/{page}/save', [BuilderController::class, 'save'])->name('builder.save');
    Route::post('/builder/{page}/autosave', [BuilderController::class, 'autosave'])->name('builder.autosave');
    Route::post('/builder/{page}/publish', [BuilderController::class, 'publish'])->name('builder.publish');
    Route::get('/builder/{page}/preview', [BuilderController::class, 'preview'])->name('builder.preview');

    // Media routes
    Route::get('/media', [MediaController::class, 'index'])->name('media.index');

    // Media API routes
    Route::get('/api/media', [ApiMediaController::class, 'index']);
    Route::post('/api/media/upload', [ApiMediaController::class, 'store']);
    Route::get('/api/media/{media}', [ApiMediaController::class, 'show']);
    Route::patch('/api/media/{media}', [ApiMediaController::class, 'update']);
    Route::delete('/api/media/{media}', [ApiMediaController::class, 'destroy']);
    Route::post('/api/media/bulk-delete', [ApiMediaController::class, 'bulkDestroy']);
});

// Public page route
Route::get('/p/{slug}', function ($slug) {
    $page = \App\Models\Page::where('slug', $slug)
        ->where('status', 'published')
        ->firstOrFail();
    return view('pages.show', compact('page'));
})->name('page.show');

require __DIR__.'/auth.php';
