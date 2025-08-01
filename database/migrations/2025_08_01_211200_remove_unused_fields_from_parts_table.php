<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Verificar si estamos usando SQLite
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // Para SQLite, usar SQL directo sin intentar eliminar índices
            DB::statement('PRAGMA foreign_keys=off;');

            // Crear tabla temporal sin las columnas no deseadas
            DB::statement('
                CREATE TABLE parts_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    model_id INTEGER NOT NULL,
                    category_id INTEGER NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    part_number VARCHAR(255),
                    original_code VARCHAR(255),
                    brand VARCHAR(255) NOT NULL,
                    price DECIMAL(10,2) NOT NULL,
                    image_path VARCHAR(255),
                    description TEXT,
                    is_available BOOLEAN DEFAULT 1,
                    view_count INTEGER DEFAULT 0,
                    created_at DATETIME,
                    updated_at DATETIME
                );
            ');

            // Copiar datos (solo las columnas que queremos mantener)
            DB::statement('
                INSERT INTO parts_new (
                    id, model_id, category_id, name, part_number, original_code,
                    brand, price, image_path, description, is_available, view_count,
                    created_at, updated_at
                )
                SELECT
                    id, model_id, category_id, name, part_number, original_code,
                    brand, price, image_path, description, is_available, view_count,
                    created_at, updated_at
                FROM parts;
            ');

            // Eliminar tabla original y renombrar
            DB::statement('DROP TABLE parts;');
            DB::statement('ALTER TABLE parts_new RENAME TO parts;');

            DB::statement('PRAGMA foreign_keys=on;');

        } else {
            // Para MySQL/PostgreSQL
            Schema::table('parts', function (Blueprint $table) {
                $table->dropColumn([
                    'stock_quantity',
                    'min_stock',
                    'location',
                    'notes',
                    'is_bestseller'
                ]);
            });
        }
    }

    public function down()
    {
        Schema::table('parts', function (Blueprint $table) {
            // Restaurar campos en caso de rollback
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock')->default(5);
            $table->string('location', 100)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_bestseller')->default(false);
        });
    }
};
