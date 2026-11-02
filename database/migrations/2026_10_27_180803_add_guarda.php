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
    public function up()
    {
         DB::statement("
            ALTER TABLE travel_items
            MODIFY COLUMN type ENUM(
                'HORA','KILOMETRO','PEAJE','FIJO','DESCARGA','MULTIDESTINO','ADICIONAL','DESCUENTO','REMITO','ESTACIONAMIENTO','PALLET','BULTO','ESTADIA','NOCHE','DEMORA','GUARDA'
            ) NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         DB::statement("
            ALTER TABLE travel_items
            MODIFY COLUMN type ENUM(
                'HORA','KILOMETRO','PEAJE','FIJO','DESCARGA','MULTIDESTINO','ADICIONAL','DESCUENTO','REMITO','ESTACIONAMIENTO','PALLET','BULTO','ESTADIA','NOCHE','DEMORA'
            ) NOT NULL
        ");
    }
};
