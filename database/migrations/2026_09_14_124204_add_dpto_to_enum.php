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
        DB::statement("ALTER TABLE contacto_categorias MODIFY categoria ENUM ('Cobros y Pagos',
              'administracion',
              'proveedores',
              'oficina',
              'contable',
              'compras',
              'ventas',
              'comercio exterior') NULL DEFAULT 'Cobros y Pagos' ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       
    }
};
