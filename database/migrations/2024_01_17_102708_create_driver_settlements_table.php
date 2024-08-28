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
        Schema::create('driver_settlements', function (Blueprint $table) {
            $table->id();
            $table->integer('number');
            $table->date('date');
            $table->decimal('total', 65, 2);
            $table->date('dateFrom');
            $table->date('dateTo');
            $table->enum('liquidated', ['SI', 'NO'])->default('NO');
            $table->foreignId('driverId')->references('id')->on('drivers')->onDelete('CASCADE');
            $table->foreignId('paymentMethodId')->references('id')->on('payment_methods')->onDelete('CASCADE');
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
        Schema::dropIfExists('driver_settlements');
    }
};
