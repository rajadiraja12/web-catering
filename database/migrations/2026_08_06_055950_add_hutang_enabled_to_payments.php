<?php
// database/migrations/2024_01_01_000000_add_hutang_enabled_to_payments.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHutangEnabledToPayments extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'hutang_enabled')) {
                $table->boolean('hutang_enabled')->default(true)->after('saldo_enabled');
            }
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('hutang_enabled');
        });
    }
}