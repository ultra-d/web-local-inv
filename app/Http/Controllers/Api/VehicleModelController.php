<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VehicleModel;
use Illuminate\Http\Request;

class VehicleModelController extends Controller
{
    public function index()
    {
        $models = VehicleModel::with('brand')
            ->active()
            ->withCount(['parts' => function ($query) {
                $query->available();
            }])
            ->orderBy('name')
            ->get();

        return response()->json($models);
    }

    public function show($id)
    {
        $model = VehicleModel::with(['brand', 'parts.category'])
            ->findOrFail($id);

        return response()->json($model);
    }

    public function parts($modelId, Request $request)
    {
        $model = VehicleModel::findOrFail($modelId);

        $query = $model->parts()->with(['category'])->available();

        $parts = $query->paginate($request->get('per_page', 12));
        return response()->json($parts);
    }
}
