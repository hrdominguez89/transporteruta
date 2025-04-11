<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->decimal('percent', 5, 2)->nullable()->after('percent_old');
        });

        // Copiar los valores de percent_old a percent con conversión a decimal
        DB::statement('UPDATE drivers SET percent = CAST(percent_old AS DECIMAL(5,2))');

        // Opcionalmente, puedes eliminar percent_old después de la copia
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('percent_old');
        });
    }

    public function down()
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->integer('percent_old')->nullable()->after('percent');
        });

        // Restaurar los valores originales
        DB::statement('UPDATE drivers SET percent_old = CAST(percent AS SIGNED)');

        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('percent');
        });
    }
};
