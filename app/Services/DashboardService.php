<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\VehicleModel;

class DashboardService
{
    public function getDashboardData():array
    {
        // Stats simplificadas
        $stats = [
            'totalParts' => Part::count(),
            'availableParts' => Part::available()->count(),
            'categories' => PartCategory::active()->count(),
        ];

        // Categorías principales
        $categories = PartCategory::active()
            ->whereNull('parent_id')
            ->withCount(['parts' => function ($query) {
                $query->available();
            }])
            ->orderBy('parts_count', 'desc')
            ->take(6)
            ->get();

        // Marcas populares
        $brands = Brand::active()
            ->withCount('models')
            ->orderBy('models_count', 'desc')
            ->take(6)
            ->get();

        // 🔥 REPUESTOS RECIENTES CON IMÁGENES
        $recentParts = Part::with(['category', 'codes'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($part) {
                // Agregar URL de imagen
                if ($part->image_path) {
                    $part->image_url = asset('storage/' . $part->image_path);
                }
                return $part;
            });

        return [
            'stats' => $stats,
            'categories' => $categories,
            'brands' => $brands,
            'recentParts' => $recentParts,
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
