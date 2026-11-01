<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->date('periode_start')->nullable();
            $table->date('periode_end')->nullable();
        });
    }

    public function down()
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->dropColumn(['periode_start', 'periode_end']);
        });
    }
};