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
    public function up(): void
    {
        Schema::create('invoice_receipt_taxes', function (Blueprint $table) {
            $table->id();

            // Relaciones lógicas (no se aplica FK todavía)
            $table->unsignedBigInteger('invoice_receipt_id');
            $table->unsignedBigInteger('tax_id');

            $table->decimal('taxAmount', 65, 2);

            $table->timestamps();

            // Si más adelante querés agregar la FK real, se podrá agregar con otra migración
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_receipt_taxes');
    }
};
