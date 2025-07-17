<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

//Route::get('dashboard', function () {
//    return Inertia::render('Dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

//require __DIR__.'/settings.php';
//require __DIR__.'/auth.php';

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::get('/brands', function () {
    return Inertia::render('Brands/Index');
})->name('brands.index');

Route::get('/brands/{brand}', function ($brand) {
    return Inertia::render('Brands/Show', ['brandId' => $brand]);
})->name('brands.show');

Route::get('/models', function () {
    return Inertia::render('Models/Index');
})->name('models.index');

Route::get('/models/{model}', function ($model) {
    return Inertia::render('Models/Show', ['modelId' => $model]);
})->name('models.show');

Route::get('/categories', function () {
    return Inertia::render('Categories/Index');
})->name('categories.index');

Route::get('/categories/{category}', function ($category) {
    return Inertia::render('Categories/Show', ['categoryId' => $category]);
})->name('categories.show');

Route::get('/parts', function () {
    return Inertia::render('Parts/Index');
})->name('parts.index');

Route::get('/parts/{part}', function ($part) {
    return Inertia::render('Parts/Show', ['partId' => $part]);
})->name('parts.show');

Route::get('/search', function () {
    return Inertia::render('Search/Index');
})->name('search');
