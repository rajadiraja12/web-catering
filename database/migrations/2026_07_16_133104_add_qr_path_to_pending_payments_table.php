<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQrPathToPendingPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pending_payments', function (Blueprint $table) {
            $table->string('qr_path')->nullable()->after('qr_image');
        });
    }

    public function down()
    {
        Schema::table('pending_payments', function (Blueprint $table) {
            $table->dropColumn('qr_path');
        });
    }
}
