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
        Schema::create('travel_certificates', function (Blueprint $table) {
            $table->id();
            $table->integer('number');
            $table->decimal('total', 65, 2)->default(0.00);
            $table->decimal('iva')->default(0.00);
            $table->decimal('driverPayment')->default(0);
            $table->date('date');
            $table->string('destiny');
            $table->enum('invoiced', ['SI', 'NO'])->default('NO');
            $table->enum('isPaidToDriver', ['SI', 'NO'])->default('NO');
            $table->foreignId('clientId')->references('id')->on('clients')->onDelete('CASCADE');
            $table->foreignId('driverId')->references('id')->on('drivers')->onDelete('CASCADE');
            $table->foreignId('invoiceId')->references('id')->on('invoices')->onDelete('CASCADE')->nullable();
            $table->foreignId('driverSettlementId')->references('id')->on('driver_settlements')->onDelete('CASCADE');
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
        Schema::dropIfExists('travel_certificates');
    }
};
