<?php

namespace App\Services;

use App\Models\Brand;

class BrandService
{
    public function getAllBrands()
    {
        return Brand::active()
            ->withCount(['models' => function ($query) {
                $query->active();
            }])
            ->orderBy('name')
            ->get();
    }

    public function getBrandById($id)
    {
        return Brand::with(['activeModels.parts' => function ($query) {
            $query->available();
        }])->findOrFail($id);
    }

    public function getBrandModels($brandId)
    {
        $brand = Brand::findOrFail($brandId);

        return $brand->activeModels()
            ->withCount(['parts' => function ($query) {
                $query->available();
            }])
            ->orderBy('name')
            ->get();
    }

    public function createBrand(array $data)
    {
        return Brand::create($data);
    }

    public function updateBrand($id, array $data)
    {
        $brand = Brand::findOrFail($id);
        $brand->update($data);
        return $brand;
    }

    public function deleteBrand($id)
    {
        $brand = Brand::findOrFail($id);
        return $brand->delete();
    }
}
