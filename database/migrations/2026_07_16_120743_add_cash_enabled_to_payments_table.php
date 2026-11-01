<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCashEnabledToPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->boolean('cash_enabled')->default(true)->after('retail_enabled');
            $table->string('cash_qr_code')->nullable()->after('cash_enabled');
            $table->string('cash_qr_image')->nullable()->after('cash_qr_code');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['cash_enabled', 'cash_qr_code', 'cash_qr_image']);
        });
    }
}
