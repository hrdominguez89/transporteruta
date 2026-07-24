<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cargas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('cantidad');
            $table->date('fecha_de_recepcion');
            $table->date('fecha_de_entrega')->nullable();
            $table->decimal('precio', 12, 2)->nullable();
            $table->string('espacio')->nullable();
            $table->enum('tipo', ['PALLET', 'BULTO']);
            $table->string('destino')->nullable();

            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('remito_id')->nullable();

            $table->enum('estado_de_envio', ['ALMACEN', 'VIAJE', 'ENTREGADO', 'RECHAZADO'])
                  ->default('ALMACEN');

            $table->boolean('notificacion_de_recepcion')->default(false);
            $table->boolean('notificacion_de_entrega')->default(false);

            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('restrict');
            // $table->foreign('remito_id')->references('id')->on('remitos')->onDelete('set null');

            $table->index('estado_de_envio');
            $table->index('fecha_de_entrega');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cargas');
    }
};