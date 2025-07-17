<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\VehicleModel;
use App\Models\PartCategory;
use App\Models\Part;
use App\Models\PartTag;
use Illuminate\Support\Str;

class PartsSeeder extends Seeder
{
    public function run()
    {
        // Crear marcas principales con slug (usar firstOrCreate para evitar duplicados)
        $brands = [
            ['name' => 'Toyota', 'description' => 'Marca japonesa líder en calidad y confiabilidad', 'slug' => 'toyota'],
            ['name' => 'Honda', 'description' => 'Tecnología japonesa innovadora', 'slug' => 'honda'],
            ['name' => 'Nissan', 'description' => 'Ingeniería japonesa avanzada', 'slug' => 'nissan'],
            ['name' => 'Hyundai', 'description' => 'Calidad coreana a precio accesible', 'slug' => 'hyundai'],
            ['name' => 'Kia', 'description' => 'Diseño moderno y garantía extendida', 'slug' => 'kia'],
            ['name' => 'Chevrolet', 'description' => 'Tradición americana en México', 'slug' => 'chevrolet'],
        ];

        foreach ($brands as $brandData) {
            Brand::firstOrCreate(['slug' => $brandData['slug']], $brandData);
        }

        // Crear categorías principales (solo si no existen)
        $categories = [
            [
                'name' => 'Motor',
                'slug' => 'motor',
                'icon' => '🔧',
                'color' => '#ef4444',
                'children' => [
                    'Filtros de aceite',
                    'Bujías',
                    'Correas',
                    'Mangueras',
                    'Pistones',
                    'Válvulas'
                ]
            ],
            [
                'name' => 'Frenos',
                'slug' => 'frenos',
                'icon' => '🛑',
                'color' => '#f59e0b',
                'children' => [
                    'Pastillas de freno',
                    'Discos de freno',
                    'Tambores',
                    'Líquido de frenos',
                    'Mangueras de freno',
                    'Cilindros'
                ]
            ],
            [
                'name' => 'Suspensión',
                'slug' => 'suspension',
                'icon' => '🚗',
                'color' => '#3b82f6',
                'children' => [
                    'Amortiguadores',
                    'Resortes',
                    'Rotulas',
                    'Bujes',
                    'Brazos',
                    'Barras estabilizadoras'
                ]
            ],
            [
                'name' => 'Sensores',
                'slug' => 'sensores',
                'icon' => '📡',
                'color' => '#10b981',
                'children' => [
                    'Sensor de oxígeno',
                    'Sensor MAP',
                    'Sensor TPS',
                    'Sensor de velocidad',
                    'Sensor de temperatura',
                    'Sensor ABS'
                ]
            ]
        ];

        foreach ($categories as $categoryData) {
            $parent = PartCategory::firstOrCreate(
                ['slug' => $categoryData['slug']],
                [
                    'name' => $categoryData['name'],
                    'slug' => $categoryData['slug'],
                    'icon' => $categoryData['icon'],
                    'color' => $categoryData['color'],
                ]
            );

            foreach ($categoryData['children'] as $childName) {
                $childSlug = Str::slug($childName);
                PartCategory::firstOrCreate(
                    ['slug' => $childSlug],
                    [
                        'name' => $childName,
                        'slug' => $childSlug,
                        'parent_id' => $parent->id,
                        'color' => $categoryData['color'],
                    ]
                );
            }
        }

        // Crear modelos de vehículos (solo si no existen)
        $toyotaModels = [
            ['name' => 'Corolla', 'code' => 'AE100', 'year_from' => 1991, 'year_to' => 1995],
            ['name' => 'Corolla', 'code' => 'AE101', 'year_from' => 1995, 'year_to' => 1998],
            ['name' => 'Corolla', 'code' => 'AE110', 'year_from' => 1998, 'year_to' => 2002],
            ['name' => 'Corolla', 'code' => 'ZZE122', 'year_from' => 2002, 'year_to' => 2007],
            ['name' => 'Camry', 'code' => 'XV30', 'year_from' => 2002, 'year_to' => 2006],
            ['name' => 'Camry', 'code' => 'XV40', 'year_from' => 2006, 'year_to' => 2011],
            ['name' => 'RAV4', 'code' => 'XA20', 'year_from' => 2000, 'year_to' => 2005],
            ['name' => 'Hilux', 'code' => 'VZN130', 'year_from' => 1997, 'year_to' => 2004],
        ];

        $hondaModels = [
            ['name' => 'Civic', 'code' => 'EK3', 'year_from' => 1996, 'year_to' => 2000],
            ['name' => 'Civic', 'code' => 'ES1', 'year_from' => 2001, 'year_to' => 2005],
            ['name' => 'Accord', 'code' => 'CF4', 'year_from' => 1998, 'year_to' => 2002],
            ['name' => 'CRV', 'code' => 'RD1', 'year_from' => 1997, 'year_to' => 2001],
        ];

        $toyotaBrand = Brand::where('slug', 'toyota')->first();
        $hondaBrand = Brand::where('slug', 'honda')->first();

        if ($toyotaBrand) {
            foreach ($toyotaModels as $modelData) {
                VehicleModel::firstOrCreate(
                    ['code' => $modelData['code']],
                    array_merge($modelData, [
                        'brand_id' => $toyotaBrand->id,
                        'is_popular' => in_array($modelData['name'], ['Corolla', 'Camry'])
                    ])
                );
            }
        }

        if ($hondaBrand) {
            foreach ($hondaModels as $modelData) {
                VehicleModel::firstOrCreate(
                    ['code' => $modelData['code']],
                    array_merge($modelData, [
                        'brand_id' => $hondaBrand->id,
                        'is_popular' => $modelData['name'] === 'Civic'
                    ])
                );
            }
        }

        // Crear tags (solo si no existen)
        $tags = [
            ['name' => 'OEM', 'slug' => 'oem', 'color' => '#059669'],
            ['name' => 'Aftermarket', 'slug' => 'aftermarket', 'color' => '#dc2626'],
            ['name' => 'Premium', 'slug' => 'premium', 'color' => '#7c3aed'],
            ['name' => 'Económico', 'slug' => 'economico', 'color' => '#2563eb'],
            ['name' => 'Importado', 'slug' => 'importado', 'color' => '#ea580c'],
            ['name' => 'Nacional', 'slug' => 'nacional', 'color' => '#16a34a'],
        ];

        foreach ($tags as $tagData) {
            PartTag::firstOrCreate(['slug' => $tagData['slug']], $tagData);
        }

        // Crear repuestos de ejemplo (solo si no existen)
        $models = VehicleModel::all();
        $categories = PartCategory::whereNotNull('parent_id')->get();
        $tags = PartTag::all();

        if ($models->count() > 0 && $categories->count() > 0) {
            $parts = [
                // Filtros de aceite
                ['name' => 'Filtro de Aceite Original', 'part_number' => 'FL-001', 'price' => 150.00, 'stock' => 25],
                ['name' => 'Filtro de Aceite Premium', 'part_number' => 'FL-002', 'price' => 200.00, 'stock' => 15],

                // Pastillas de freno
                ['name' => 'Pastillas Freno Delanteras', 'part_number' => 'BR-001', 'price' => 800.00, 'stock' => 12],
                ['name' => 'Pastillas Freno Traseras', 'part_number' => 'BR-002', 'price' => 600.00, 'stock' => 8],

                // Amortiguadores
                ['name' => 'Amortiguador Delantero Izq', 'part_number' => 'SH-001', 'price' => 1200.00, 'stock' => 6],
                ['name' => 'Amortiguador Delantero Der', 'part_number' => 'SH-002', 'price' => 1200.00, 'stock' => 6],

                // Sensores
                ['name' => 'Sensor de Oxígeno', 'part_number' => 'SN-001', 'price' => 450.00, 'stock' => 10],
                ['name' => 'Sensor MAP', 'part_number' => 'SN-002', 'price' => 320.00, 'stock' => 8],

                // Bujías
                ['name' => 'Bujía NGK Estándar', 'part_number' => 'SP-001', 'price' => 80.00, 'stock' => 50],
                ['name' => 'Bujía Iridium', 'part_number' => 'SP-002', 'price' => 220.00, 'stock' => 20],

                // Correas
                ['name' => 'Correa de Distribución', 'part_number' => 'BT-001', 'price' => 350.00, 'stock' => 15],
                ['name' => 'Correa del Alternador', 'part_number' => 'BT-002', 'price' => 120.00, 'stock' => 25],

                // Discos de freno
                ['name' => 'Disco Freno Delantero', 'part_number' => 'DR-001', 'price' => 650.00, 'stock' => 8],
                ['name' => 'Disco Freno Trasero', 'part_number' => 'DR-002', 'price' => 450.00, 'stock' => 10],

                // Rotulas
                ['name' => 'Rotula Superior', 'part_number' => 'BJ-001', 'price' => 280.00, 'stock' => 12],
                ['name' => 'Rotula Inferior', 'part_number' => 'BJ-002', 'price' => 320.00, 'stock' => 10],
            ];

            foreach ($parts as $index => $partData) {
                $model = $models->random();
                $category = $categories->random();

                $part = Part::firstOrCreate(
                    ['part_number' => $partData['part_number']],
                    array_merge($partData, [
                        'model_id' => $model->id,
                        'category_id' => $category->id,
                        'original_code' => 'OEM-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                        'brand' => $model->brand->name,
                        'location' => 'A' . rand(1, 5) . '-' . rand(1, 20),
                        'description' => 'Repuesto de alta calidad compatible con ' . $model->brand->name . ' ' . $model->name,
                        'is_bestseller' => rand(0, 100) < 20, // 20% de probabilidad de ser bestseller
                        'min_stock' => rand(3, 10),
                        'view_count' => rand(0, 100),
                    ])
                );

                // Asignar tags aleatorios a cada repuesto (solo si existen tags)
                if ($tags->count() > 0) {
                    $randomTags = $tags->random(rand(1, min(3, $tags->count())));
                    $part->tags()->syncWithoutDetaching($randomTags->pluck('id'));
                }
            }
        }

        $this->command->info('✅ Datos de ejemplo creados exitosamente!');
        $this->command->info('📊 Estadísticas:');
        $this->command->info('- ' . Brand::count() . ' marcas creadas');
        $this->command->info('- ' . VehicleModel::count() . ' modelos creados');
        $this->command->info('- ' . PartCategory::count() . ' categorías creadas');
        $this->command->info('- ' . Part::count() . ' repuestos creados');
        $this->command->info('- ' . PartTag::count() . ' tags creados');
    }
}
