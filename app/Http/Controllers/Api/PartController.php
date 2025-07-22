<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Part;
use Illuminate\Http\Request;

class PartController extends Controller
{
    public function index(Request $request)
    {
        $query = Part::with(['model.brand', 'category']);

        // Filtros básicos
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('part_number', 'like', "%{$search}%")
                  ->orWhere('original_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $parts = $query->available()->paginate(12);
        return response()->json($parts);
    }

    public function show($id)
    {
        $part = Part::with(['model.brand', 'category'])->findOrFail($id);
        return response()->json($part);
    }

    public function search(Request $request)
    {
        // Validación mejorada
        $request->validate([
            'q' => 'required|string|min:2|max:255'
        ]);

        $query = $request->get('q');

        try {
            $parts = Part::with(['model.brand', 'category'])
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('part_number', 'like', "%{$query}%")
                      ->orWhere('original_code', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%");
                })
                ->available()
                ->limit(20)
                ->get();

            // CORRECCIÓN: Devolver array directo, no objeto con results
            return response()->json($parts);

        } catch (\Exception $e) {
            \Log::error('Search error: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }
}
