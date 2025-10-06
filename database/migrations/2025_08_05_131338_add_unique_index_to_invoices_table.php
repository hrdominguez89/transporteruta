<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Índice único por número + punto de venta
            $table->unique(['number', 'pointOfSale'], 'unique_invoice_number_point_of_sale');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique('unique_invoice_number_point_of_sale');
        });
    }
};
