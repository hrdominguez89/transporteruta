<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cargas', function (Blueprint $table) {
            $table->unsignedBigInteger('travel_certificate_id')->nullable();
            $table->foreign('travel_certificate_id')
                  ->references('id')->on('travel_certificates')
                  ->nullOnDelete();

            $table->unsignedBigInteger('cliente_tercero_id')->nullable();
            $table->foreign('cliente_tercero_id')
                  ->references('id')->on('cliente_terceros')
                  ->nullOnDelete();

            $table->boolean('liquidado')->default(false);
            $table->string('motivo')->nullable();
        });
    }

    public function down()
    {
        Schema::table('cargas', function (Blueprint $table) {
            $table->dropForeign(['travel_certificate_id']);
            $table->dropForeign(['cliente_tercero_id']);
            $table->dropColumn([
                'travel_certificate_id',
                'cliente_tercero_id',
                'liquidado',
                'motivo',
            ]);
        });
    }
};