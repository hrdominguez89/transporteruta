<?php
// database/migrations/2026_07_22_000000_add_role_and_client_id_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 10)->nullable();

            $table->unsignedBigInteger('client_id')->nullable()->after('role');
            $table->foreign('client_id')
                  ->references('id')->on('clients')
                  ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn(['client_id', 'role']);
        });
    }
};