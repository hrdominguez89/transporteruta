<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        schema::table('travel_certificates',function(Blueprint $table)
        {
            $table->integer('vehicleId')->nullable();
            $table->dateTime('horaLLegada')->nullable();
            $table->dateTime('horaSalida')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        schema::table('travel_certificates',function(Blueprint $table)
        {
            $table->dropColumn(['vehicleId','horaLLegada','horaSalida']);
        });
    }
};
