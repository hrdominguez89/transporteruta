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
        DB::statement("ALTER TABLE cargas MODIFY estado_de_envio ENUM('DEPOSITO', 'COORDINADO', 'TRANSITO', 'ENTREGADO', 'RECHAZADO', 'AVISADO') NOT NULL DEFAULT 'DEPOSITO'");
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
