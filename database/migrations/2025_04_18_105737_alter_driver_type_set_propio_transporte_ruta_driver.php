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
        DB::statement("UPDATE drivers SET type = 'PROPIO' , percent = 100 WHERE `drivers`.`name` like '%T. RUTA%'");
        DB::statement("
            UPDATE travel_certificates AS tc
            JOIN drivers AS d ON tc.driverId = d.id
            SET tc.percent = d.percent
            WHERE tc.commission_type = 'porcentaje pactado';
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
