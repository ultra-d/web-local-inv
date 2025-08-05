<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\BrandController;
use App\Http\Controllers\Web\SearchController;
use App\Http\Controllers\Web\PartCategoryController;
use App\Http\Controllers\Web\VehicleModelController;
use App\Http\Controllers\Web\PartController;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Marcas
Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
Route::get('/brands/{brand}', [BrandController::class, 'show'])->name('brands.show');
Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');

// Búsqueda
Route::get('/search', [SearchController::class, 'index'])->name('search');

// Categorías
Route::get('/categories', [PartCategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/create', [PartCategoryController::class, 'create'])->name('categories.create');
Route::post('/categories', [PartCategoryController::class, 'store'])->name('categories.store');
Route::get('/categories/{category}', [PartCategoryController::class, 'show'])->name('categories.show');
Route::get('/categories/{category}/edit', [PartCategoryController::class, 'edit'])->name('categories.edit');
Route::put('/categories/{category}', [PartCategoryController::class, 'update'])->name('categories.update');
Route::delete('/categories/{category}', [PartCategoryController::class, 'destroy'])->name('categories.destroy');

// Modelos
Route::get('/models', [VehicleModelController::class, 'index'])->name('models.index');
Route::get('/models/create', [VehicleModelController::class, 'create'])->name('models.create');
Route::post('/models', [VehicleModelController::class, 'store'])->name('models.store');
Route::get('/models/{model}', [VehicleModelController::class, 'show'])->name('models.show');
Route::get('/models/{model}/edit', [VehicleModelController::class, 'edit'])->name('models.edit');
Route::put('/models/{model}', [VehicleModelController::class, 'update'])->name('models.update');
Route::delete('/models/{model}', [VehicleModelController::class, 'destroy'])->name('models.destroy');

// Repuestos
Route::get('/parts', [PartController::class, 'index'])->name('parts.index');
Route::get('/parts/create', [PartController::class, 'create'])->name('parts.create');
Route::post('/parts', [PartController::class, 'store'])->name('parts.store');
Route::get('/parts/{part}', [PartController::class, 'show'])->name('parts.show');
Route::get('/parts/{part}/edit', [PartController::class, 'edit'])->name('parts.edit');
Route::put('/parts/{part}', [PartController::class, 'update'])->name('parts.update');
Route::delete('/parts/{part}', [PartController::class, 'destroy'])->name('parts.destroy');

// ============================
// RUTAS AJAX para los modales
// ============================
Route::post('/categories/store-ajax', [PartCategoryController::class, 'storeAjax'])->name('categories.store-ajax');
Route::post('/models/store-ajax', [VehicleModelController::class, 'storeAjax'])->name('models.store-ajax');
Route::post('/brands/store-ajax', [BrandController::class, 'storeAjax'])->name('brands.store-ajax');

// ====================================================
// MANTENER las rutas existentes para compatibilidad
// ====================================================
//Route::post('/parts/create-category', [PartController::class, 'createCategory'])->name('parts.create-category');
//Route::post('/parts/create-brand', [PartController::class, 'createBrand'])->name('parts.create-brand');
//Route::post('/parts/create-model', [PartController::class, 'createModel'])->name('parts.create-model');

// Agregar al final de routes/web.php
Route::get('/debug/storage', function() {
    $publicStoragePath = public_path('storage');
    $storagePublicPath = storage_path('app/public');
    $partsPath = storage_path('app/public/parts');

    // Buscar la imagen específica de los logs
    $testImagePath = 'parts/1754341443_6891204364adb.png';
    $fullImagePath = storage_path('app/public/' . $testImagePath);

    // Obtener todos los repuestos con imagen
    $partsWithImages = \App\Models\Part::whereNotNull('image_path')->get();

    $debug = [
        'storage_config' => [
            'public_storage_path' => $publicStoragePath,
            'public_storage_exists' => file_exists($publicStoragePath),
            'public_storage_is_link' => is_link($publicStoragePath),
            'public_storage_target' => is_link($publicStoragePath) ? readlink($publicStoragePath) : null,
            'storage_public_path' => $storagePublicPath,
            'storage_public_exists' => file_exists($storagePublicPath),
            'parts_directory_exists' => file_exists($partsPath),
            'parts_directory_writable' => is_writable($partsPath),
        ],
        'test_image' => [
            'path' => $testImagePath,
            'full_path' => $fullImagePath,
            'exists' => file_exists($fullImagePath),
            'size' => file_exists($fullImagePath) ? filesize($fullImagePath) : null,
            'permissions' => file_exists($fullImagePath) ? substr(sprintf('%o', fileperms($fullImagePath)), -4) : null,
            'expected_url' => asset('storage/' . $testImagePath),
        ],
        'parts_with_images' => $partsWithImages->map(function($part) {
            $fullPath = storage_path('app/public/' . $part->image_path);
            return [
                'id' => $part->id,
                'name' => $part->name,
                'image_path' => $part->image_path,
                'full_path' => $fullPath,
                'exists' => file_exists($fullPath),
                'url' => asset('storage/' . $part->image_path),
                'image_url_accessor' => $part->image_url,
            ];
        }),
        'directory_contents' => [
            'parts_dir' => file_exists($partsPath) ? scandir($partsPath) : ['Directory not found']
        ]
    ];

    return response()->json($debug, 200, [], JSON_PRETTY_PRINT);
});

// Ruta para mostrar imagen directamente (para testing)
Route::get('/debug/image/{filename}', function($filename) {
    $path = storage_path('app/public/parts/' . $filename);

    if (!file_exists($path)) {
        abort(404, 'Image not found: ' . $path);
    }

    $file = file_get_contents($path);
    $type = mime_content_type($path);

    return response($file, 200)->header('Content-Type', $type);
});
