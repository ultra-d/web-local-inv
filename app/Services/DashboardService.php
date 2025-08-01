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
            'availableParts' => Part::available()->count(),
            'categories' => PartCategory::whereNull('parent_id')->count(),
        ];
    }

    private function getMainCategories()
    {
        return PartCategory::whereNull('parent_id')
            ->withCount(['parts' => function ($query) {
                $query->available();
            }])
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
        return Part::with(['model.brand', 'category', 'codes' => function($query) {
                $query->where('is_primary', true);
            }])
            ->available()
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();
    }
}
