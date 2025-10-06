<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Hacer travel_certificates.invoiceId NULLABLE
     * sin requerir doctrine/dbal (usamos SQL crudo).
     * - Si existe FK sobre invoiceId, lo eliminamos primero.
     * - Convertimos la columna a NULL.
     * - Re-creamos FK con ON DELETE SET NULL (si existe invoices).
     */
    public function up(): void
    {
        if (!Schema::hasTable('travel_certificates') || !Schema::hasColumn('travel_certificates', 'invoiceId')) {
            return;
        }

        // 1) Buscar nombre real del FK (si lo hay) en information_schema
        $fkName = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'travel_certificates')
            ->where('COLUMN_NAME', 'invoiceId')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->value('CONSTRAINT_NAME');

        // 2) Dropear el FK si existe
        if ($fkName) {
            try {
                DB::statement("ALTER TABLE `travel_certificates` DROP FOREIGN KEY `{$fkName}`");
            } catch (\Throwable $e) {
                // ignoramos; si no existe o ya se dropeó, seguimos
            }
        }

        // 3) Detectar el tipo exacto de la columna para no romper (BIGINT UNSIGNED, etc.)
        $columnType = DB::table('information_schema.COLUMNS')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'travel_certificates')
            ->where('COLUMN_NAME', 'invoiceId')
            ->value('COLUMN_TYPE') ?? 'bigint unsigned';

        // 4) Volverla NULL
        DB::statement("ALTER TABLE `travel_certificates` MODIFY `invoiceId` {$columnType} NULL");

        // 5) Re-crear FK con ON DELETE SET NULL (si la tabla invoices existe)
        if (Schema::hasTable('invoices')) {
            try {
                DB::statement("ALTER TABLE `travel_certificates`
                    ADD CONSTRAINT `tc_invoiceId_fk`
                    FOREIGN KEY (`invoiceId`) REFERENCES `invoices`(`id`)
                    ON DELETE SET NULL");
            } catch (\Throwable $e) {
                // si falla por nombre duplicado u otro motivo, no interrumpimos el deploy
            }
        }
    }

    /**
     * Revertir: volver invoiceId NOT NULL.
     * Nota: para que no falle al convertir a NOT NULL, cualquier NULL se pasa a 0.
     * No re-creamos el FK viejo porque 0 no referenciaría a invoices.id.
     */
    public function down(): void
    {
        if (!Schema::hasTable('travel_certificates') || !Schema::hasColumn('travel_certificates', 'invoiceId')) {
            return;
        }

        // Intentar dropear el FK que agregamos en up()
        try {
            DB::statement("ALTER TABLE `travel_certificates` DROP FOREIGN KEY `tc_invoiceId_fk`");
        } catch (\Throwable $e) {
            // ignoramos
        }

        $columnType = DB::table('information_schema.COLUMNS')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'travel_certificates')
            ->where('COLUMN_NAME', 'invoiceId')
            ->value('COLUMN_TYPE') ?? 'bigint unsigned';

        // Asegurar que no queden NULL antes de NOT NULL
        DB::table('travel_certificates')->whereNull('invoiceId')->update(['invoiceId' => 0]);

        DB::statement("ALTER TABLE `travel_certificates` MODIFY `invoiceId` {$columnType} NOT NULL");

        // (Opcional) acá podrías re-crear un FK "estricto" si garantizas que no hay 0s.
    }
};
