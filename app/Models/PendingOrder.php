<?php
// app/Models/PendingOrder.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingOrder extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'order_id',
        'cart_data',
        'total',
        'payment_gateway',
        'expires_at',
    ];

    protected $casts = [
        'cart_data' => 'array',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}