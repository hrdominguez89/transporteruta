<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Columna remito_number (si no existiese)
        if (!Schema::hasColumn('travel_items', 'remito_number')) {
            Schema::table('travel_items', function (Blueprint $table) {
                $table->string('remito_number', 50)->nullable()->after('description');
                // si queremos consultas rápidas por remito
                $table->index('remito_number', 'idx_travel_items_remito_number');
            });
        }

        // 2) Índice único por constancia+remito (evita duplicados en la misma constancia)
            Schema::table('travel_items', function (Blueprint $table) {
            try {
                $table->unique(['travelCertificateId', 'remito_number'], 'uniq_remito_por_constancia');
            } catch (\Throwable $e) {
                // Silencioso si ya existe o si hay duplicados a limpiar.
            }
        });
    }

    public function down(): void
    {
        Schema::table('travel_items', function (Blueprint $table) {
            // Borrar el índice único si existiera
            try { $table->dropUnique('uniq_remito_por_constancia'); } catch (\Throwable $e) {}

            // Borrar el índice simple y la columna si existieran
            try { $table->dropIndex('idx_travel_items_remito_number'); } catch (\Throwable $e) {}
            if (Schema::hasColumn('travel_items', 'remito_number')) {
                $table->dropColumn('remito_number');
            }
        });
    }
};
