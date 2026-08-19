<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estado_envios', function (Blueprint $table) {
            $table->enum('estado', ['DEPOSITO', 'COORDINADO', 'TRANSITO', 'ENTREGADO', 'RECHAZADO', 'AVISADO'])->default('DEPOSITO'); 
            $table->id();
            $table->foreignId('carga_id')->constrained('cargas')->cascadeOnDelete();
            $table->string('label');
            $table->date('horario');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estado_envios');
    }
};