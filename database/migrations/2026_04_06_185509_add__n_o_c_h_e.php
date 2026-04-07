<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE travel_items
            MODIFY COLUMN type ENUM(
                'HORA','KILOMETRO','PEAJE','FIJO','DESCARGA','MULTIDESTINO','ADICIONAL','DESCUENTO','REMITO','ESTACIONAMIENTO','PALLET','BULTO','ESTADIA','NOCHE'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
         DB::statement("
            ALTER TABLE travel_items
            MODIFY COLUMN type ENUM(
                'HORA','KILOMETRO','PEAJE','FIJO','DESCARGA','MULTIDESTINO','ADICIONAL','DESCUENTO','REMITO','ESTACIONAMIENTO','PALLET','BULTO','ESTADIA'
            ) NOT NULL
        ");
    }
};
