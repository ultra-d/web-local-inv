<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PartCategory;
use Illuminate\Http\Request;

class PartCategoryController extends Controller
{
    public function index()
    {
        $categories = PartCategory::active()
            ->with('children')
            ->whereNull('parent_id')
            ->withCount('parts')
            ->orderBy('sort_order')
            ->get();

        return response()->json($categories);
    }

    public function show(PartCategory $category)
    {
        $category->load(['children', 'parts.model.brand']);

        return response()->json($category);
    }

    public function parts(PartCategory $category, Request $request)
    {
        $query = $category->parts()->with(['model.brand', 'tags'])->available();

        if ($request->has('model_id')) {
            $query->byModel($request->model_id);
        }

        $parts = $query->paginate($request->get('per_page', 12));

        return response()->json($parts);
    }
}
