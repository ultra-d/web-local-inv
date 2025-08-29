<?php

namespace App\Http\Controllers\Web;

use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Services\BrandService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BrandController extends Controller
{
    public function __construct(
        private BrandService $brandService
    ) {}

    public function index()
    {
        $brands = $this->brandService->getAllBrands();
        return Inertia::render('Brands/Index', [
            'brands' => $brands
        ]);
    }

    public function show($id)
    {
        $brand = $this->brandService->getBrandById($id);
        return Inertia::render('Brands/Show', [
            'brand' => $brand
        ]);
    }

    public function storeAjax(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:brands,name',
                'code' => 'nullable|string|max:50|unique:brands,code',
                'country' => 'nullable|string|max:100',
                'description' => 'nullable|string|max:1000',
            ]);

            // Si no se proporciona código, generarlo desde el nombre
            if (empty($validated['code'])) {
                $validated['code'] = strtoupper(substr($validated['name'], 0, 3)) . rand(100, 999);
            }

            $brand = \App\Models\Brand::create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'country' => $validated['country'] ?? null,
                'description' => $validated['description'] ?? null,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'brand' => $brand,
                'message' => 'Marca creada exitosamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Error de validación'
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Error creating brand via AJAX: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }
}
