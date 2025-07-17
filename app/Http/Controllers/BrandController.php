<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::active()
            ->withCount(['models' => function ($query) {
                $query->active();
            }])
            ->orderBy('name')
            ->get();

        return response()->json($brands);
    }

    public function show(Brand $brand)
    {
        $brand->load(['activeModels.parts' => function ($query) {
            $query->available();
        }]);

        return response()->json($brand);
    }

    public function models(Brand $brand)
    {
        $models = $brand->activeModels()
            ->withCount(['parts' => function ($query) {
                $query->available();
            }])
            ->orderBy('name')
            ->get();

        return response()->json($models);
    }
}
