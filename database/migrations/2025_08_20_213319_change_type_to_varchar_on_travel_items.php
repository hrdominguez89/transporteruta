<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Cambiamos de ENUM a VARCHAR(20)
        DB::statement("ALTER TABLE travel_items MODIFY COLUMN type VARCHAR(20) NOT NULL");
    }

    public function down(): void
    {
        // Si querés volver a ENUM (incluyendo REMITO)
        DB::statement("
            ALTER TABLE travel_items
            MODIFY COLUMN type ENUM(
                'HORA','KILOMETRO','PEAJE','ADICIONAL','FIJO',
                'MULTIDESTINO','DESCARGA','DESCUENTO','REMITO'
            ) NOT NULL
        ");
    }
};
