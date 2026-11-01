<?php
// database/migrations/2026_07_16_add_payment_id_to_orders.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('set null');
            $table->string('payment_method')->nullable(); // bank_transfer, credit_card, ewallet
            $table->string('payment_reference')->nullable(); // Reference dari payment gateway
            $table->timestamp('paid_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropColumn(['payment_id', 'payment_method', 'payment_reference', 'paid_at']);
        });
    }
};