<?php

use App\Models\TravelCertificate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE travel_certificates MODIFY number INT(10) NULL');
        DB::statement('ALTER TABLE driver_settlements MODIFY number INT(10) NULL');
        Schema::table('travel_certificates', function (Blueprint $table) {
            // Agregar columna porcentaje (decimal) que puede ser nulo
            $table->decimal('percent', 5, 2)->nullable()->after('invoiceId');  // 5 total digits, 2 decimals

            // Agregar columna monto fijo (decimal)
            $table->decimal('fixed_amount', 10, 2)->nullable()->after('percent');  // 10 total digits, 2 decimals

            // Renombrar columna tipo a commission_type (enum en español con valor por defecto)
            $table->enum('commission_type', ['porcentaje', 'monto fijo'])->default('porcentaje')->after('fixed_amount');
        });

        // Ejecutar la consulta directa para actualizar el porcentaje
        DB::statement("
            UPDATE travel_certificates AS tc
            JOIN drivers AS d ON tc.driverId = d.id
            SET tc.percent = d.percent
            WHERE tc.percent IS NULL;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
{
    // Restaurar las columnas 'number' en las tablas 'travel_certificates' y 'driver_settlements' a no nulas
    DB::statement('ALTER TABLE travel_certificates MODIFY number INT(10) NOT NULL');
    DB::statement('ALTER TABLE driver_settlements MODIFY number INT(10) NOT NULL');

    // Eliminar las columnas 'percent', 'fixed_amount' y 'commission_type'
    Schema::table('travel_certificates', function (Blueprint $table) {
        $table->dropColumn(['percent', 'fixed_amount', 'commission_type']);
    });
}
};
