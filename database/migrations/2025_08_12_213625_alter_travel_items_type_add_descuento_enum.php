<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Agregamos DESCUENTO al ENUM
        DB::statement("
            ALTER TABLE `travel_items`
            MODIFY `type` ENUM(
                'HORA','KILOMETRO','PEAJE','ADICIONAL','FIJO','MULTIDESTINO','DESCARGA','DESCUENTO'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        // Si querés revertir: primero reasignamos posibles DESCUENTO a FIJO (para no romper al reducir el ENUM)
        DB::statement("
            UPDATE `travel_items` SET `type`='FIJO' WHERE `type`='DESCUENTO'
        ");

        DB::statement("
            ALTER TABLE `travel_items`
            MODIFY `type` ENUM(
                'HORA','KILOMETRO','PEAJE','ADICIONAL','FIJO','MULTIDESTINO','DESCARGA'
            ) NOT NULL
        ");
    }
};
