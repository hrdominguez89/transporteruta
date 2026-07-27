<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cliente_terceros', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();

            $table->unsignedBigInteger('contacto_id')->nullable();

            $table->string('nombre');
            $table->string('codigo_postal', 20)->nullable();
            $table->string('direccion')->nullable();

            $table->timestamps();
            $table->foreign('contacto_id')->references('id')->on('contactos')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cliente_terceros');
    }
};