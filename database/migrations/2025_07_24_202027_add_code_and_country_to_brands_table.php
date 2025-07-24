<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->string('code', 10)->nullable()->after('name');
            $table->string('country', 100)->nullable()->after('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn(['code', 'country']);
        });
    }
};
