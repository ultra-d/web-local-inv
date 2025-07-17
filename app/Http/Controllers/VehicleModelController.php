<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VehicleModel;
use Illuminate\Http\Request;

class VehicleModelController extends Controller
{
    public function index(Request $request)
    {
        $query = VehicleModel::with('brand')->active();

        if ($request->has('brand_id')) {
            $query->byBrand($request->brand_id);
        }

        if ($request->has('popular') && $request->popular) {
            $query->popular();
        }

        $models = $query->withCount(['parts' => function ($q) {
            $q->available();
        }])
        ->orderBy('name')
        ->get();

        return response()->json($models);
    }

    public function show(VehicleModel $model)
    {
        $model->load(['brand', 'availableParts.category']);

        return response()->json($model);
    }

    public function parts(VehicleModel $model, Request $request)
    {
        $query = $model->availableParts()->with(['category', 'tags']);

        if ($request->has('category_id')) {
            $query->byCategory($request->category_id);
        }

        $parts = $query->paginate($request->get('per_page', 12));

        return response()->json($parts);
    }
}
