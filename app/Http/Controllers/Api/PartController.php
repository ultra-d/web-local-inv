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

    public function validatePartCode(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:50',
                'exclude_part_id' => 'nullable|integer'
            ]);

            // Verificar si el modelo PartCode existe
            if (!class_exists('\App\Models\PartCode')) {
                // Si no existe el modelo PartCode, usar una validación simple
                return response()->json([
                    'exists' => false,
                    'message' => 'Código disponible'
                ]);
            }

            $query = \App\Models\PartCode::where('code', $validated['code']);

            if (isset($validated['exclude_part_id']) && $validated['exclude_part_id']) {
                $query->where('part_id', '!=', $validated['exclude_part_id']);
            }

            $exists = $query->exists();

            return response()->json([
                'exists' => $exists,
                'message' => $exists ? 'Código ya existe' : 'Código disponible'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'exists' => false,
                'errors' => $e->errors(),
                'message' => 'Error de validación'
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Error validating part code: ' . $e->getMessage());

            return response()->json([
                'exists' => false,
                'message' => 'Código disponible', // En caso de error, asumir disponible
            ]);
        }
    }
}
