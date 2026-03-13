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
        Schema::create('payment_recipe_pivot',function(Blueprint $table){
            $table->foreignId('paymentId')->references('id')->on('payments');
            $table->foreignId('recipeId')->references('id')->on('receipts');
            $table->double('total',16,2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
