<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BrandService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct(
        private BrandService $brandService
    ) {}

    public function index()
    {
        $brands = $this->brandService->getAllBrands();
        return response()->json($brands);
    }

    public function show($id)
    {
        $brand = $this->brandService->getBrandById($id);
        return response()->json($brand);
    }

    public function models($brandId)
    {
        $models = $this->brandService->getBrandModels($brandId);
        return response()->json($models);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $brand = $this->brandService->createBrand($validated);
        return response()->json($brand, 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $brand = $this->brandService->updateBrand($id, $validated);
        return response()->json($brand);
    }

    public function destroy($id)
    {
        $this->brandService->deleteBrand($id);
        return response()->json(null, 204);
    }
}
