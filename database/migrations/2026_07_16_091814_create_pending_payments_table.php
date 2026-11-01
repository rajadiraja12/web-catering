<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pending_payments', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('cart_data');
            $table->decimal('total', 10, 2);
            $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('set null');
            $table->string('payment_method');
            $table->string('payment_gateway');
            $table->enum('status', ['pending', 'processing', 'paid', 'failed', 'expired', 'cancelled','completed'])->default('pending');
            $table->string('snap_token')->nullable();
            $table->string('payment_url')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pending_payments');
    }
};