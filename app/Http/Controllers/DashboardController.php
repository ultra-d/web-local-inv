<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\VehicleModel;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'totalParts' => Part::count(),
            'lowStock' => Part::lowStock()->count(),
            'bestsellers' => Part::where('is_bestseller', true)->count(),
            'categories' => PartCategory::whereNull('parent_id')->count(),
        ];

        $categories = PartCategory::whereNull('parent_id')
            ->withCount('parts')
            ->orderBy('sort_order')
            ->take(4)
            ->get();

        $brands = Brand::active()
            ->withCount(['models' => function ($query) {
                $query->active();
            }])
            ->orderByDesc('models_count')
            ->take(6)
            ->get();

        $recentParts = Part::with(['model.brand', 'category'])
            ->available()
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        return response()->json([
            'stats' => $stats,
            'categories' => $categories,
            'brands' => $brands,
            'recentParts' => $recentParts,
        ]);
    }
}
