<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('part_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('part_tags')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['part_id', 'tag_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('part_tag');
    }
};
