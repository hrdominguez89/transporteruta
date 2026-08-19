<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::table('cargas', function (Blueprint $table) {
            $table->decimal('pallet_costo', 12, 2)->default(0);
            $table->decimal('bulto_costo', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('cargas', function (Blueprint $table) {
            $table->dropColumn(['pallet_costo', 'bulto_costo']);
        });
    }
};
