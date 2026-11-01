<?php
// app/Models/Order.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_order',
        'tanggal_order',
        'tanggal_menu',
        'id_menu',
        'id_customer',
        'paket',
        'harga_satuan',
        'total_harga',
        'catatan',           // <-- TAMBAHKAN
        'payment_status',
        'payment_method',  // TAMBAHKAN
        'payment_id',      // TAMBAHKAN
        'paid_at',         // TAMBAHKAN
        'is_hutang',      // <-- TAMBAHKAN
        'due_date',       // <-- TAMBAHKAN
        'late_fee',       // <-- TAMBAHKAN 
    ];

    protected $casts = [
        'tanggal_order' => 'date',
        'tanggal_menu' => 'date',
        'paid_at' => 'datetime',
        'harga_satuan' => 'decimal:2',
        'total_harga' => 'decimal:2',
        'is_hutang' => 'boolean',
        'due_date' => 'date',
        'late_fee' => 'decimal:2',
    ];

    // ===== RELATIONSHIPS =====
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu');
    }
    
    public function tanggal()
    {
        return $this->belongsTo(Order::class, 'tanggal_menu', 'tanggal');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'id_customer');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    // ===== SCOPES =====
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    // ===== ACCESSORS =====
    public function getStatusLabelAttribute()
    {
        return [
            'pending' => '⏳ Pending',
            'paid' => '✅ Lunas',
            'unpaid' => '❌ Belum Bayar',
        ][$this->payment_status] ?? $this->payment_status;
    }

    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'yellow',
            'paid' => 'green',
            'unpaid' => 'red',
        ][$this->payment_status] ?? 'gray';
    }

    public function getPaymentMethodLabelAttribute()
    {
        $methods = [
            'qris' => 'QRIS',
            'bank_transfer' => 'Transfer Bank',
            'credit_card' => 'Kartu Kredit',
            'ewallet' => 'E-Wallet',
            'retail' => 'Retail / Indomaret',
        ];
        
        return $methods[$this->payment_method] ?? $this->payment_method;
    }
}