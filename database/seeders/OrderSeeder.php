<?php
// database/seeders/OrderSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Menu;
use App\Models\User;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil data menu dan customer yang sudah ada
        $menus = Menu::all();
        $customers = User::where('role', 'customer')->get();
        
        if ($menus->isEmpty() || $customers->isEmpty()) {
            $this->command->error('Tidak ada data menu atau customer. Jalankan seeder terlebih dahulu.');
            return;
        }

        $pakets = ['kecil', 'sedang', 'besar'];
        $paymentStatuses = ['unpaid', 'paid'];

        // Buat 20 data order dummy
        for ($i = 1; $i <= 20; $i++) {
            $menu = $menus->random();
            $customer = $customers->random();
            $paket = $pakets[array_rand($pakets)];
            $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];
            
            // Dapatkan harga berdasarkan paket
            $hargaSatuan = $this->getHargaByPaket($menu, $paket);
            $totalHarga = $hargaSatuan; // Bisa ditambah dengan biaya lain jika ada
            
            Order::create([
                'kode_order' => 'ORD' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'tanggal_order' => Carbon::now()->subDays(rand(0, 15)),
                'tanggal_menu' => Carbon::now()->subDays(rand(0, 15)),
                'id_menu' => $menu->id,
                'id_customer' => $customer->id,
                'paket' => $paket,
                'harga_satuan' => $hargaSatuan,
                'total_harga' => $totalHarga,
                'payment_status' => $paymentStatus,
                'created_at' => Carbon::now()->subDays(rand(0, 15)),
                'updated_at' => Carbon::now()->subDays(rand(0, 15)),
            ]);
        }

        $this->command->info('20 data order berhasil dibuat!');
    }

    private function getHargaByPaket($menu, $paket)
    {
        switch ($paket) {
            case 'kecil':
                return $menu->harga_paket_kecil ?? 10000;
            case 'sedang':
                return $menu->harga_paket_sedang ?? 15000;
            case 'besar':
                return $menu->harga_paket_besar ?? 20000;
            default:
                return 10000;
        }
    }
}