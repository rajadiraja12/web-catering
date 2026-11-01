<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'nama_menu',
        'deskripsi',
        'gambar',
        'tanggal',
        'harga_paket_kecil',
        'harga_paket_sedang',
        'harga_paket_besar',
        'harga_paket_khusus',
        'tipe_menu',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'harga_paket_kecil' => 'decimal:2',
        'harga_paket_sedang' => 'decimal:2',
        'harga_paket_besar' => 'decimal:2',
        'harga_paket_khusus' => 'decimal:2',
    ];

    // ===== SCOPES =====
    public function scopeUmum($query)
    {
        return $query->where('tipe_menu', 'umum');
    }

    public function scopeKelas1($query)
    {
        return $query->where('tipe_menu', 'kelas_1');
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'id_menu');
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
}