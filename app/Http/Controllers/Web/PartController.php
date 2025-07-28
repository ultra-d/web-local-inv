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
            $query = Part::with(['model.brand', 'category', 'codes']);

            // Aplicar filtros si existen
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%")
                    ->orWhere('original_code', 'like', "%{$search}%")
                    ->orWhereHas('codes', function ($codeQuery) use ($search) {
                        $codeQuery->where('code', 'like', "%{$search}%")
                                ->where('is_active', true);
                    });
                });
            }

            // 🔥 FILTRO JERÁRQUICO DE CATEGORÍAS
            if ($request->has('category_id') && $request->category_id) {
                $selectedCategoryId = $request->category_id;

                // Verificar si la categoría seleccionada es principal (parent_id = null)
                $selectedCategory = PartCategory::find($selectedCategoryId);

                if ($selectedCategory) {
                    if ($selectedCategory->parent_id === null) {
                        // Es categoría principal: incluir repuestos de la categoría Y de todas sus subcategorías
                        $subcategoryIds = PartCategory::where('parent_id', $selectedCategoryId)
                                                    ->pluck('id')
                                                    ->toArray();

                        // Incluir la categoría principal + todas sus subcategorías
                        $allCategoryIds = array_merge([$selectedCategoryId], $subcategoryIds);

                        $query->whereIn('category_id', $allCategoryIds);

                        \Log::info("Filtro jerárquico aplicado:", [
                            'categoria_principal' => $selectedCategoryId,
                            'subcategorias' => $subcategoryIds,
                            'todas_las_categorias' => $allCategoryIds
                        ]);
                    } else {
                        // Es subcategoría: solo mostrar repuestos de esa subcategoría específica
                        $query->where('category_id', $selectedCategoryId);

                        \Log::info("Filtro subcategoría aplicado:", [
                            'subcategoria' => $selectedCategoryId
                        ]);
                    }
                }
            }

            $parts = $query->available()->paginate(12);

            // Obtener TODAS las categorías (principales y subcategorías) para filtros
            $allCategories = PartCategory::active()
                ->withCount(['parts' => function ($query) {
                    $query->available(); // Solo contar repuestos disponibles
                }])
                ->orderBy('parent_id', 'asc') // Primero las principales, luego las subcategorías
                ->orderBy('name')
                ->get();

            // Obtener también categorías jerárquicas para el frontend
            $hierarchicalCategories = PartCategory::active()
                ->with(['children' => function ($query) {
                    $query->withCount(['parts' => function ($subQuery) {
                        $subQuery->available();
                    }]);
                }])
                ->whereNull('parent_id')
                ->withCount(['parts' => function ($query) {
                    $query->available();
                }])
                ->orderBy('name')
                ->get();

            return Inertia::render('Parts/Index', [
                'parts' => $parts,
                'categories' => $allCategories, // Todas las categorías para el filtro
                'hierarchicalCategories' => $hierarchicalCategories, // Para otros usos si necesitas
                'filters' => $request->only(['search', 'category_id']),
                'stats' => [
                    'total_parts' => Part::count(),
                    'available_parts' => Part::available()->count(),
                    'out_of_stock' => Part::where('stock_quantity', '<=', 0)->count(),
                    'low_stock' => Part::where('stock_quantity', '>', 0)
                                    ->whereColumn('stock_quantity', '<=', 'min_stock')
                                    ->count(),
                    'bestsellers' => Part::where('is_bestseller', true)->count()
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Parts index error: ' . $e->getMessage());

            return Inertia::render('Parts/Index', [
                'parts' => ['data' => [], 'total' => 0],
                'categories' => [],
                'hierarchicalCategories' => [],
                'filters' => [],
                'stats' => [
                    'total_parts' => 0,
                    'available_parts' => 0,
                    'out_of_stock' => 0,
                    'low_stock' => 0,
                    'bestsellers' => 0
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
                'brand' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'stock_quantity' => 'required|integer|min:0',
                'min_stock' => 'required|integer|min:0',
                'location' => 'nullable|string|max:100',
                'description' => 'nullable|string',
                'notes' => 'nullable|string',
                'model_id' => 'required|exists:models,id',
                'category_id' => 'required|exists:part_categories,id',
                'is_bestseller' => 'boolean',
                'is_available' => 'boolean',
                'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
                'codes' => 'required|array|min:1',
                'codes.*.code' => 'required|string|max:100',
                'codes.*.type' => 'required|in:internal,oem,aftermarket,universal',
                'codes.*.is_primary' => 'boolean',
                'codes.*.is_active' => 'boolean',
            ]);

            // 🔍 VALIDACIÓN 1: Códigos duplicados en el mismo formulario
            $codes = collect($validated['codes'])->pluck('code')->map('trim')->filter()->toArray();
            $duplicateCodesInForm = array_diff_assoc($codes, array_unique($codes));

            if (!empty($duplicateCodesInForm)) {
                return back()
                    ->withInput()
                    ->withErrors(['codes' => 'No puede haber códigos duplicados en el formulario: ' . implode(', ', array_unique($duplicateCodesInForm))]);
            }

            // 🔍 VALIDACIÓN 2: Códigos que ya existen en la base de datos
            $existingCodes = \App\Models\PartCode::whereIn('code', $codes)
                ->with('part') // Cargar información del repuesto
                ->get();

            if ($existingCodes->isNotEmpty()) {
                $errorMessage = 'Los siguientes códigos ya están en uso: ';
                $codeErrors = [];

                foreach ($existingCodes as $existingCode) {
                    $codeErrors[] = "{$existingCode->code} (usado en: {$existingCode->part->name})";
                }

                return back()
                    ->withInput()
                    ->withErrors(['codes' => $errorMessage . implode(', ', $codeErrors)]);
            }

            // 🔍 VALIDACIÓN 3: Asegurar que haya exactamente un código primary
            $primaryCodes = collect($validated['codes'])->where('is_primary', true);

            if ($primaryCodes->count() === 0) {
                // Si no hay primary, hacer el primero como primary
                $validated['codes'][0]['is_primary'] = true;
            } elseif ($primaryCodes->count() > 1) {
                // Si hay más de uno primary, mantener solo el primero
                $primaryFound = false;
                foreach ($validated['codes'] as $index => $code) {
                    if ($code['is_primary'] && !$primaryFound) {
                        $primaryFound = true;
                    } elseif ($code['is_primary'] && $primaryFound) {
                        $validated['codes'][$index]['is_primary'] = false;
                    }
                }
            }

            \DB::beginTransaction();

            // Manejar subida de imagen
            $imagePath = null;
            if ($request->hasFile('image')) {
                try {
                    $imagePath = $request->file('image')->store('parts', 'public');
                } catch (\Exception $e) {
                    \Log::error('Error uploading image: ' . $e->getMessage());
                    return back()
                        ->withInput()
                        ->withErrors(['image' => 'Error al subir la imagen. Inténtalo de nuevo.']);
                }
            }

            // Crear el repuesto
            $part = Part::create([
                'name' => $validated['name'],
                'brand' => $validated['brand'],
                'price' => $validated['price'],
                'stock_quantity' => $validated['stock_quantity'],
                'min_stock' => $validated['min_stock'],
                'location' => $validated['location'],
                'description' => $validated['description'],
                'notes' => $validated['notes'],
                'model_id' => $validated['model_id'],
                'category_id' => $validated['category_id'],
                'is_bestseller' => $validated['is_bestseller'] ?? false,
                'is_available' => $validated['is_available'] ?? true,
                'image_path' => $imagePath,
                'part_number' => collect($validated['codes'])->firstWhere('is_primary', true)['code'] ?? $validated['codes'][0]['code'],
            ]);

            // Crear los códigos
            foreach ($validated['codes'] as $index => $codeData) {
                $part->codes()->create([
                    'code' => trim($codeData['code']),
                    'type' => $codeData['type'],
                    'is_primary' => $codeData['is_primary'] ?? false,
                    'is_active' => $codeData['is_active'] ?? true,
                    'sort_order' => $index,
                ]);
            }

            \DB::commit();

            return redirect()
                ->route('parts.show', $part)
                ->with('success', "Repuesto '{$part->name}' creado exitosamente con " . count($validated['codes']) . ' código(s)');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \DB::rollBack();
            return back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            \DB::rollBack();
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
    /**
        * Validar si un código de repuesto ya existe
    */
    public function validatePartCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:100',
            'exclude_part_id' => 'nullable|integer'
        ]);

        $code = trim($request->code);
        $excludePartId = $request->exclude_part_id;

        $query = \App\Models\PartCode::where('code', $code);

        if ($excludePartId) {
            $query->where('part_id', '!=', $excludePartId);
        }

        $existingCode = $query->with(['part:id,name'])->first();

        if ($existingCode) {
            return response()->json([
                'exists' => true,
                'message' => "El código '{$code}' ya está en uso",
                'used_in' => $existingCode->part ? $existingCode->part->name : 'Repuesto desconocido'
            ]);
        }

        return response()->json([
            'exists' => false,
            'message' => 'Código disponible'
        ]);
    }

}
