<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->string('slug')->unique();
            $table->year('year_from')->nullable();
            $table->year('year_to')->nullable();
            $table->string('image_path')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['brand_id', 'is_active']);
            $table->index('is_popular');
        });
    }

    public function down()
    {
        Schema::dropIfExists('models');
    }
};
