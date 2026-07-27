<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('remitos', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->nullable();
            $table->unsignedBigInteger('client_id');
            $table->string('path')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('restrict');
            $table->index('numero');
        });
    }

    public function down()
    {
        Schema::dropIfExists('remitos');
    }
};