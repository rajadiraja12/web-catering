<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingPayment extends Model
{
    use HasFactory;

    protected $table = 'pending_payments';

    protected $fillable = [
        'order_id',
        'user_id',
        'cart_data',
        'total',
        'payment_id',
        'payment_method',
        'payment_gateway',
        'snap_token',           // <-- TAMBAHKAN
        'midtrans_order_id',    // <-- TAMBAHKAN
        'payment_url', 
        'status',
        'expires_at',
        'snap_token',
        'payment_url',
    ];

    protected $casts = [
        'cart_data' => 'array',
        'total' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Scope untuk pending payment yang masih aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '>', now());
    }

    /**
     * Scope untuk expired payment
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '<=', now());
    }
}