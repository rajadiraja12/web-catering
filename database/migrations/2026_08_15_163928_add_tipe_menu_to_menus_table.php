<?php
// database/migrations/2024_08_15_000000_add_tipe_menu_to_menus_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipeMenuToMenusTable extends Migration
{
    public function up()
    {
        Schema::table('menus', function (Blueprint $table) {
            if (!Schema::hasColumn('menus', 'tipe_menu')) {
                $table->enum('tipe_menu', ['umum', 'kelas_1'])->default('umum')->after('status');
            }
        });
    }

    public function down()
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn(['tipe_menu']);
        });
    }
}