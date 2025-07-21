<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\BrandController;
use App\Http\Controllers\Web\SearchController;
use Inertia\Inertia;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Marcas
Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
Route::get('/brands/{brand}', [BrandController::class, 'show'])->name('brands.show');

// Búsqueda
Route::get('/search', [SearchController::class, 'index'])->name('search');

// Rutas temporales
Route::get('/categories', function () {
    return Inertia::render('Categories/Index');
})->name('categories.index');

Route::get('/models', function () {
    return Inertia::render('Models/Index');
})->name('models.index');

Route::get('/parts', function () {
    return Inertia::render('Parts/Index');
})->name('parts.index');
