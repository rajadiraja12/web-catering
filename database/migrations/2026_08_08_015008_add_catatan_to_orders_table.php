<?php
// database/migrations/2024_08_08_000000_add_catatan_to_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCatatanToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'catatan')) {
                $table->text('catatan')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('orders', 'bukti_pembayaran')) {
                $table->string('bukti_pembayaran')->nullable()->after('catatan');
            }
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['catatan', 'bukti_pembayaran']);
        });
    }
}