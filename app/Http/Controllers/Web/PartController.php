<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePartRequest;
use App\Http\Requests\UpdatePartRequest;
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

            // 🔥 AGREGAR URLs DE IMAGEN A CADA REPUESTO
            $parts->getCollection()->transform(function ($part) {
                if ($part->image_path) {
                    $part->image_url = asset('storage/' . $part->image_path);
                }
                return $part;
            });

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
                    'brand' => '',
                    'price' => '',
                    'description' => '',
                    'model_id' => '',
                    'category_id' => '',
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Parts create error: ' . $e->getMessage());
            return redirect()->route('parts.index')
                ->with('error', 'Error al cargar el formulario');
        }
    }

    public function store(StorePartRequest $request)
    {
        try {
            $validated = $request->validated();

            \Log::info('📥 Store request validated data:', [
                'name' => $validated['name'],
                'brand' => $validated['brand'],
                'price' => $validated['price'],
                'has_image' => $request->hasFile('image'),
                'codes_count' => count($validated['codes'])
            ]);

            \DB::beginTransaction();

            // MANEJO DE IMÁGENES
            $imagePath = null;
            if ($request->hasFile('image')) {
                try {
                    $imageFile = $request->file('image');

                    \Log::info('📸 Processing image:', [
                        'original_name' => $imageFile->getClientOriginalName(),
                        'size' => $imageFile->getSize(),
                        'mime_type' => $imageFile->getMimeType(),
                        'is_valid' => $imageFile->isValid()
                    ]);

                    // Generar nombre único para la imagen
                    $fileName = time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();

                    // Guardar imagen en storage/app/public/parts/
                    $imagePath = $imageFile->storeAs('parts', $fileName, 'public');

                    \Log::info('✅ Image saved successfully:', [
                        'path' => $imagePath,
                        'full_path' => storage_path('app/public/' . $imagePath)
                    ]);

                } catch (\Exception $e) {
                    \Log::error('❌ Error uploading image: ' . $e->getMessage());
                    \DB::rollBack();
                    return back()
                        ->withInput()
                        ->withErrors(['image' => 'Error al subir la imagen: ' . $e->getMessage()]);
                }
            }

            // Crear el repuesto (siempre disponible por defecto)
            $part = Part::create([
                'name' => $validated['name'],
                'brand' => $validated['brand'],
                'price' => $validated['price'],
                'description' => $validated['description'],
                'model_id' => $validated['model_id'],
                'category_id' => $validated['category_id'],
                'is_available' => true,
                'image_path' => $imagePath,
                'part_number' => collect($validated['codes'])->firstWhere('is_primary', true)['code'] ?? $validated['codes'][0]['code'],
            ]);

            \Log::info('✅ Part created:', [
                'id' => $part->id,
                'name' => $part->name,
                'image_path' => $part->image_path
            ]);

            // Crear los códigos
            foreach ($validated['codes'] as $index => $codeData) {
                $partCode = $part->codes()->create([
                    'code' => trim($codeData['code']),
                    'type' => $codeData['type'],
                    'is_primary' => $codeData['is_primary'] ?? false,
                    'is_active' => $codeData['is_active'] ?? true,
                    'sort_order' => $index,
                ]);

                \Log::info('✅ Code created:', [
                    'code' => $partCode->code,
                    'type' => $partCode->type,
                    'is_primary' => $partCode->is_primary
                ]);
            }

            \DB::commit();

            \Log::info('🎉 Part creation completed successfully:', [
                'part_id' => $part->id,
                'codes_count' => $part->codes()->count(),
                'has_image' => !is_null($part->image_path)
            ]);

            return redirect()
                ->route('parts.show', $part)
                ->with('success', "Repuesto '{$part->name}' creado exitosamente con " . count($validated['codes']) . ' código(s)');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \DB::rollBack();
            \Log::error('❌ Validation errors:', $e->errors());

            return back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('❌ Parts store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error al crear el repuesto: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $part = Part::with(['model.brand', 'category', 'codes'])->findOrFail($id);

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

    public function edit($id)
    {
        try {
            $part = Part::with(['model.brand', 'category', 'codes' => function($query) {
                $query->orderBy('sort_order')->orderBy('is_primary', 'desc');
            }])->findOrFail($id);

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

            return Inertia::render('Parts/Edit', [
                'part' => $part,
                'categories' => $categories,
                'brands' => $brands,
                'models' => $models,
            ]);
        } catch (\Exception $e) {
            \Log::error('Part edit error: ' . $e->getMessage());
            return redirect()->route('parts.index')
                ->with('error', 'Repuesto no encontrado');
        }
    }

    public function update(UpdatePartRequest $request, $id)
    {
        try {
            $part = Part::findOrFail($id);

            // Debug: Verificar que llegan los datos
            \Log::info('Update request received:', [
                'name' => $request->input('name'),
                'brand' => $request->input('brand'),
                'price' => $request->input('price'),
                'has_image' => $request->hasFile('image'),
                'codes_count' => count($request->input('codes', []))
            ]);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'brand' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'model_id' => 'required|exists:models,id',
                'category_id' => 'required|exists:part_categories,id',
                'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
                'remove_image' => 'sometimes|boolean',
                'codes' => 'required|array|min:1',
                'codes.*.code' => 'required|string|max:100',
                'codes.*.type' => 'required|in:internal,oem,aftermarket,universal',
                'codes.*.is_primary' => 'sometimes|boolean',
                'codes.*.is_active' => 'sometimes|boolean',
            ]);

            \DB::commit();

            // 🔥 RESPONDER SEGÚN EL TIPO DE REQUEST
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Repuesto '{$part->name}' actualizado exitosamente",
                    'redirect' => route('parts.show', $part)
                ]);
            }

            return redirect()
                ->route('parts.show', $part)
                ->with('success', "Repuesto '{$part->name}' actualizado exitosamente");

        } catch (\Illuminate\Validation\ValidationException $e) {
            \DB::rollBack();

            // Responder con errores JSON si es AJAX
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Parts update error: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['general' => 'Error interno del servidor']
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el repuesto: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $part = Part::with('codes')->findOrFail($id);

            \DB::beginTransaction();

            // Eliminar imagen si existe
            if ($part->image_path && \Storage::disk('public')->exists($part->image_path)) {
                \Storage::disk('public')->delete($part->image_path);
            }

            // Eliminar códigos relacionados
            $part->codes()->delete();

            // Guardar nombre para el mensaje
            $partName = $part->name;

            // Eliminar el repuesto
            $part->delete();

            \DB::commit();

            return redirect()
                ->route('parts.index')
                ->with('success', "Repuesto '{$partName}' eliminado exitosamente");

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Part destroy error: ' . $e->getMessage());

            return redirect()
                ->route('parts.index')
                ->with('error', 'Error al eliminar el repuesto: ' . $e->getMessage());
        }
    }
}
