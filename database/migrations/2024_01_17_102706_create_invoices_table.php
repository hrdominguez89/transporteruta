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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->integer('number');
            $table->date('date');
            $table->decimal('total', 65, 2);
            $table->decimal('iva', 65, 2);
            $table->decimal('totalWithIva', 65, 2);
            $table->enum('invoiced', ['SI', 'NO'])->default('NO');
            $table->enum('paid', ['SI', 'NO'])->default('NO');
            $table->decimal('balance', 65, 2);
            $table->foreignId('clientId')->references('id')->on('clients')->onDelete('CASCADE');
            $table->foreignId('receiptId')->references('id')->on('receipts')->onDelete('CASCADE');
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
        Schema::dropIfExists('invoices');
    }
};
