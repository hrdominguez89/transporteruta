<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
   public function up(): void
{
    // Ejecutar solo si existe la tabla (en local podría no existir)
    if (Schema::hasTable('invoice_receipt')) {

        // 1) Si existe taxId: primero intentamos dropear la FK (si la hubiera) y luego la columna
        if (Schema::hasColumn('invoice_receipt', 'taxId')) {
            // dropear FK por nombre de columna, envuelto en try/catch por si no existe
            Schema::table('invoice_receipt', function (Blueprint $table) {
                try { $table->dropForeign(['taxId']); } catch (\Throwable $e) {}
            });

            // dropear la columna taxId (solo si sigue existiendo)
            if (Schema::hasColumn('invoice_receipt', 'taxId')) {
                Schema::table('invoice_receipt', function (Blueprint $table) {
                    $table->dropColumn('taxId');
                });
            }
        }

        // 2) Modificar taxAmount (solo si la columna existe)
        if (Schema::hasColumn('invoice_receipt', 'taxAmount')) {
            DB::statement('ALTER TABLE invoice_receipt MODIFY taxAmount DECIMAL(65,2) NOT NULL DEFAULT 0.00');
        }
    }
}

public function down(): void
{
    if (Schema::hasTable('invoice_receipt')) {

        // Restaurar taxId solo si no existe
        if (!Schema::hasColumn('invoice_receipt', 'taxId')) {
            Schema::table('invoice_receipt', function (Blueprint $table) {
                $table->unsignedBigInteger('taxId')->nullable();
            });

            // (Opcional) restaurar FK a taxes si aplica tu modelo de datos
            Schema::table('invoice_receipt', function (Blueprint $table) {
                try { $table->foreign('taxId')->references('id')->on('taxes')->nullOnDelete(); } catch (\Throwable $e) {}
            });
        }

        // Revertir el cambio de taxAmount si existe
        if (Schema::hasColumn('invoice_receipt', 'taxAmount')) {
            DB::statement('ALTER TABLE invoice_receipt MODIFY taxAmount DECIMAL(65,2) NOT NULL');
        }
    }
}

};
