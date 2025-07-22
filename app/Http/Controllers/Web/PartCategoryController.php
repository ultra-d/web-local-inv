<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PartCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PartCategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = PartCategory::active()
                ->with('children')
                ->whereNull('parent_id')
                ->withCount('parts')
                ->orderBy('sort_order')
                ->get();

            return Inertia::render('Categories/Index', [
                'categories' => $categories,
                'stats' => [
                    'total_categories' => PartCategory::count(),
                    'active_categories' => PartCategory::active()->count(),
                    'categories_with_parts' => PartCategory::has('parts')->count(),
                    'main_categories' => PartCategory::whereNull('parent_id')->count(),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Categories index error: ' . $e->getMessage());

            return Inertia::render('Categories/Index', [
                'categories' => [],
                'stats' => [
                    'total_categories' => 0,
                    'active_categories' => 0,
                    'categories_with_parts' => 0,
                    'main_categories' => 0,
                ],
                'error' => 'Error al cargar las categorías'
            ]);
        }
    }

    public function show($id)
    {
        try {
            $category = PartCategory::with(['children', 'parts.model.brand'])
                ->findOrFail($id);

            return Inertia::render('Categories/Show', [
                'category' => $category
            ]);
        } catch (\Exception $e) {
            \Log::error('Category show error: ' . $e->getMessage());
            return redirect()->route('categories.index')
                ->with('error', 'Categoría no encontrada');
        }
    }
}
