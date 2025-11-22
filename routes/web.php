<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\BuilderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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

    // Builder routes
    Route::get('/builder/{page}/edit', [BuilderController::class, 'edit'])->name('builder.edit');
    Route::post('/builder/{page}/save', [BuilderController::class, 'save'])->name('builder.save');
    Route::post('/builder/{page}/autosave', [BuilderController::class, 'autosave'])->name('builder.autosave');
    Route::post('/builder/{page}/publish', [BuilderController::class, 'publish'])->name('builder.publish');
    Route::get('/builder/{page}/preview', [BuilderController::class, 'preview'])->name('builder.preview');
});

require __DIR__.'/auth.php';
