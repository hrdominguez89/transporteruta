<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) Aseguramos que existan las columnas necesarias
        if (! Schema::hasColumn('invoices', 'point_of_sale')) {
            Schema::table('invoices', function (Blueprint $table) {
                // Ajustá el tipo si preferís otro; SMALLINT alcanza para Punto Venta
                $table->unsignedSmallInteger('point_of_sale')->after('id');
            });
        }

        if (! Schema::hasColumn('invoices', 'number')) {
            Schema::table('invoices', function (Blueprint $table) {
                // Si ya la tenías con otro tipo, omití este bloque
                $table->unsignedBigInteger('number')->after('point_of_sale');
            });
        }

        // 2) Creamos índice único compuesto PV+Número
        Schema::table('invoices', function (Blueprint $table) {
            // nombre explícito del índice para poder dropearlo fácil si hace falta
            $table->unique(['point_of_sale', 'number'], 'invoices_pv_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // quitamos el índice; NO borramos columnas por seguridad
            $table->dropUnique('invoices_pv_number_unique');
        });
    }
};

