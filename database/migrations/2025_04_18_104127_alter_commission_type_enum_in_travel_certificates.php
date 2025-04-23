<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Agrega el nuevo valor "porcentaje pactado" al enum
        DB::statement("ALTER TABLE travel_certificates 
            MODIFY commission_type ENUM('porcentaje', 'monto fijo', 'porcentaje pactado') 
            DEFAULT 'porcentaje pactado'");

        DB::statement("
            UPDATE travel_certificates AS tc
            SET tc.commission_type = 'porcentaje pactado'
            WHERE tc.commission_type = 'porcentaje' AND tc.percent IS NOT NULL
            AND tc.fixed_amount IS NULL;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::statement("
            UPDATE travel_certificates AS tc
            SET tc.commission_type = 'porcentaje'
            WHERE tc.commission_type = 'porcentaje pactado' AND tc.percent IS NOT NULL
            AND tc.fixed_amount IS NULL;
        ");
        // Revertir a los valores originales
        DB::statement("ALTER TABLE travel_certificates 
            MODIFY commission_type ENUM('porcentaje', 'monto fijo') 
            DEFAULT 'porcentaje'");
    }
};
