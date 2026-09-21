<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('config')) {
            return;
        }
        Schema::create('config', function (Blueprint $table) {
            $table->id();
            $table->boolean('automatico')->default(false);
            $table->tinyInteger('dia')->default(2);  
            $table->time('hora')->default('11:00');
            $table->timestamps();
        });
        DB::table('config')->insert([
            'automatico' => true,
            'dia'        => 2,
            'hora'       => '11:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('config');
    }
};