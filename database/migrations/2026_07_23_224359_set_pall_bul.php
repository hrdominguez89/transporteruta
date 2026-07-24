<?php
// database/migrations/2026_07_23_100000_insert_default_prices.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::table('prices')->insert([
            [
                'type'       => 'PALLET',
                'price'      => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type'       => 'BULTO',
                'price'      => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        DB::table('prices')->whereIn('type', ['PALLET', 'BULTO'])->delete();
    }
};