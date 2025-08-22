<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up(): void
{
    // 1) Normalizar datos antes de convertir
    // Reemplazar comas por punto (si el campo es string / hay comas)
    DB::statement("UPDATE travel_certificates SET percent = REPLACE(percent, ',', '.') WHERE percent LIKE '%,%'");

    // Vacíos -> NULL
    DB::statement("UPDATE travel_certificates SET percent = NULL WHERE TRIM(COALESCE(percent,'')) = ''");

    // Cualquier valor NO numérico (regex) -> NULL
    DB::statement("UPDATE travel_certificates SET percent = NULL WHERE percent IS NOT NULL AND percent NOT REGEXP '^[0-9]+(\\.[0-9]+)?$'");

    // 2) Convertir a DECIMAL de forma tolerante (permitimos NULL primero)
    DB::statement("ALTER TABLE travel_certificates MODIFY percent DECIMAL(10,4) NULL");

    // 3) Setear default para nulos
    DB::statement("UPDATE travel_certificates SET percent = 0 WHERE percent IS NULL");

    // 4) Endurecer a NOT NULL
    DB::statement("ALTER TABLE travel_certificates MODIFY percent DECIMAL(10,4) NOT NULL");
}

public function down(): void
{
    DB::statement("ALTER TABLE travel_certificates MODIFY percent VARCHAR(255) NULL");
}
};
