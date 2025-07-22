<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Part;
use App\Models\PartCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PartController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Part::with(['model.brand', 'category']);

            // Aplicar filtros si existen
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('part_number', 'like', "%{$search}%")
                      ->orWhere('original_code', 'like', "%{$search}%");
                });
            }

            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            $parts = $query->available()->paginate(12);

            // Obtener categorías para filtros
            $categories = PartCategory::active()
                ->withCount('parts')
                ->orderBy('name')
                ->get();

            return Inertia::render('Parts/Index', [
                'parts' => $parts,
                'categories' => $categories,
                'filters' => $request->only(['search', 'category_id']),
                'stats' => [
                    'total_parts' => Part::count(),
                    'available_parts' => Part::available()->count(),
                    'out_of_stock' => Part::where('stock_quantity', '<=', 0)->count(),
                    'low_stock' => Part::whereBetween('stock_quantity', [1, 5])->count(),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Parts index error: ' . $e->getMessage());

            return Inertia::render('Parts/Index', [
                'parts' => ['data' => [], 'total' => 0],
                'categories' => [],
                'filters' => [],
                'stats' => [
                    'total_parts' => 0,
                    'available_parts' => 0,
                    'out_of_stock' => 0,
                    'low_stock' => 0,
                ],
                'error' => 'Error al cargar los repuestos'
            ]);
        }
    }

    public function show($id)
    {
        try {
            $part = Part::with(['model.brand', 'category'])->findOrFail($id);

            return Inertia::render('Parts/Show', [
                'part' => $part
            ]);
        } catch (\Exception $e) {
            \Log::error('Part show error: ' . $e->getMessage());
            return redirect()->route('parts.index')
                ->with('error', 'Repuesto no encontrado');
        }
    }
}
