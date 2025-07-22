<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\VehicleModel;
use App\Models\Brand;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VehicleModelController extends Controller
{
    public function index()
    {
        try {
            $models = VehicleModel::with('brand')
                ->active()
                ->withCount(['parts' => function ($query) {
                    $query->available();
                }])
                ->orderBy('name')
                ->get();

            $brands = Brand::active()
                ->withCount('models')
                ->orderBy('name')
                ->get();

            return Inertia::render('Models/Index', [
                'models' => $models,
                'brands' => $brands,
                'stats' => [
                    'total_models' => VehicleModel::count(),
                    'active_models' => VehicleModel::active()->count(),
                    'models_with_parts' => VehicleModel::has('parts')->count(),
                    'total_brands' => Brand::count(),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Models index error: ' . $e->getMessage());

            return Inertia::render('Models/Index', [
                'models' => [],
                'brands' => [],
                'stats' => [
                    'total_models' => 0,
                    'active_models' => 0,
                    'models_with_parts' => 0,
                    'total_brands' => 0,
                ],
                'error' => 'Error al cargar los modelos'
            ]);
        }
    }

    public function show($id)
    {
        try {
            $model = VehicleModel::with(['brand', 'parts.category'])
                ->findOrFail($id);

            return Inertia::render('Models/Show', [
                'model' => $model
            ]);
        } catch (\Exception $e) {
            \Log::error('Model show error: ' . $e->getMessage());
            return redirect()->route('models.index')
                ->with('error', 'Modelo no encontrado');
        }
    }
}
