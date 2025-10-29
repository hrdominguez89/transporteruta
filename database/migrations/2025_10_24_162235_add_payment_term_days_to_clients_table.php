<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'paymentTermDays')) {
                // sin "after(...)" para evitar errores según el esquema
                $table->integer('paymentTermDays')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'paymentTermDays')) {
                $table->dropColumn('paymentTermDays');
            }
        });
    }
};

