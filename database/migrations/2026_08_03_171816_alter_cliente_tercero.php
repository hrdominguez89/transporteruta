<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
    {
        Schema::table('cliente_terceros', function (Blueprint $table) {
            $table->string('horario_entrega')->nullable();
            $table->string('cuit')->nullable();
            $table->string('condicion_venta')->nullable();
            $table->string('numero_cliente')->nullable();
        });
    }

    public function down()
    {
        Schema::table('cliente_terceros', function (Blueprint $table) {
            $table->dropColumn(['horario_entrega', 'cuit', 'condicion_venta', 'numero_cliente']);
        });
    }
};
