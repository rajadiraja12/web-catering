<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Midtrans, Tripay, Xendit
            $table->string('gateway')->unique(); // midtrans, tripay, xendit
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->string('merchant_code')->nullable();
            $table->string('api_url')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_sandbox')->default(true);
            
            // ===== PAYMENT METHODS =====
            $table->boolean('qris_enabled')->default(false);
            $table->boolean('bank_transfer_enabled')->default(false);
            $table->boolean('credit_card_enabled')->default(false);
            $table->boolean('ewallet_enabled')->default(false);
            $table->boolean('retail_enabled')->default(false);
            
            // Bank Transfer details
            $table->text('bank_list')->nullable(); // ['bca', 'bni', 'bri', 'mandiri']
            
            // QRIS details
            $table->string('qris_image')->nullable(); // Path ke gambar QRIS
            
            $table->text('settings')->nullable();
            $table->timestamps();
        });

        // Insert default payment gateways with payment methods
        DB::table('payments')->insert([
            [
                'name' => 'Midtrans',
                'gateway' => 'midtrans',
                'api_key' => null,
                'api_secret' => null,
                'merchant_code' => null,
                'api_url' => 'https://api.midtrans.com/v2',
                'is_active' => false,
                'is_sandbox' => true,
                'qris_enabled' => true,
                'bank_transfer_enabled' => true,
                'credit_card_enabled' => true,
                'ewallet_enabled' => true,
                'retail_enabled' => true,
                'bank_list' => json_encode(['bca', 'bni', 'bri', 'mandiri', 'cimb', 'danamon']),
                'qris_image' => null,
                'settings' => json_encode(['client_key' => null]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tripay',
                'gateway' => 'tripay',
                'api_key' => null,
                'api_secret' => null,
                'merchant_code' => null,
                'api_url' => 'https://api.tripay.co.id',
                'is_active' => false,
                'is_sandbox' => true,
                'qris_enabled' => true,
                'bank_transfer_enabled' => true,
                'credit_card_enabled' => false,
                'ewallet_enabled' => true,
                'retail_enabled' => true,
                'bank_list' => json_encode(['bca', 'bni', 'bri', 'mandiri']),
                'qris_image' => null,
                'settings' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Xendit',
                'gateway' => 'xendit',
                'api_key' => null,
                'api_secret' => null,
                'merchant_code' => null,
                'api_url' => 'https://api.xendit.co',
                'is_active' => false,
                'is_sandbox' => true,
                'qris_enabled' => true,
                'bank_transfer_enabled' => true,
                'credit_card_enabled' => true,
                'ewallet_enabled' => true,
                'retail_enabled' => false,
                'bank_list' => json_encode(['bca', 'bni', 'bri', 'mandiri']),
                'qris_image' => null,
                'settings' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};