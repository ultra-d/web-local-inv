<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\VehicleModelController;
use App\Http\Controllers\Api\PartCategoryController;
use App\Http\Controllers\Api\PartController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('api')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Búsqueda global
    Route::get('/search', [PartController::class, 'search']);

    // Marcas
    Route::prefix('brands')->group(function () {
        Route::get('/', [BrandController::class, 'index']);
        Route::get('/{brand}', [BrandController::class, 'show']);
        Route::get('/{brand}/models', [BrandController::class, 'models']);
    });

    // Modelos de vehículos
    Route::prefix('models')->group(function () {
        Route::get('/', [VehicleModelController::class, 'index']);
        Route::get('/{model}', [VehicleModelController::class, 'show']);
        Route::get('/{model}/parts', [VehicleModelController::class, 'parts']);
    });

    // Categorías
    Route::prefix('categories')->group(function () {
        Route::get('/', [PartCategoryController::class, 'index']);
        Route::get('/{category}', [PartCategoryController::class, 'show']);
        Route::get('/{category}/parts', [PartCategoryController::class, 'parts']);
    });

    // Repuestos
    Route::prefix('parts')->group(function () {
        Route::get('/', [PartController::class, 'index']);
        Route::get('/{part}', [PartController::class, 'show']);
    });

    Route::post('/validate-part-code', [PartController::class, 'validatePartCode']);
});
