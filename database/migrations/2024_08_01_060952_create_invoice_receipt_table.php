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
        Schema::create('invoice_receipt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('CASCADE');
            $table->foreignId('receipt_id')->constrained()->onDelete('CASCADE');
            $table->decimal('total', 65, 2);
            $table->foreignId('paymentMethodId')->references('id')->on('payment_methods')->onDelete('CASCADE');
            $table->foreignId('taxId')->references('id')->on('taxes')->onDelete('CASCADE');
            $table->decimal('taxAmount', 65, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_receipt');
    }
};
