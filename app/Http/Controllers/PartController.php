<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Part;
use Illuminate\Http\Request;

class PartController extends Controller
{
    public function index(Request $request)
    {
        $query = Part::with(['model.brand', 'category', 'tags']);

        // Filtros
        if ($request->has('search')) {
            $query->search($request->search);
        }

        if ($request->has('category_id')) {
            $query->byCategory($request->category_id);
        }

        if ($request->has('model_id')) {
            $query->byModel($request->model_id);
        }

        if ($request->has('brand_id')) {
            $query->whereHas('model', function ($q) use ($request) {
                $q->where('brand_id', $request->brand_id);
            });
        }

        if ($request->has('in_stock') && $request->in_stock) {
            $query->inStock();
        }

        if ($request->has('bestsellers') && $request->bestsellers) {
            $query->bestseller();
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');

        switch ($sortBy) {
            case 'price':
                $query->orderBy('price', $sortOrder);
                break;
            case 'stock':
                $query->orderBy('stock_quantity', $sortOrder);
                break;
            case 'popularity':
                $query->orderBy('view_count', 'desc');
                break;
            default:
                $query->orderBy('name', $sortOrder);
        }

        $parts = $query->paginate($request->get('per_page', 12));

        return response()->json($parts);
    }

    public function show(Part $part)
    {
        $part->load(['model.brand', 'category', 'tags']);
        $part->incrementViews();

        // Repuestos relacionados
        $relatedParts = Part::with(['model.brand', 'category'])
            ->where('id', '!=', $part->id)
            ->where(function ($query) use ($part) {
                $query->where('model_id', $part->model_id)
                      ->orWhere('category_id', $part->category_id);
            })
            ->available()
            ->limit(4)
            ->get();

        return response()->json([
            'part' => $part,
            'relatedParts' => $relatedParts,
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->get('q');

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $parts = Part::with(['model.brand', 'category'])
            ->search($query)
            ->available()
            ->limit(10)
            ->get();

        return response()->json(['results' => $parts]);
    }
}
