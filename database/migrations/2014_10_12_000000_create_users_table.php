<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('no_hp');
            $table->string('kelas');
            $table->string('alergi')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'customer','kasir'])->default('customer');
            $table->enum('status', ['Active', 'Non Aktif'])->default('Active');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};