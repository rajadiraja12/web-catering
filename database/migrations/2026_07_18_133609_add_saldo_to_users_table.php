<?php
// database/migrations/2024_01_01_000000_add_saldo_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaldoToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('saldo', 15, 2)->default(0)->after('password');
            $table->decimal('saldo_total', 15, 2)->default(0)->after('saldo');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['saldo', 'saldo_total']);
        });
    }
}