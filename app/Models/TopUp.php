<?php
// app/Models/TopUp.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class TopUp extends Model
{
    protected $table = 'topups';

    protected $fillable = [
        'user_id',
        'kode_topup',
        'nominal',
        'metode_pembayaran',
        'payment_token',
        'payment_url',
        'status',
        'verified_by',
        'verified_at',
        'catatan',
        'expires_at',
        'paid_at',
        'midtrans_order_id'
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public static function generateCode()
    {
        return 'TP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    // ===== CEK EXPIRED =====
    public function isExpired()
    {
        return $this->status === 'expired' || 
               ($this->status === 'pending' && $this->created_at < now()->subHours(24));
    }

    // ===== AUTO EXPIRE =====
    public function autoExpire()
    {
        if ($this->isExpired() && $this->status !== 'expired') {
            $this->status = 'expired';
            $this->save();
            return true;
        }
        return false;
    }

    // ===== BOOT METHOD UNTUK EVENT =====
    protected static function booted()
    {
        static::retrieved(function ($topup) {
            // Cek expired saat model di-load
            if ($topup->status === 'pending' && $topup->created_at < now()->subHours(24)) {
                $topup->status = 'expired';
                $topup->save();
                
                Log::info('Topup auto-expired via model event', [
                    'topup_id' => $topup->id,
                    'kode_topup' => $topup->kode_topup
                ]);
            }
        });
    }

}