<?php
// app/Http/Controllers/Customer/CashQRController.php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PendingPayment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CashQRController extends Controller
{
    /**
     * Get authenticated user from session
     */
    private function getAuthUser()
    {
        if (!session()->has('user_id')) {
            return null;
        }
        
        $userId = session('user_id');
        return User::find($userId);
    }

    /**
     * Get authenticated user ID from session
     */
    private function getAuthId()
    {
        return session('user_id');
    }

    /**
     * Tampilkan halaman QR Code untuk pembayaran cash
     */
    public function show($orderId)
    {
        // ===== CEK SESSION =====
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }
        
        $userId = $this->getAuthId();
        $user = $this->getAuthUser();
        
        if (!$user) {
            session()->forget(['user_id', 'user_name', 'user_role', 'user_username']);
            return redirect('/login')->with('error', 'Session tidak valid. Silakan login kembali.');
        }
        
        // Cari data pending payment
        $pending = PendingPayment::where('order_id', $orderId)
            ->where('user_id', $userId)
            ->where('payment_method', 'cash')
            ->first();
        
        if (!$pending) {
            return redirect()->route('customer.orders')
                ->with('error', 'Data pembayaran tidak ditemukan!');
        }
        
        // Cek apakah sudah dibayar
        $order = Order::where('kode_order', 'LIKE', $orderId . '%')
            ->where('id_customer', $userId)
            ->first();
        
        if ($order) {
            return redirect()->route('customer.orders')
                ->with('success', 'Pembayaran sudah dikonfirmasi!');
        }
        
        // Cek apakah expired
        if ($pending->expires_at < now()) {
            $pending->update(['status' => 'expired']);
            return view('customer.cash_qr_expired', compact('pending'));
        }
        
        // Generate QR Code jika belum ada
        if (!$pending->qr_image) {
            $this->generateQRCode($pending);
        }
        
        // Kirim user ke view
        return view('customer.cash_qr', compact('pending', 'user'));
    }
    
    /**
     * Generate QR Code untuk pending payment
     */
    private function generateQRCode($pending)
    {
        $user = User::find($pending->user_id);
        
        $qrData = json_encode([
            'order_id' => $pending->order_id,
            'total' => $pending->total,
            'customer' => $user->name ?? 'Customer',
            'date' => now()->toDateTimeString(),
            'type' => 'cash_payment'
        ]);
        
        try {
            // Generate QR Code sebagai SVG
            $qrCodeSvg = QrCode::format('svg')
                ->size(300)
                ->margin(10)
                ->errorCorrection('H')
                ->generate($qrData);
            
            $qrCodeString = (string) $qrCodeSvg;
            $qrImageBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrCodeString);
            
            // Simpan ke database
            $pending->qr_code = $qrData;
            $pending->qr_image = $qrImageBase64;
            $pending->save();
            
            return $qrImageBase64;
            
        } catch (\Exception $e) {
            Log::error('QR Code generation error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Cek status pembayaran via AJAX
     */
    public function checkStatus($orderId)
    {
        // ===== CEK SESSION =====
        if (!session()->has('user_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Silakan login terlebih dahulu.'
            ], 401);
        }
        
        $userId = $this->getAuthId();
        
        try {
            // Cek di tabel orders
            $order = Order::where('kode_order', 'LIKE', $orderId . '%')
                ->where('id_customer', $userId)
                ->first();
            
            if ($order) {
                return response()->json([
                    'status' => 'paid',
                    'message' => 'Pembayaran berhasil dikonfirmasi!',
                    'redirect' => route('customer.orders')
                ]);
            }
            
            // Cek di pending payment
            $pending = PendingPayment::where('order_id', $orderId)
                ->where('user_id', $userId)
                ->first();
            
            if (!$pending) {
                return response()->json([
                    'status' => 'not_found',
                    'message' => 'Data tidak ditemukan'
                ]);
            }
            
            if ($pending->expires_at < now()) {
                return response()->json([
                    'status' => 'expired',
                    'message' => 'QR Code sudah kadaluarsa'
                ]);
            }
            
            return response()->json([
                'status' => 'pending',
                'message' => 'Menunggu konfirmasi kasir...'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Check status error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Download QR Code
     */
    public function download($orderId)
    {
        // ===== CEK SESSION =====
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }
        
        $userId = $this->getAuthId();
        
        $pending = PendingPayment::where('order_id', $orderId)
            ->where('user_id', $userId)
            ->where('payment_method', 'cash')
            ->first();
        
        if (!$pending || !$pending->qr_image) {
            return redirect()->back()->with('error', 'QR Code tidak ditemukan');
        }
        
        // Decode base64 image
        $imageData = base64_decode(str_replace('data:image/svg+xml;base64,', '', $pending->qr_image));
        
        return response($imageData)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="QR-Cash-' . $orderId . '.svg"');
    }
    
    /**
     * Print QR Code
     */
    public function print($orderId)
    {
        // ===== CEK SESSION =====
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }
        
        $userId = $this->getAuthId();
        $user = $this->getAuthUser();
        
        if (!$user) {
            session()->forget(['user_id', 'user_name', 'user_role', 'user_username']);
            return redirect('/login')->with('error', 'Session tidak valid. Silakan login kembali.');
        }
        
        $pending = PendingPayment::where('order_id', $orderId)
            ->where('user_id', $userId)
            ->where('payment_method', 'cash')
            ->first();
        
        if (!$pending) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }
        
        return view('customer.cash_qr_print', compact('pending', 'user'));
    }
}