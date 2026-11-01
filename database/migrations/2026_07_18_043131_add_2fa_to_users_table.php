<?php
// database/migrations/2024_01_01_000000_add_2fa_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Add2faToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google2fa_secret')->nullable()->after('password');
            $table->boolean('google2fa_enabled')->default(false)->after('google2fa_secret');
            $table->timestamp('google2fa_verified_at')->nullable()->after('google2fa_enabled');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google2fa_secret', 'google2fa_enabled', 'google2fa_verified_at']);
        });
    }
}