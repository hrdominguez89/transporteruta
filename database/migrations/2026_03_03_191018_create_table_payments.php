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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->decimal('total',15,2)->nullable();
            $table->decimal('balance',15,2)->nullable();
            $table->foreignId('clientId')->nullable()->references('id')->on('clients');
            $table->dateTime('acreditation_date')->nullable();
            $table->string('method')->nullable();
            $table->string('cheq_type')->nullable();
            $table->string('banco')->nullable();
            $table->string('number')->nullable();
            $table->string('note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
