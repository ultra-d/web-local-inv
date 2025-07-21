<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\VehicleModel;

class DashboardService
{
    public function getDashboardData(): array
    {
        return [
            'stats' => $this->getStats(),
            'categories' => $this->getMainCategories(),
            'brands' => $this->getPopularBrands(),
            'recentParts' => $this->getRecentParts(),
        ];
    }

    private function getStats(): array
    {
        return [
            'totalParts' => Part::count(),
            'lowStock' => Part::lowStock()->count(),
            'bestsellers' => Part::where('is_bestseller', true)->count(),
            'categories' => PartCategory::whereNull('parent_id')->count(),
        ];
    }

    private function getMainCategories()
    {
        return PartCategory::whereNull('parent_id')
            ->withCount('parts')
            ->orderBy('sort_order')
            ->take(4)
            ->get();
    }

    private function getPopularBrands()
    {
        return Brand::active()
            ->withCount(['models' => function ($query) {
                $query->active();
            }])
            ->orderByDesc('models_count')
            ->take(6)
            ->get();
    }

    private function getRecentParts()
    {
        return Part::with(['model.brand', 'category'])
            ->available()
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();
    }
}
