<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('travel_certificates', function (Blueprint $table) {
            // Cambiar a DECIMAL(15,2) o DECIMAL(20,2) según necesites
            $table->decimal('total', 15, 2)->change();
            $table->decimal('iva', 15, 2)->change();
            
            // Si tienes otras columnas con montos, también modifícalas:
            // $table->decimal('subtotal', 15, 2)->change();
            // $table->decimal('descuento', 15, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('travel_certificates', function (Blueprint $table) {
            // Volver al tamaño anterior (ajusta según tu migración original)
            $table->decimal('total', 10, 2)->change();
            $table->decimal('iva', 10, 2)->change();
        });
    }
};