<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal_laporan',
        'keterangan',
        'debit',
        'kredit',
        'saldo',
        'kategori',
    ];

    protected $casts = [
        'tanggal_laporan' => 'date',
        'debit' => 'decimal:2',
        'kredit' => 'decimal:2',
        'saldo' => 'decimal:2',
    ];

    // Scope
    public function scopeByCategory($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    public function scopeByDate($query, $tanggal)
    {
        return $query->whereDate('tanggal_laporan', $tanggal);
    }
}