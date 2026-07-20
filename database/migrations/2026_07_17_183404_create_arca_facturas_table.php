<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arca_facturas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('invoiceId');

            $table->string('cae', 14);
            $table->string('fecha_vto', 8);      // AAAAMMDD
            $table->string('fecha_cbte', 8);     // AAAAMMDD

            $table->unsignedInteger('punto_vta');
            $table->unsignedSmallInteger('tipo_cbte');
            $table->unsignedBigInteger('cbt_desde');

            $table->decimal('imp_total', 15, 2);

            $table->char('resultado', 1);        // A = aprobado / R = rechazado

            $table->timestamps();

            $table->foreign('invoiceId')->references('id')->on('invoices');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arca_facturas');
    }
};