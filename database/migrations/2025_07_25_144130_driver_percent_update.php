<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up(): void
    {
        // Modificar el campo percent a DECIMAL(11,9)
        DB::statement('ALTER TABLE drivers MODIFY percent DECIMAL(12,9) NOT NULL');
        DB::statement('ALTER TABLE travel_certificates MODIFY percent DECIMAL(12,9) NOT NULL');
    }

    public function down(): void
    {
        // Revertir el cambio a DECIMAL(5,2)
        DB::statement('ALTER TABLE drivers MODIFY percent DECIMAL(5,2) NOT NULL');
        DB::statement('ALTER TABLE travel_certificates MODIFY percent DECIMAL(5,2) NOT NULL');
    }
};
