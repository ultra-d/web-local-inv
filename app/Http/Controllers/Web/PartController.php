<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\VehicleModel;
use App\Models\Brand;
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

    public function create()
    {
        try {
            // Obtener todos los datos necesarios para el formulario
            $categories = PartCategory::active()
                ->with('children')
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get();

            $brands = Brand::active()
                ->orderBy('name')
                ->get();

            $models = VehicleModel::with('brand')
                ->active()
                ->orderBy('name')
                ->get();

            return Inertia::render('Parts/Create', [
                'categories' => $categories,
                'brands' => $brands,
                'models' => $models,
                'formData' => [
                    'name' => '',
                    'part_number' => '',
                    'original_code' => '',
                    'brand' => '',
                    'price' => '',
                    'stock_quantity' => '',
                    'min_stock' => 5,
                    'location' => '',
                    'description' => '',
                    'notes' => '',
                    'model_id' => '',
                    'category_id' => '',
                    'is_bestseller' => false,
                    'is_available' => true,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Parts create error: ' . $e->getMessage());
            return redirect()->route('parts.index')
                ->with('error', 'Error al cargar el formulario');
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'part_number' => 'required|string|max:100|unique:parts,part_number',
                'original_code' => 'nullable|string|max:100',
                'brand' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'stock_quantity' => 'required|integer|min:0',
                'min_stock' => 'required|integer|min:0',
                'location' => 'nullable|string|max:100',
                'description' => 'nullable|string',
                'notes' => 'nullable|string',
                'model_id' => 'required|exists:vehicle_models,id',  // ← CORREGIDO: tabla correcta
                'category_id' => 'required|exists:part_categories,id',
                'is_bestseller' => 'boolean',
                'is_available' => 'boolean',
            ]);

            $part = Part::create($validated);

            return redirect()
                ->route('parts.show', $part)
                ->with('success', 'Repuesto creado exitosamente');

        } catch (\Exception $e) {
            \Log::error('Parts store error: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al crear el repuesto: ' . $e->getMessage());
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

    // Métodos AJAX para crear opciones dinámicamente
    public function createCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:part_categories,id',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:10',
        ]);

        $category = PartCategory::create([
            'name' => $validated['name'],
            'parent_id' => $validated['parent_id'] ?? null,
            'color' => $validated['color'] ?? '#3B82F6',
            'icon' => $validated['icon'] ?? '📦',
            'sort_order' => PartCategory::max('sort_order') + 1,
            'is_active' => true,
        ]);

        return response()->json($category);
    }

    public function createBrand(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'description' => 'nullable|string',
        ]);

        $brand = Brand::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'is_active' => true,
        ]);

        return response()->json($brand);
    }

    public function createModel(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'brand_id' => 'required|exists:brands,id',
            'year_from' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'year_to' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'description' => 'nullable|string',
        ]);

        $model = VehicleModel::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'brand_id' => $validated['brand_id'],
            'year_from' => $validated['year_from'],
            'year_to' => $validated['year_to'],
            'description' => $validated['description'] ?? '',
            'is_active' => true,
        ]);

        $model->load('brand'); // Cargar la relación para la respuesta

        return response()->json($model);
    }
}
