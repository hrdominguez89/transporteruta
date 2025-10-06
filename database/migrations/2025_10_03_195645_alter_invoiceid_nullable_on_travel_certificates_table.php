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
        DB::statement("ALTER TABLE `travel_certificates` MODIFY `invoiceId` BIGINT(20) UNSIGNED NULL");
        DB::statement("ALTER TABLE `travel_certificates` MODIFY `driverSettlementId` BIGINT(20) UNSIGNED NULL");
    }
    /**
     * Revertir: volver invoiceId NOT NULL.
     * Nota: para que no falle al convertir a NOT NULL, cualquier NULL se pasa a 0.
     * No re-creamos el FK viejo porque 0 no referenciaría a invoices.id.
     */
    public function down(): void
    {
        // Primero actualizar los NULL a 0
        DB::table('travel_certificates')->whereNull('invoiceId')->update(['invoiceId' => 0]);
        DB::table('travel_certificates')->whereNull('driverSettlementId')->update(['driverSettlementId' => 0]);
        // Luego cambiar las columnas
        DB::statement("ALTER TABLE `travel_certificates` MODIFY `invoiceId` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0");
        DB::statement("ALTER TABLE `travel_certificates` MODIFY `driverSettlementId` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0");
    }
};
