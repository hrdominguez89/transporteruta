<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
{
    // 1. Agregar columna
    Schema::table('vehicles', function (Blueprint $table) {
        $table->unsignedBigInteger('driverId')->nullable()->after('id');
    });

    // 2. Copiar relaciones
    DB::statement('
        UPDATE vehicles v
        INNER JOIN drivers d ON d.vehicleId = v.id
        SET v.driverId = d.id
    ');

    // 3. FK con raw SQL para evitar el lowercase bug de Laravel
    DB::statement('
        ALTER TABLE vehicles 
        ADD CONSTRAINT vehicles_driverId_foreign 
        FOREIGN KEY (driverId) REFERENCES drivers(id) 
        ON DELETE SET NULL
    ');

    // 4. Sacar vehicleId de drivers
    Schema::table('drivers', function (Blueprint $table) {
        $table->dropColumn('vehicleId');
    });
}

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->unsignedBigInteger('vehicleId')->nullable()->after('id');
        });

      DB::statement('ALTER TABLE vehicles DROP FOREIGN KEY vehicles_driverId_foreign');

        // down() - antes de dropColumn('driverId')
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['driverId']);
        });
    }
};