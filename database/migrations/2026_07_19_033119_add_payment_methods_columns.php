<?php
// database/migrations/2024_XXXX_XX_add_payment_methods_columns.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentMethodsColumns extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            // Tambahkan kolom untuk cash dan saldo
            $table->boolean('saldo_enabled')->default(false)->after('cash_enabled');
            
            // Kolom methods untuk menyimpan array metode yang di-enable
            $table->text('methods')->nullable()->after('settings');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['cash_enabled', 'saldo_enabled', 'methods']);
        });
    }
}