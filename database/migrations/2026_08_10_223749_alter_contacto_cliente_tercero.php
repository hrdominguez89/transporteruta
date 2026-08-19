<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contactos', function (Blueprint $table) {
            $table->foreignId('cliente_tercero_id')
                ->nullable()
                ->constrained('cliente_terceros')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contactos', function (Blueprint $table) {
            $table->dropForeign(['cliente_tercero_id']);
            $table->dropColumn('cliente_tercero_id');
        });
    }
};