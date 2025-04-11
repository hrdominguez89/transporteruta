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
        if (Schema::hasColumn('drivers', 'percent')) {
            DB::statement('ALTER TABLE drivers CHANGE percent percent_old INT(11)');
        }
    }

    public function down()
    {
        if (Schema::hasColumn('drivers', 'percent_old')) {
            DB::statement('ALTER TABLE drivers CHANGE percent_old percent INT(11)');
        }
    }
};
