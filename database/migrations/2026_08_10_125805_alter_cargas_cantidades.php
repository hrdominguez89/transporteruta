<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cargas', function (Blueprint $table) {
            $table->unsignedInteger('cantidad_bulto')->default(0);
            $table->unsignedInteger('cantidad_pallet_normal')->default(0);
            $table->unsignedInteger('cantidad_pallet_grande')->default(0);
            $table->unsignedInteger('rechazado_bulto')->default(0);
            $table->unsignedInteger('rechazado_pallet_normal')->default(0);
            $table->unsignedInteger('rechazado_pallet_grande')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('cargas', function (Blueprint $table) {
            $table->dropColumn([
                'cantidad_bulto',
                'cantidad_pallet_normal',
                'cantidad_pallet_grande',
                'rechazado_bulto',
                'rechazado_pallet_normal',
                'rechazado_pallet_grande',
            ]);
        });
    }
};