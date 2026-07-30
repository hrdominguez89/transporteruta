<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::table('cargas', function (Blueprint $table) {
            $table->dropColumn('estado_de_envio');
        });
        Schema::table('cargas', function (Blueprint $table) {
            $table->enum('estado_de_envio', ['DEPOSITO', 'COORDINADO', 'TRANSITO','ENTREGADO', 'RECHAZADO'])
                  ->default('DEPOSITO');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('cargas', function (Blueprint $table) {
            $table->enum('estado_de_envio', ['ALMACEN', 'VIAJE', 'ENTREGADO', 'RECHAZADO'])
                  ->default('ALMACEN');
        });
    }
};
