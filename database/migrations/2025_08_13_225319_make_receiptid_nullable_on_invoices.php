<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Dropeo FK (nombre usual de Laravel)
        DB::statement('ALTER TABLE `invoices` DROP FOREIGN KEY `invoices_receiptid_foreign`');

        // 2) Hacer la columna nullable
        DB::statement('ALTER TABLE `invoices` MODIFY `receiptId` BIGINT UNSIGNED NULL');

        // 3) Convertir posibles 0 → NULL (por si hubiera)
        DB::statement('UPDATE `invoices` SET `receiptId` = NULL WHERE `receiptId` = 0');

        // 4) Re-crear FK con ON DELETE SET NULL (requiere nullable)
        DB::statement('ALTER TABLE `invoices` 
            ADD CONSTRAINT `invoices_receiptid_foreign` 
            FOREIGN KEY (`receiptId`) REFERENCES `receipts`(`id`) 
            ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down(): void
    {
        // Volver al estado anterior (solo si realmente lo necesitás)
        // Nota: Esto puede fallar si existen NULLs y no querés forzarlos a un id válido.
        DB::statement('ALTER TABLE `invoices` DROP FOREIGN KEY `invoices_receiptid_foreign`');

        // Si insistís en volver a NOT NULL, primero tendrás que decidir qué hacer con los NULLs.
        // Ejemplo (no recomendado en prod): forzar NULLs a un id válido existente.
        // DB::statement('UPDATE `invoices` SET `receiptId` = 1 WHERE `receiptId` IS NULL');

        DB::statement('ALTER TABLE `invoices` MODIFY `receiptId` BIGINT UNSIGNED NOT NULL');

        DB::statement('ALTER TABLE `invoices` 
            ADD CONSTRAINT `invoices_receiptid_foreign` 
            FOREIGN KEY (`receiptId`) REFERENCES `receipts`(`id`) 
            ON DELETE CASCADE ON UPDATE CASCADE');
    }
};
