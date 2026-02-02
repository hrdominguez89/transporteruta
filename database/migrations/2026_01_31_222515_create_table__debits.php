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
        Schema::create('debits', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('clientId')->nullable()->references('id')->on('clients');
            $table->foreignId('invoiceId')->nullable()->references('id')->on('invoices');
            $table->string('referenceNumber')->nullable();
            $table->string('reason')->nullable();
            $table->date('emission_date');
            $table->decimal('balance', 15, 2);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('debits');
    }
};
