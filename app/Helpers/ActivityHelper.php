<?php
// app/Helpers/ActivityHelper.php

namespace App\Helpers;

use App\Models\LogActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityHelper
{
    /**
     * Mencatat aktivitas ke log
     *
     * @param string $aksi
     * @param string $module
     * @param string $keterangan
     * @param array|null $oldData
     * @param array|null $newData
     * @return void
     */
    public static function log($aksi, $module, $keterangan, $oldData = null, $newData = null)
    {
        try {
            $user = Auth::user();
            
            LogActivity::create([
                'user_id' => $user ? $user->id : null,
                'ip' => Request::ip(),
                'aksi' => $aksi,
                'module' => $module,
                'keterangan' => $keterangan,
                'old_data' => $oldData,
                'new_data' => $newData,
                'user_agent' => Request::userAgent()
            ]);
        } catch (\Exception $e) {
            \Log::error('Gagal menyimpan log aktivitas: ' . $e->getMessage());
        }
    }

    /**
     * Mencatat aktivitas login
     */
    public static function logLogin($user)
    {
        self::log(
            'Login',
            'Auth',
            'User ' . $user->username . ' login ke sistem'
        );
    }

    /**
     * Mencatat aktivitas logout
     */
    public static function logLogout($user)
    {
        self::log(
            'Logout',
            'Auth',
            'User ' . $user->username . ' logout dari sistem'
        );
    }

    /**
     * Mencatat aktivitas CRUD Menu
     */
    public static function logMenu($aksi, $menu, $oldData = null)
    {
        $nama = $menu->nama_menu ?? 'Menu';
        
        // Replace match with switch
        switch($aksi) {
            case 'Tambah':
                $keterangan = 'Menambahkan menu: ' . $nama;
                break;
            case 'Edit':
                $keterangan = 'Mengupdate menu: ' . $nama;
                break;
            case 'Hapus':
                $keterangan = 'Menghapus menu: ' . $nama;
                break;
            default:
                $keterangan = $aksi . ' menu: ' . $nama;
                break;
        }

        self::log(
            $aksi,
            'Menu',
            $keterangan,
            $oldData,
            $aksi !== 'Hapus' ? $menu->toArray() : null
        );
    }

    /**
     * Mencatat aktivitas CRUD Order
     */
    public static function logOrder($aksi, $order, $oldData = null)
    {
        $kode = $order->kode_order ?? 'Order';
        
        // Replace match with switch
        switch($aksi) {
            case 'Tambah':
                $keterangan = 'Menambahkan order: ' . $kode;
                break;
            case 'Edit':
                $keterangan = 'Mengupdate order: ' . $kode;
                break;
            case 'Hapus':
                $keterangan = 'Menghapus order: ' . $kode;
                break;
            case 'Bayar':
                $keterangan = 'Pembayaran order: ' . $kode;
                break;
            default:
                $keterangan = $aksi . ' order: ' . $kode;
                break;
        }

        self::log(
            $aksi,
            'Order',
            $keterangan,
            $oldData,
            $aksi !== 'Hapus' ? $order->toArray() : null
        );
    }

    /**
     * Mencatat aktivitas CRUD User
     */
    public static function logUser($aksi, $user, $oldData = null)
    {
        $nama = $user->username ?? 'User';
        
        // Replace match with switch
        switch($aksi) {
            case 'Tambah':
                $keterangan = 'Menambahkan user: ' . $nama;
                break;
            case 'Edit':
                $keterangan = 'Mengupdate user: ' . $nama;
                break;
            case 'Hapus':
                $keterangan = 'Menghapus user: ' . $nama;
                break;
            default:
                $keterangan = $aksi . ' user: ' . $nama;
                break;
        }

        self::log(
            $aksi,
            'User',
            $keterangan,
            $oldData,
            $aksi !== 'Hapus' ? $user->toArray() : null
        );
    }
}