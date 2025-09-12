<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('travel_certificates', function (Blueprint $table) {
            $table->string('remito_number', 50)->nullable()->after('number');
            // si querés, también un índice para búsquedas:
            $table->index('remito_number', 'tc_remito_idx');
        });
    }

    public function down(): void
    {
        Schema::table('travel_certificates', function (Blueprint $table) {
            $table->dropIndex('tc_remito_idx');
            $table->dropColumn('remito_number');
        });
    }
};
