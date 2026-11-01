<?php
// database/migrations/2024_01_01_000003_update_topups_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTopupsTable extends Migration
{
    public function up()
    {
        Schema::table('topups', function (Blueprint $table) {
            // Tambahkan kolom untuk Midtrans
            $table->string('payment_token')->nullable()->after('metode_pembayaran');
            $table->string('payment_url')->nullable()->after('payment_token');
            $table->string('midtrans_order_id')->nullable()->after('payment_url');
            $table->timestamp('paid_at')->nullable()->after('expires_at');
            
            // Hapus kolom bukti_pembayaran (tidak diperlukan lagi)
            $table->dropColumn('bukti_pembayaran');
        });
    }

    public function down()
    {
        Schema::table('topups', function (Blueprint $table) {
            $table->string('bukti_pembayaran')->nullable();
            $table->dropColumn(['payment_token', 'payment_url', 'midtrans_order_id', 'paid_at']);
        });
    }
}