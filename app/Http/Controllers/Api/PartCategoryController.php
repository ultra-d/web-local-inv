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

    public function show($id)
    {
        $category = PartCategory::with(['children', 'parts.model.brand'])
            ->findOrFail($id);

        return response()->json($category);
    }

    public function parts($categoryId, Request $request)
    {
        $category = PartCategory::findOrFail($categoryId);

        $query = $category->parts()->with(['model.brand'])->available();

        $parts = $query->paginate($request->get('per_page', 12));
        return response()->json($parts);
    }
}
