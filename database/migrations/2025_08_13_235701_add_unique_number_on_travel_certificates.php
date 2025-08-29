<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Si tu MySQL permite múltiples NULL en índices únicos (sí),
        // esto hará que solo los números NO nulos sean únicos.
        Schema::table('travel_certificates', function (Blueprint $table) {
            $table->unique(['number'], 'tc_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('travel_certificates', function (Blueprint $table) {
            $table->dropUnique('tc_number_unique');
        });
    }
};
