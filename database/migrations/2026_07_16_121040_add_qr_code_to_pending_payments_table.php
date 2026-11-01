<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQrCodeToPendingPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pending_payments', function (Blueprint $table) {
            $table->text('qr_code')->nullable()->after('expires_at');
            $table->text('qr_image')->nullable()->after('qr_code');
        });
    }

    public function down()
    {
        Schema::table('pending_payments', function (Blueprint $table) {
            $table->dropColumn(['qr_code', 'qr_image']);
        });
    }
}
