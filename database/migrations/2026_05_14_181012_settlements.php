<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->nullable();
            $table->foreignId('travel_certificate_id')
                ->nullable()
                ->constrained('travel_certificates')
                ->nullOnDelete();
            $table->foreignId('cliente_id')
                ->nullable()
                ->constrained('clients')
                ->nullOnDelete();
            $table->decimal('chofer_porcentaje', 5, 2)->nullable();
            $table->decimal('importe_neto', 12, 2)->nullable();
            $table->decimal('base_recaudacion', 12, 2)->nullable();
            $table->decimal('peajes', 12, 2)->nullable();
            $table->decimal('estacionamiento', 12, 2)->nullable();
            $table->decimal('chofer_total', 12, 2)->nullable();
            $table->decimal('carga_descarga_b', 12, 2)->nullable();
            $table->decimal('carga_descarga_n', 12, 2)->nullable();
            $table->decimal('noche_b', 12, 2)->nullable();
            $table->decimal('noche_n', 12, 2)->nullable();
            $table->decimal('chofer_cd_n', 12, 2)->nullable();
            $table->decimal('chofer_n_n', 12, 2)->nullable();
            $table->decimal('base_recaudacion_n', 12, 2)->nullable();
            $table->decimal('chofer_n', 12, 2)->nullable();
            $table->decimal('diferencia', 12, 2)->nullable();
            $table->text('comentarios')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};