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
    DB::statement('ALTER TABLE drivers MODIFY dni VARCHAR(255) NULL');
    DB::statement('ALTER TABLE drivers MODIFY address VARCHAR(255) NULL');
    DB::statement('ALTER TABLE drivers MODIFY city VARCHAR(255) NULL');
    DB::statement('ALTER TABLE drivers MODIFY phone VARCHAR(255) NULL');
    DB::statement('ALTER TABLE drivers MODIFY vehicleId BIGINT UNSIGNED NULL');
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
  public function down()
{
    DB::statement('ALTER TABLE drivers MODIFY dni VARCHAR(255) NOT NULL');
    DB::statement('ALTER TABLE drivers MODIFY address VARCHAR(255) NOT NULL');
    DB::statement('ALTER TABLE drivers MODIFY city VARCHAR(255) NOT NULL');
    DB::statement('ALTER TABLE drivers MODIFY phone VARCHAR(255) NOT NULL');
    DB::statement('ALTER TABLE drivers MODIFY vehicleId BIGINT UNSIGNED NOT NULL');
}
};
