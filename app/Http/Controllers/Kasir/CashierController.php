<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PendingPayment;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CashierController extends Controller
{
    /**
     * Halaman Dashboard Kasir
     */
    public function index()
    {
        // Data statistik
        $pendingPayments = PendingPayment::whereIn('payment_method', ['kasir','cash'])
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->count();
            
        $todayOrders = Order::whereDate('created_at', now())
            ->whereIn('payment_method', ['kasir','cash'])
            ->count();
            
        $todayRevenue = Order::whereDate('created_at', now())
            ->whereIn('payment_method', ['kasir','cash'])
            ->where('payment_status', 'paid')
            ->sum('total_harga');
            
        $recentOrders = Order::with(['menu', 'customer'])
            ->whereDate('created_at', now()->toDateString())
            ->whereIn('payment_method', ['kasir','cash'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        return view('kasir.cashiers.dashboard', compact(
            'pendingPayments',
            'todayOrders',
            'todayRevenue',
            'recentOrders'
        ));
    }

    /**
     * Halaman Scan QR Code
     */
    public function scan()
    {
        return view('kasir.cashiers.scan');
    }

    /**
     * Halaman Konfirmasi Pembayaran
     */
    public function confirm($orderId)
    {
        $pending = PendingPayment::where('order_id', $orderId)
            ->where('payment_method', 'cash')
            ->where('status', 'pending')
            ->first();
            
        if (!$pending) {
            return redirect()->route('kasir.scan')
                ->with('error', 'QR Code tidak valid atau sudah kadaluarsa!');
        }
        
        $customer = User::find($pending->user_id);
        $total = $pending->total;
        
        return view('kasir.cashiers.confirm', compact('pending', 'customer', 'total', 'orderId'));
    }

    /**
     * Proses Konfirmasi Pembayaran
     */
    public function processConfirm(Request $request, $orderId)
    {
        DB::beginTransaction();
        try {
            $pending = PendingPayment::where('order_id', $orderId)
                ->where('payment_method', 'cash')
                ->where('status', 'pending')
                ->first();
                
            if (!$pending) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pembayaran tidak ditemukan!'
                ], 404);
            }
            
            // Cek apakah sudah kadaluarsa
            if ($pending->expires_at < now()) {
                $pending->update(['status' => 'expired']);
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code sudah kadaluarsa!'
                ], 400);
            }
            
            // Cek apakah sudah dibayar
            $existingOrders = Order::where('kode_order', 'LIKE', $orderId . '%')
                ->where('id_customer', $pending->user_id)
                ->get();
                
            if ($existingOrders->count() > 0) {
                $pending->update(['status' => 'completed']);
                return response()->json([
                    'success' => true,
                    'message' => 'Pembayaran sudah dikonfirmasi sebelumnya!'
                ]);
            }
            
            // Simpan order
            $carts = $pending->cart_data;
            $user = $pending->user;
            $orderIds = [];
            
            foreach ($carts as $cartData) {
                $uniqueOrderId = $orderId . '-' . strtoupper(substr(uniqid(), -4));
                
                $order = Order::create([
                    'kode_order' => $uniqueOrderId,
                    'tanggal_order' => now(),
                    'tanggal_menu' => $cartData['menu']['tanggal'] ?? $cartData['tanggal'] ?? now(),
                    'id_menu' => $cartData['menu_id'],
                    'id_customer' => $pending->user_id,
                    'paket' => $cartData['paket'],
                    'harga_satuan' => $cartData['price'],
                    'total_harga' => $cartData['subtotal'],
                    'payment_status' => 'paid',
                    'payment_method' => 'cash',
                    'payment_id' => $pending->payment_id,
                    'paid_at' => now(),
                    'confirmed_by' => auth()->id(),
                    'confirmed_at' => now(),
                ]);
                
                $orderIds[] = $order->id;
                
                Report::create([
                    'tanggal_laporan' => now(),
                    'keterangan' => 'Pembayaran Cash Order: ' . $order->kode_order . ' - ' . ($user->name ?? 'Customer'),
                    'debit' => $order->total_harga,
                    'kredit' => 0,
                    'saldo' => $order->total_harga,
                    'kategori' => 'Pendapatan',
                ]);
            }
            
            // Hapus cart
            \App\Models\Cart::where('user_id', $pending->user_id)->delete();
            
            // Update status
            $pending->update(['status' => 'completed']);
            
            DB::commit();
            
            Log::info('Cash payment confirmed', [
                'order_id' => $orderId,
                'confirmed_by' => auth()->id(),
                'order_count' => count($orderIds)
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dikonfirmasi!',
                'order_ids' => $orderIds,
                'redirect' => route('kasir.dashboard')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to confirm cash payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal konfirmasi pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Payment Status via AJAX
     */
    public function getStatus($orderId)
    {
        $pending = PendingPayment::where('order_id', $orderId)
            ->where('payment_method', 'cash')
            ->first();
            
        if (!$pending) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Data tidak ditemukan'
            ]);
        }
        
        $order = Order::where('kode_order', 'LIKE', $orderId . '%')
            ->where('id_customer', $pending->user_id)
            ->first();
            
        if ($order) {
            return response()->json([
                'status' => 'paid',
                'message' => 'Pembayaran sudah dikonfirmasi'
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
            'message' => 'Menunggu konfirmasi',
            'total' => $pending->total,
            'customer' => $pending->user->name ?? 'Customer',
            'expires_at' => $pending->expires_at
        ]);
    }

    /**
     * Riwayat Transaksi Cash
     */
    public function history(Request $request)
    {
        $query = Order::with(['menu', 'customer'])
            ->where('payment_method','cash' )
            ->where('payment_status', 'paid');
            
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('kode_order', 'LIKE', '%' . $request->search . '%')
                  ->orWhereHas('customer', function($cq) use ($request) {
                      $cq->where('name', 'LIKE', '%' . $request->search . '%');
                  });
            });
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(20);
        $totalRevenue = $query->sum('total_harga');
        
        return view('kasir.cashiers.history', compact('orders', 'totalRevenue'));
    }

    // Di CashierController
    public function cancel($orderId)
    {
        $pending = PendingPayment::where('order_id', $orderId)
            ->where('payment_method', 'cash' )
            ->where('status', 'pending')
            ->first();
            
        if ($pending) {
            $pending->update(['status' => 'cancelled']);
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false], 404);
    }
}
