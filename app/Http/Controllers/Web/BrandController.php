<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\BrandService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BrandController extends Controller
{
    public function __construct(
        private BrandService $brandService
    ) {}

    public function index()
    {
        $brands = $this->brandService->getAllBrands();
        return Inertia::render('Brands/Index', [
            'brands' => $brands
        ]);
    }

    public function show($id)
    {
        $brand = $this->brandService->getBrandById($id);
        return Inertia::render('Brands/Show', [
            'brand' => $brand
        ]);
    }
}
