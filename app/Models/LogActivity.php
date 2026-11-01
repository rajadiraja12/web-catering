<?php
// app/Models/LogActivity.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogActivity extends Model
{
    use HasFactory;

    protected $table = 'log_activities';

    protected $fillable = [
        'user_id',
        'ip',
        'aksi',
        'module',
        'keterangan',
        'old_data',
        'new_data',
        'user_agent'
    ];

    // Relasi dengan User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope untuk filter
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('aksi', 'like', "%{$search}%")
              ->orWhere('module', 'like', "%{$search}%")
              ->orWhere('keterangan', 'like', "%{$search}%")
              ->orWhereHas('user', function($q2) use ($search) {
                  $q2->where('username', 'like', "%{$search}%")
                     ->orWhere('name', 'like', "%{$search}%");
              });
        });
    }

    public function scopeDateRange($query, $from, $to)
    {
        if ($from && $to) {
            return $query->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
        }
        if ($from) {
            return $query->where('created_at', '>=', $from . ' 00:00:00');
        }
        if ($to) {
            return $query->where('created_at', '<=', $to . ' 23:59:59');
        }
        return $query;
    }

    // Accessor untuk format data
    public function getOldDataAttribute($value)
    {
        return $value ? json_decode($value, true) : null;
    }

    public function getNewDataAttribute($value)
    {
        return $value ? json_decode($value, true) : null;
    }

    // Mutator untuk menyimpan data sebagai JSON
    public function setOldDataAttribute($value)
    {
        $this->attributes['old_data'] = $value ? json_encode($value) : null;
    }

    public function setNewDataAttribute($value)
    {
        $this->attributes['new_data'] = $value ? json_encode($value) : null;
    }
}