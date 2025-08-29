<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('travel_items', function (Blueprint $table) {
            $table->string('remito_number', 50)->nullable()->after('description');
            // evita duplicar el mismo remito dentro de una constancia
            $table->unique(['travelCertificateId', 'remito_number'], 'ti_tc_remito_unique');
        });
    }

    public function down(): void
    {
        Schema::table('travel_items', function (Blueprint $table) {
            $table->dropUnique('ti_tc_remito_unique');
            $table->dropColumn('remito_number');
        });
    }
};

