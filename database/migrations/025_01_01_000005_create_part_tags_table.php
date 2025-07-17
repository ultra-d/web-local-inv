<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('part_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('slug')->unique();
            $table->string('color', 7)->default('#6366f1');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('part_tags');
    }
};
