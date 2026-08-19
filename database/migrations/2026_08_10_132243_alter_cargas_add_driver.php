<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up(): void
    {
        Schema::table('cargas', function (Blueprint $table) {
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cargas', function (Blueprint $table) {
            $table->dropColumn([
                'driver_id'
            ]);
        });
    }
};
