<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_backup',
        'lokasi_file',
        'type',
        'keterangan_data',
        'size',
        'created_by',
        'backup_date',
        'periode_start',
        'periode_end',
    ];

    protected $casts = [
        'size' => 'decimal:2',
        'backup_date' => 'datetime',
        'periode_start' => 'date',
        'periode_end' => 'date',
    ];

    // Relasi
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeManual($query)
    {
        return $query->where('type', 'manual');
    }

    public function scopeOtomatis($query)
    {
        return $query->where('type', 'otomatis');
    }

    public function scopeTransaksi($query)
    {
        return $query->where('keterangan_data', 'LIKE', '%transaksi%');
    }

    public function scopeKeuangan($query)
    {
        return $query->where('keterangan_data', 'LIKE', '%keuangan%');
    }
}