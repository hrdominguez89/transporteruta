<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE travel_certificates 
            MODIFY COLUMN total DECIMAL(15,2) NOT NULL,
            MODIFY COLUMN iva DECIMAL(15,2) NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE travel_certificates 
            MODIFY COLUMN total DECIMAL(8,2) NOT NULL,
            MODIFY COLUMN iva DECIMAL(8,2) NOT NULL
        ');
    }
};