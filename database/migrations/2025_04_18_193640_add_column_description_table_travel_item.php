<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Agregar columna descripcion (texto)
        Schema::table('travel_items', function (Blueprint $table) {
            $table->string('description')->nullable()->after('type');
            $table->string('percent')->nullable()->after('distance');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('travel_items', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
