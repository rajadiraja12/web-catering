<?php
// database/migrations/2026_07_16_create_pending_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pending_orders', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('order_id'); // Kode order dari Midtrans
            $table->text('cart_data'); // Data cart
            $table->decimal('total', 10, 2);
            $table->string('payment_gateway');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pending_orders');
    }
};