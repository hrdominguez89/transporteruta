<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // A) Asegurá que exista point_of_sale (por si en otra base no existiera)
        if (! Schema::hasColumn('invoices', 'point_of_sale')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->unsignedSmallInteger('point_of_sale')->nullable()->after('id');
            });
        }

        // B) Copiá datos de pointOfSale -> point_of_sale si hace falta
        if (Schema::hasColumn('invoices', 'pointOfSale')) {
            DB::statement("
                UPDATE invoices
                SET point_of_sale = pointOfSale
                WHERE pointOfSale IS NOT NULL
                  AND (point_of_sale IS NULL OR point_of_sale = 0)
            ");
        }

        // C) Dejá point_of_sale como NOT NULL
        DB::statement("
            ALTER TABLE invoices
            MODIFY point_of_sale SMALLINT UNSIGNED NOT NULL
        ");

        // D) Eliminá la columna legacy camelCase
        if (Schema::hasColumn('invoices', 'pointOfSale')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('pointOfSale');
            });
        }
    }

    public function down(): void
    {
        // Volvemos a crear pointOfSale por reversibilidad (nullable)
        if (! Schema::hasColumn('invoices', 'pointOfSale')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->unsignedSmallInteger('pointOfSale')->nullable()->after('point_of_sale');
            });
        }

        // Copiamos los datos actuales a la columna legacy
        DB::statement("
            UPDATE invoices
            SET pointOfSale = point_of_sale
            WHERE point_of_sale IS NOT NULL
        ");
    }
};
