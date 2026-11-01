<?php
// app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'name',
        'gateway',
        'api_key',
        'api_secret',
        'merchant_code',
        'api_url',
        'is_active',
        'is_sandbox',
        'qris_enabled',
        'bank_transfer_enabled',
        'credit_card_enabled',
        'ewallet_enabled',
        'retail_enabled',
        'cash_enabled',
        'saldo_enabled',
        'hutang_enabled',      // <-- TAMBAHKAN
        'bank_list',
        'qris_image',
        'settings',
        'methods',
    ];

    protected $casts = [
        'settings' => 'array',
        'bank_list' => 'array',
        'methods' => 'array',
        'is_active' => 'boolean',
        'is_sandbox' => 'boolean',
        'qris_enabled' => 'boolean',
        'bank_transfer_enabled' => 'boolean',
        'credit_card_enabled' => 'boolean',
        'ewallet_enabled' => 'boolean',
        'retail_enabled' => 'boolean',
        'cash_enabled' => 'boolean',
        'saldo_enabled' => 'boolean',
        'hutang_enabled' => 'boolean',   // <-- TAMBAHKAN
    ];

    /**
     * Get available payment methods for this gateway
     */
    public function getAvailableMethods()
    {
        $allMethods = [
            'qris' => [
                'id' => 'qris',
                'name' => 'QRIS',
                'description' => 'Scan QRIS untuk bayar',
                'icon' => '📱',
            ],
            'cash' => [
                'id' => 'cash',
                'name' => 'Tunai (Cash)',
                'description' => 'Bayar langsung ke kasir',
                'icon' => '💵',
            ],
            'saldo' => [
                'id' => 'saldo',
                'name' => 'Saldo',
                'description' => 'Bayar dengan saldo Anda',
                'icon' => '💰',
            ],
            'hutang' => [  // <-- TAMBAHKAN
                'id' => 'hutang',
                'name' => 'Hutang',
                'description' => 'Bayar nanti (jatuh tempo setiap tgl 25)',
                'icon' => '💳',
            ],
        ];

        $enabledMethods = [];

        // ===== METHOD 1: Menggunakan kolom enabled terpisah =====
        if ($this->gateway === 'midtrans' || $this->gateway === 'tripay' || $this->gateway === 'xendit') {
            if ($this->qris_enabled) {
                $enabledMethods[] = $allMethods['qris'];
            }
            if ($this->bank_transfer_enabled) {
                // $enabledMethods[] = $allMethods['bank_transfer'];
            }
            if ($this->credit_card_enabled) {
                // $enabledMethods[] = $allMethods['credit_card'];
            }
            if ($this->ewallet_enabled) {
                // $enabledMethods[] = $allMethods['ewallet'];
            }
            if ($this->retail_enabled) {
                // $enabledMethods[] = $allMethods['retail'];
            }
        }

        // ===== KHUSUS CASH =====
        if ($this->gateway === 'cash' || $this->cash_enabled) {
            $enabledMethods[] = $allMethods['cash'];
        }

        // ===== KHUSUS SALDO =====
        if ($this->gateway === 'saldo' || $this->gateway === 'internal' || $this->saldo_enabled) {
            $enabledMethods[] = $allMethods['saldo'];
        }

        // ===== KHUSUS HUTANG =====  // <-- TAMBAHKAN
        if ($this->gateway === 'hutang' || $this->gateway === 'internal' || $this->hutang_enabled) {
            $enabledMethods[] = $allMethods['hutang'];
        }

        // ===== METHOD 2: Jika menggunakan kolom 'methods' (array) =====
        if (isset($this->methods) && is_array($this->methods) && !empty($this->methods)) {
            $methodsFromColumn = [];
            foreach ($this->methods as $methodKey) {
                if (isset($allMethods[$methodKey])) {
                    $methodsFromColumn[] = $allMethods[$methodKey];
                }
            }
            if (!empty($methodsFromColumn)) {
                return $methodsFromColumn;
            }
        }

        // ===== FALLBACK: Jika tidak ada metode yang di-enable =====
        if (empty($enabledMethods)) {
            // Untuk gateway tertentu, tampilkan metode default
            if ($this->gateway === 'midtrans') {
                $enabledMethods = [
                    $allMethods['qris'],
                    $allMethods['bank_transfer'],
                ];
            } elseif ($this->gateway === 'cash') {
                $enabledMethods = [$allMethods['cash']];
            } elseif ($this->gateway === 'saldo' || $this->gateway === 'internal') {
                $enabledMethods = [$allMethods['saldo']];
            } elseif ($this->gateway === 'hutang' || $this->gateway === 'internal') {
                $enabledMethods = [$allMethods['hutang']];
            } else {
                // Fallback: tampilkan semua metode
                $enabledMethods = array_values($allMethods);
            }
        }

        return $enabledMethods;
    }

    /**
     * Check if gateway has any enabled method
     */
    public function hasEnabledMethods()
    {
        // Cek berdasarkan gateway
        if ($this->gateway === 'cash') {
            return true;
        }
        
        if ($this->gateway === 'saldo' || $this->gateway === 'internal') {
            return true;
        }

        if ($this->gateway === 'hutang' || $this->gateway === 'internal') {
            return true;
        }

        // Cek berdasarkan kolom enabled
        return $this->qris_enabled || 
            $this->bank_transfer_enabled || 
            $this->credit_card_enabled || 
            $this->ewallet_enabled || 
            $this->retail_enabled ||
            $this->cash_enabled ||
            $this->saldo_enabled ||
            $this->hutang_enabled;   // <-- TAMBAHKAN
    }

    /**
     * Get specific method details
     */
    public function getMethodDetails($methodId)
    {
        $methods = $this->getAvailableMethods();
        foreach ($methods as $method) {
            if ($method['id'] === $methodId) {
                return $method;
            }
        }
        return null;
    }

    /**
     * Check if a specific method is enabled
     */
    public function isMethodEnabled($methodId)
    {
        $methods = $this->getAvailableMethods();
        foreach ($methods as $method) {
            if ($method['id'] === $methodId) {
                return true;
            }
        }
        return false;
    }
}