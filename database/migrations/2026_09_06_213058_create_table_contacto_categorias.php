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

        if (Schema::hasTable('contacto_categorias')) {
            return;
        }

        Schema::create('contacto_categorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contacto_id')->constrained('contactos')->cascadeOnDelete();
            $table->enum('categoria', ['Cobros y Pagos',
              'administracion',
              'proveedores',
              'oficina',
              'contable',
              'compras',
              'ventas']);
            $table->timestamps();

            $table->unique(['contacto_id', 'categoria']);
            
        });
    }

    public function down()
    {
        Schema::dropIfExists('contacto_categorias');
    }
};
