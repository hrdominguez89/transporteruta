<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Schema::table('estado_envios', function (Blueprint $table) {
        //     $table->boolean('estado_actual')->default(false);
        // });
    }

    public function down(): void
    {
        Schema::table('estado_envios', function (Blueprint $table) {
            $table->dropColumn('estado_actual');
        });
    }
};