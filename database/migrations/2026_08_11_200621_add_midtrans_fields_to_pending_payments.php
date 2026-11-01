<?php
// database/migrations/2024_08_11_000000_add_midtrans_fields_to_pending_payments.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMidtransFieldsToPendingPayments extends Migration
{
    public function up()
    {
        Schema::table('pending_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('pending_payments', 'snap_token')) {
                $table->string('snap_token')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('pending_payments', 'midtrans_order_id')) {
                $table->string('midtrans_order_id')->nullable()->after('snap_token');
            }
            if (!Schema::hasColumn('pending_payments', 'payment_url')) {
                $table->string('payment_url')->nullable()->after('midtrans_order_id');
            }
        });
    }

    public function down()
    {
        Schema::table('pending_payments', function (Blueprint $table) {
            $table->dropColumn(['snap_token', 'midtrans_order_id', 'payment_url']);
        });
    }
}