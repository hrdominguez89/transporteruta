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
        Schema::create('travel_items', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['HORA','KILOMETRO', 'PEAJE', 'FIJO', 'DESCARGA', 'MULTIDESTINO', 'ADICIONAL']);
            $table->decimal('price', 65, 2);
            $table->string('departureTime')->nullable();
            $table->string('arrivalTime')->nullable();
            $table->string('totalTime')->nullable();
            $table->string('distance')->nullable();
            $table->foreignId('travelCertificateId')->references('id')->on('travel_certificates')->onDelete('CASCADE');
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
        Schema::dropIfExists('travel_items');
    }
};
