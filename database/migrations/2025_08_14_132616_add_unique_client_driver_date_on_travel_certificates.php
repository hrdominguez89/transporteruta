<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('travel_certificates', function (Blueprint $table) {
            // evita duplicados exactos de cliente + chofer + fecha
            $table->unique(
                ['clientId','driverId','date'],
                'tc_client_driver_date_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('travel_certificates', function (Blueprint $table) {
            $table->dropUnique('tc_client_driver_date_unique');
        });
    }
};