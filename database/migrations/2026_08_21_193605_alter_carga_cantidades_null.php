<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE cargas MODIFY cantidad_pallet_normal INT UNSIGNED NULL');
        DB::statement('ALTER TABLE cargas MODIFY cantidad_bulto INT UNSIGNED NULL');
        DB::statement('ALTER TABLE cargas MODIFY cantidad_pallet_grande INT UNSIGNED NULL');
        DB::statement('ALTER TABLE cargas MODIFY rechazado_bulto INT UNSIGNED NULL');
        DB::statement('ALTER TABLE cargas MODIFY rechazado_pallet_normal INT UNSIGNED NULL');
        DB::statement('ALTER TABLE cargas MODIFY rechazado_pallet_grande INT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE cargas MODIFY cantidad_pallet_normal INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE cargas MODIFY cantidad_bulto INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE cargas MODIFY cantidad_pallet_grande INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE cargas MODIFY rechazado_bulto INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE cargas MODIFY rechazado_pallet_normal INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE cargas MODIFY rechazado_pallet_grande INT UNSIGNED NOT NULL');
    }
};