<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // --- 0) Dropear TODAS las FKs que referencian estas columnas, sin asumir nombres
        $constraints = DB::select("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'travel_certificates'
              AND COLUMN_NAME IN ('invoiceId','driverSettlementId')
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        foreach ($constraints as $c) {
            try {
                DB::statement("ALTER TABLE `travel_certificates` DROP FOREIGN KEY `{$c->CONSTRAINT_NAME}`");
            } catch (\Throwable $e) {
                // ignoramos si no existe
            }
        }

        // --- 1) Convertir 0 -> NULL antes de alterar (evita NOT NULL al cambiar)
        DB::statement("UPDATE `travel_certificates` SET `invoiceId` = NULL WHERE `invoiceId` = 0");
        DB::statement("UPDATE `travel_certificates` SET `driverSettlementId` = NULL WHERE `driverSettlementId` = 0");

        // --- 2) Detectar tipo real de las columnas (INT o BIGINT) para usarlo en el MODIFY
        $invoiceCol = DB::selectOne("SHOW COLUMNS FROM `travel_certificates` LIKE 'invoiceId'");
        $settleCol  = DB::selectOne("SHOW COLUMNS FROM `travel_certificates` LIKE 'driverSettlementId'");

        $invoiceType = isset($invoiceCol->Type) && str_contains(strtolower($invoiceCol->Type), 'int')
            ? (str_contains(strtolower($invoiceCol->Type), 'bigint') ? 'BIGINT UNSIGNED' : 'INT UNSIGNED')
            : 'BIGINT UNSIGNED';

        $settleType = isset($settleCol->Type) && str_contains(strtolower($settleCol->Type), 'int')
            ? (str_contains(strtolower($settleCol->Type), 'bigint') ? 'BIGINT UNSIGNED' : 'INT UNSIGNED')
            : 'BIGINT UNSIGNED';

        // --- 3) Hacer columnas NULLABLE con el tipo correcto
        DB::statement("ALTER TABLE `travel_certificates` MODIFY `invoiceId` {$invoiceType} NULL");
        DB::statement("ALTER TABLE `travel_certificates` MODIFY `driverSettlementId` {$settleType} NULL");

        // --- 4) Recrear FKs con ON DELETE SET NULL
        Schema::table('travel_certificates', function (Blueprint $t) {
            $t->foreign('invoiceId')
              ->references('id')->on('invoices')
              ->nullOnDelete();

            $t->foreign('driverSettlementId')
              ->references('id')->on('driver_settlements')
              ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Dropear FKs creadas
        $constraints = DB::select("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'travel_certificates'
              AND COLUMN_NAME IN ('invoiceId','driverSettlementId')
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        foreach ($constraints as $c) {
            try {
                DB::statement("ALTER TABLE `travel_certificates` DROP FOREIGN KEY `{$c->CONSTRAINT_NAME}`");
            } catch (\Throwable $e) {
                // ignoramos si no existe
            }
        }

        // Volver NULL -> 0 (simetría; no recomendado, pero deja el down consistente)
        DB::statement("UPDATE `travel_certificates` SET `invoiceId` = 0 WHERE `invoiceId` IS NULL");
        DB::statement("UPDATE `travel_certificates` SET `driverSettlementId` = 0 WHERE `driverSettlementId` IS NULL");

        // Detectar tipos para revertir
        $invoiceCol = DB::selectOne("SHOW COLUMNS FROM `travel_certificates` LIKE 'invoiceId'");
        $settleCol  = DB::selectOne("SHOW COLUMNS FROM `travel_certificates` LIKE 'driverSettlementId'");

        $invoiceType = isset($invoiceCol->Type) && str_contains(strtolower($invoiceCol->Type), 'int')
            ? (str_contains(strtolower($invoiceCol->Type), 'bigint') ? 'BIGINT UNSIGNED' : 'INT UNSIGNED')
            : 'BIGINT UNSIGNED';

        $settleType = isset($settleCol->Type) && str_contains(strtolower($settleCol->Type), 'int')
            ? (str_contains(strtolower($settleCol->Type), 'bigint') ? 'BIGINT UNSIGNED' : 'INT UNSIGNED')
            : 'BIGINT UNSIGNED';

        DB::statement("ALTER TABLE `travel_certificates` MODIFY `invoiceId` {$invoiceType} NOT NULL DEFAULT 0");
        DB::statement("ALTER TABLE `travel_certificates` MODIFY `driverSettlementId` {$settleType} NOT NULL DEFAULT 0");

        // Recrear FKs con CASCADE (como suele venir de fábrica)
        Schema::table('travel_certificates', function (Blueprint $t) {
            $t->foreign('invoiceId')
              ->references('id')->on('invoices')
              ->cascadeOnDelete();

            $t->foreign('driverSettlementId')
              ->references('id')->on('driver_settlements')
              ->cascadeOnDelete();
        });
    }
};
