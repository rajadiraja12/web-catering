<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('nama_menu');
            $table->date('tanggal');
            $table->string('gambar')->nullable();
            $table->decimal('harga_paket_kecil', 10, 2)->default(0);
            $table->decimal('harga_paket_sedang', 10, 2)->default(0);
            $table->decimal('harga_paket_besar', 10, 2)->default(0);
            $table->decimal('harga_paket_khusus', 10, 2)->default(0);
            $table->text('deskripsi');
            $table->enum('status', ['available', 'unavailable'])->default('available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};