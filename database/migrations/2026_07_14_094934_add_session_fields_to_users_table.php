<?php
// database/migrations/2026_07_14_000000_add_session_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('session_id')->nullable();
            $table->string('session_ip')->nullable();        // IP address
            $table->string('session_device')->nullable();    // Nama perangkat
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->string('last_login_device')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['session_id','session_ip','session_device', 'last_login_at', 'last_login_ip', 'last_login_device']);
        });
    }
};