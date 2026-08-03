<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE contactos MODIFY categoria VARCHAR(255) NULL");
        DB::statement("ALTER TABLE contactos MODIFY apellido VARCHAR(255) NULL");
        DB::statement("ALTER TABLE contactos MODIFY mail VARCHAR(255) NULL");
        DB::statement("ALTER TABLE contactos MODIFY comentario TEXT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE contactos MODIFY categoria VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE contactos MODIFY apellido VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE contactos MODIFY mail VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE contactos MODIFY comentario TEXT NOT NULL");
    }
};
