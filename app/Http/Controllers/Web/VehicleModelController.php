<?php

namespace App\Http\Controllers\Web;

use Illuminate\Validation\ValidationException;
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

    public function storeAjax(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:models,code',
                'brand_id' => 'required|exists:brands,id',
                'year_from' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
                'year_to' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
                'description' => 'nullable|string|max:1000',
            ]);

            // Validar que year_to sea mayor o igual a year_from
            if ($validated['year_from'] && $validated['year_to'] && $validated['year_to'] < $validated['year_from']) {
                return response()->json([
                    'success' => false,
                    'errors' => ['year_to' => ['El año hasta debe ser mayor o igual al año desde']],
                    'message' => 'Error de validación'
                ], 422);
            }

            $model = VehicleModel::create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'brand_id' => $validated['brand_id'],
                'year_from' => $validated['year_from'],
                'year_to' => $validated['year_to'],
                'description' => $validated['description'],
                'is_active' => true,
            ]);

            $model->load('brand');

            return response()->json([
                'success' => true,
                'model' => $model->load('brand'),
                'message' => 'Modelo creado exitosamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Error de validación'
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Error creating model via AJAX: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }
}
