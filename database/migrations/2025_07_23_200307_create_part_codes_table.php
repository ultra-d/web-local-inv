<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('part_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('parts')->onDelete('cascade');
            $table->string('code', 100)->index(); // Código del repuesto
            $table->enum('type', ['internal', 'oem', 'aftermarket', 'universal'])->default('internal');
            $table->boolean('is_primary')->default(false); // Si es el código principal
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Índices
            $table->unique(['part_id', 'code']); // Un código por repuesto
            $table->index(['code', 'type']);
            $table->index(['part_id', 'is_primary']);
        });

        // Migrar códigos existentes SOLO si la tabla parts tiene datos
        if (Schema::hasTable('parts') && \DB::table('parts')->exists()) {
            // Migrar part_number como código interno
            \DB::statement("
                INSERT INTO part_codes (part_id, code, type, is_primary, is_active, created_at, updated_at)
                SELECT
                    id,
                    part_number,
                    'internal',
                    true,
                    true,
                    created_at,
                    updated_at
                FROM parts
                WHERE part_number IS NOT NULL AND part_number != ''
            ");

            // Migrar original_code como código OEM (en lugar de 'original')
            \DB::statement("
                INSERT INTO part_codes (part_id, code, type, is_primary, is_active, created_at, updated_at)
                SELECT
                    id,
                    original_code,
                    'oem',
                    false,
                    true,
                    created_at,
                    updated_at
                FROM parts
                WHERE original_code IS NOT NULL
                AND original_code != ''
                AND original_code NOT IN (
                    SELECT code FROM part_codes WHERE part_id = parts.id
                )
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_codes');
    }
};
