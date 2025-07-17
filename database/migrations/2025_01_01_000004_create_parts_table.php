<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('part_categories')->onDelete('cascade');
            $table->string('name', 200);
            $table->string('part_number', 100)->unique();
            $table->string('original_code', 100)->nullable();
            $table->string('brand', 100)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock')->default(5);
            $table->string('location', 100)->nullable();
            $table->string('image_path')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_bestseller')->default(false);
            $table->boolean('is_available')->default(true);
            $table->integer('view_count')->default(0);
            $table->timestamps();

            $table->index(['model_id', 'category_id']);
            $table->index('is_bestseller');
            $table->index('is_available');
            $table->index('stock_quantity');
            $table->index('name');
            $table->index('part_number');
            $table->index('original_code');
            $table->index('description');
        });
    }

    public function down()
    {
        Schema::dropIfExists('parts');
    }
};
