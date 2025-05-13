<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar taxId
        Schema::table('invoice_receipt', function ($table) {
            $table->dropColumn('taxId');
        });

        // Modificar taxAmount con SQL puro
        DB::statement('ALTER TABLE invoice_receipt MODIFY taxAmount DECIMAL(65,2) NOT NULL DEFAULT 0.00');
    }

    public function down(): void
    {
        // Restaurar taxId
        Schema::table('invoice_receipt', function ($table) {
            $table->unsignedBigInteger('taxId')->nullable();
        });

        // Revertir taxAmount sin default con SQL puro
        DB::statement('ALTER TABLE invoice_receipt MODIFY taxAmount DECIMAL(65,2) NOT NULL');
    }
};
