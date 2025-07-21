<?php

namespace App\Services;

use App\Models\Part;
use App\Models\PartCategory;

class SearchService
{
    public function search(string $query, array $filters = []): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        $partsQuery = Part::with(['model.brand', 'category'])
            ->search($query)
            ->available();

        // Aplicar filtros
        if (!empty($filters['category'])) {
            $partsQuery->where('category_id', $filters['category']);
        }

        if (!empty($filters['status'])) {
            $this->applyStatusFilter($partsQuery, $filters['status']);
        }

        return $partsQuery->limit(20)->get()->toArray();
    }

    public function getSearchFilters(): array
    {
        return [
            'categories' => PartCategory::active()
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get(),
            'brands' => \App\Models\Brand::active()
                ->orderBy('name')
                ->get(['id', 'name'])
        ];
    }

    private function applyStatusFilter($query, string $status): void
    {
        switch ($status) {
            case 'available':
                $query->where('is_available', true);
                break;
            case 'in_stock':
                $query->where('stock_quantity', '>', 0);
                break;
            case 'bestseller':
                $query->where('is_bestseller', true);
                break;
        }
    }
}
