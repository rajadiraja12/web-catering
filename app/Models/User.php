<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'no_hp',
        'kelas',
        'alergi',
        'password',
        'role',
        'status',
        'session_id',
        'session_ip',
        'session_device',
        'last_login_at',
        'last_login_ip',
        'last_login_device',
        'google2fa_secret',
        'google2fa_enabled',
        'google2fa_verified_at',
        'saldo',
        'saldo_total',
        'hutang_limit',    // <-- UBAH
        'hutang_used',     // <-- UBAH
        'hutang_enabled',  // <-- UBAH
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime', // Tambahkan ini
        'google2fa_enabled' => 'boolean',
        'google2fa_verified_at' => 'datetime',
        'hutang_limit' => 'decimal:2',
        'hutang_used' => 'decimal:2',
        'hutang_enabled' => 'boolean',
    ];

    // Relasi
    public function orders()
    {
        return $this->hasMany(Order::class, 'id_customer');
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function logActivities()
    {
        return $this->hasMany(LogActivity::class);
    }

    public function getHutangAvailableAttribute()
    {
        return $this->hutang_limit - $this->hutang_used;
    }

    public function getHutangPercentageAttribute()
    {
        if ($this->hutang_limit == 0) return 0;
        return round(($this->hutang_used / $this->hutang_limit) * 100, 2);
    }

    public function backups()
    {
        return $this->hasMany(Backup::class, 'created_by');
    }
    public function getKelasNumberAttribute()
    {
        $parts = explode('-', $this->kelas);
        return $parts[0] ?? '';
    }

    public function getKelasNameAttribute()
    {
        $parts = explode('-', $this->kelas);
        return $parts[1] ?? '';
    }
    // Cek apakah user sedang aktif
    public function isActive()
    {
        return $this->status === 'Active';
    }

    // Cek apakah user online
    public function isOnline()
    {
        if (!$this->session_id) return false;
        
        $sessionExists = \DB::table('sessions')
            ->where('id', $this->session_id)
            ->exists();
            
        return $sessionExists;
    }
}