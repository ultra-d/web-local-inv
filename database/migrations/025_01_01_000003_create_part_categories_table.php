<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('part_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug')->unique();
            $table->string('icon', 100)->nullable();
            $table->string('color', 7)->default('#6366f1');
            $table->foreignId('parent_id')->nullable()->constrained('part_categories')->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('parent_id');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('part_categories');
    }
};
