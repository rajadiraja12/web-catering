<?php
// database/migrations/2026_08_06_010259_add_hutang_fields_to_orders.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHutangFieldsToOrders extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'is_hutang')) {
                $table->boolean('is_hutang')->default(false)->after('payment_method');
            }
            if (!Schema::hasColumn('orders', 'due_date')) {
                $table->date('due_date')->nullable()->after('is_hutang');
            }
            if (!Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('due_date');
            }
            if (!Schema::hasColumn('orders', 'late_fee')) {
                $table->decimal('late_fee', 15, 2)->default(0)->after('paid_at');
            }
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_hutang', 'due_date', 'paid_at', 'late_fee']);
        });
    }
}