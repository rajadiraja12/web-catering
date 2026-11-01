<?php
// database/migrations/2026_08_06_010142_add_hutang_to_users.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHutangToUsers extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'hutang_limit')) {
                $table->decimal('hutang_limit', 15, 2)->default(500000)->after('saldo_total');
            }
            if (!Schema::hasColumn('users', 'hutang_used')) {
                $table->decimal('hutang_used', 15, 2)->default(0)->after('hutang_limit');
            }
            if (!Schema::hasColumn('users', 'hutang_enabled')) {
                $table->boolean('hutang_enabled')->default(true)->after('hutang_used');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['hutang_limit', 'hutang_used', 'hutang_enabled']);
        });
    }
}