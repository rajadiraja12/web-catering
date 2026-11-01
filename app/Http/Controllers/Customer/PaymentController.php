<?php
// app/Http/Controllers/Customer/PaymentController.php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PendingPayment;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PaymentController extends Controller
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
     * Halaman Pembayaran
     */
    public function index()
    {
        // ===== CEK SESSION =====
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }
        
        $user = $this->getAuthUser();
        $userId = $this->getAuthId();
        
        if (!$user) {
            session()->forget(['user_id', 'user_name', 'user_role', 'user_username']);
            return redirect('/login')->with('error', 'Session tidak valid. Silakan login kembali.');
        }

        $existingPending = PendingPayment::where('user_id', $userId)
                ->where('status', ['pending'])
                ->exists();

        if ($existingPending) {
                return redirect()->route('customer.cart')->with('error', 'masih ada pesanan pending');
            }
        
        $carts = Cart::with('menu')
            ->where('user_id', $userId)
            ->get();

        if ($carts->count() == 0) {
            return redirect()->route('customer.cart')->with('error', 'Keranjang kosong!');
        }

        $total = $carts->sum('subtotal');

        $paymentGateways = Payment::where('is_active', true)->get();
        $paymentGateways = $paymentGateways->filter(function($gateway) {
            return $gateway->hasEnabledMethods();
        });

        $gatewayMethods = [];
        foreach ($paymentGateways as $gateway) {
            $methods = $gateway->getAvailableMethods();
            if (!empty($methods)) {
                $gatewayMethods[$gateway->id] = [
                    'gateway' => $gateway,
                    'methods' => $methods
                ];
            }
        }

        if ($paymentGateways->count() == 0) {
            return redirect()->route('customer.cart')->with('error', 'Belum ada metode pembayaran yang tersedia. Silakan hubungi admin.');
        }

        return view('customer.payment', compact('carts', 'total', 'paymentGateways', 'gatewayMethods','user'));
    }

    /**
     * Proses Pembayaran - SIMPAN KE DATABASE TEMPORARY
     */
    public function process(Request $request)
    {
        // ===== CEK SESSION =====
        if (!session()->has('user_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.'
            ], 401);
        }
        
        $userId = $this->getAuthId();
        $user = $this->getAuthUser();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'payment_gateway' => 'required|exists:payments,id',
            'payment_method' => 'required|string|in:qris,bank_transfer,credit_card,ewallet,retail,cash,saldo,hutang',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $carts = Cart::with('menu')
            ->where('user_id', $userId)
            ->get();

        if ($carts->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang kosong!'
            ], 400);
        }

        $payment = Payment::find($request->payment_gateway);
        $paymentMethod = $request->payment_method;
        $total = $carts->sum('subtotal');

        if (!$payment || !$payment->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Payment gateway tidak aktif!'
            ], 400);
        }

        $orderId = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        try {
            $pendingPayment = PendingPayment::create([
                'order_id' => $orderId,
                'user_id' => $userId,
                'cart_data' => $carts->toArray(),
                'total' => $total,
                'payment_id' => $payment->id,
                'payment_method' => $paymentMethod,
                'payment_gateway' => $payment->gateway,
                'status' => 'pending',
                'expires_at' => now()->addHours(24),
            ]);
            
            session()->put('pending_payment_id', $pendingPayment->id);
            session()->put('last_order_id', $orderId);
            session()->put('payment_carts', $carts->toArray());
            session()->put('payment_method', $paymentMethod);
            session()->put('payment_id', $payment->id);
            session()->put('total_amount', $total);
            
        } catch (\Exception $e) {
            Log::error('Failed to save pending payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran. Silakan coba lagi!'
            ], 500);
        }

        // ===== PROSES CASH =====
        if ($paymentMethod === 'cash') {
            return $this->processCash($payment, $carts, $total, $orderId);
        }

        if ($paymentMethod === 'saldo') {
            return $this->processSaldo($payment, $carts, $total, $orderId);
        }

        if ($paymentMethod === 'hutang') {
            return $this->processHutang($payment, $carts, $total, $orderId);
        }

        switch ($payment->gateway) {
            case 'midtrans':
                $result = $this->processMidtrans($payment, $carts, $total, $orderId, $paymentMethod);
                break;
            case 'tripay':
                $result = $this->processTripay($payment, $carts, $total, $orderId, $paymentMethod);
                break;
            case 'xendit':
                $result = $this->processXendit($payment, $carts, $total, $orderId, $paymentMethod);
                break;
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway tidak dikenali!'
                ], 400);
        }

        return response()->json($result);
    }

    /**
     * Calculate due date to the 25th of next month or current month
     */
    private function calculateDueDate()
    {
        $now = now('Asia/Jakarta');
        $currentDay = (int) $now->format('d');
        $currentMonth = (int) $now->format('m');
        $currentYear = (int) $now->format('Y');
        
        // Jika tanggal sekarang <= 25, jatuh tempo di tanggal 25 bulan ini
        if ($currentDay <= 25) {
            $dueDate = $now->copy()->setDay(25);
        } else {
            // Jika tanggal sekarang > 25, jatuh tempo di tanggal 25 bulan depan
            $dueDate = $now->copy()->addMonth()->setDay(25);
        }
        
        // Log untuk debugging
        Log::info('Due date calculated:', [
            'current_date' => $now->format('Y-m-d'),
            'current_day' => $currentDay,
            'due_date' => $dueDate->format('Y-m-d'),
            'timezone' => 'Asia/Jakarta'
        ]);
        
        return $dueDate;
    }

    private function processHutang($payment, $carts, $total, $orderId)
    {
        $userId = session('user_id');
        $user = User::find($userId);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User tidak ditemukan.'
            ];
        }

        if (!$user->hutang_enabled) {
            return [
                'success' => false,
                'message' => 'Fitur hutang tidak aktif untuk akun Anda. Silakan hubungi admin.'
            ];
        }

        $availableHutang = $user->hutang_limit - $user->hutang_used;
        if ($availableHutang < $total) {
            return [
                'success' => false,
                'message' => 'Limit hutang tidak mencukupi. Sisa limit: Rp ' . 
                            number_format($availableHutang, 0, ',', '.') . 
                            ', Total: Rp ' . number_format($total, 0, ',', '.')
            ];
        }

        DB::beginTransaction();
        try {
            $user->hutang_used += $total;
            $user->save();

            $pending = PendingPayment::where('order_id', $orderId)->first();
            if ($pending) {
                $pending->status = 'completed';
                $pending->save();
            }

            $orderIds = [];
            $dueDate = $this->calculateDueDate();

            foreach ($carts as $cartData) {
                if (is_object($cartData)) {
                    $cartArray = $cartData->toArray();
                } else {
                    $cartArray = $cartData;
                }

                $uniqueOrderId = $orderId . '-' . strtoupper(substr(uniqid(), -4));

                $tanggalMenu = null;
                
                if (isset($cartArray['menu']['tanggal'])) {
                    $tanggalMenu = $cartArray['menu']['tanggal'];
                } elseif (isset($cartArray['tanggal'])) {
                    $tanggalMenu = $cartArray['tanggal'];
                }
                
                if (!$tanggalMenu) {
                    $tanggalMenu = now('Asia/Jakarta');
                }
                
                if ($tanggalMenu instanceof \Carbon\Carbon) {
                    $tanggalMenu = $tanggalMenu->setTimezone('Asia/Jakarta')->toDateString();
                } elseif (is_string($tanggalMenu)) {
                    try {
                        $tanggalMenu = \Carbon\Carbon::parse($tanggalMenu, 'Asia/Jakarta')
                            ->setTimezone('Asia/Jakarta')
                            ->toDateString();
                    } catch (\Exception $e) {
                        $tanggalMenu = now('Asia/Jakarta')->toDateString();
                    }
                } else {
                    $tanggalMenu = now('Asia/Jakarta')->toDateString();
                }

                $order = Order::create([
                    'kode_order' => $uniqueOrderId,
                    'tanggal_order' => now('Asia/Jakarta'),
                    'tanggal_menu' => $tanggalMenu,
                    'id_menu' => $cartArray['menu_id'],
                    'id_customer' => $userId,
                    'paket' => $cartArray['paket'],
                    'harga_satuan' => $cartArray['price'],
                    'total_harga' => $cartArray['subtotal'],
                    'payment_status' => 'unpaid',
                    'payment_method' => 'hutang',
                    'is_hutang' => true,
                    'due_date' => $dueDate,
                    'paid_at' => null,
                ]);

                $orderIds[] = $order->id;
            }

            Cart::where('user_id', $userId)->delete();

            DB::commit();

            return [
                'success' => true,
                'payment_gateway' => 'hutang',
                'payment_type' => 'hutang',
                'order_id' => $orderId,
                'message' => 'Pesanan berhasil! Silakan bayar sebelum ' . $dueDate->format('d-m-Y'),
                'due_date' => $dueDate,
                'total' => $total,
                'sisa_hutang' => $user->hutang_limit - $user->hutang_used,
                'redirect_url' => route('customer.payment.success', ['order_id' => $orderId])
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Hutang payment error: ' . $e->getMessage());
            Log::error('Cart data: ' . json_encode($carts));
            return [
                'success' => false,
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Bayar Hutang
     */
    public function payHutang(Request $request)
    {
        if (!session()->has('user_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.'
            ], 401);
        }

        $userId = session('user_id');
        $user = User::find($userId);

        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::where('id', $request->order_id)
            ->where('id_customer', $userId)
            ->where('is_hutang', true)
            ->where('payment_status', 'unpaid')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan atau sudah dibayar.'
            ], 404);
        }

        if ($user->saldo < $order->total_harga) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo tidak mencukupi. Saldo Anda: Rp ' . 
                            number_format($user->saldo, 0, ',', '.') . 
                            ', Total: Rp ' . number_format($order->total_harga, 0, ',', '.')
            ]);
        }

        DB::beginTransaction();
        try {
            $user->saldo -= $order->total_harga;
            $user->hutang_used -= $order->total_harga;
            $user->save();

            $order->payment_status = 'paid';
            $order->paid_at = now();
            $order->save();

            Report::create([
                'tanggal_laporan' => now(),
                'keterangan' => 'Pembayaran Hutang: ' . $order->kode_order . ' - ' . $user->name,
                'debit' => $order->total_harga,
                'kredit' => 0,
                'saldo' => $order->total_harga,
                'kategori' => 'Pendapatan',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran hutang berhasil!',
                'data' => [
                    'order_id' => $order->id,
                    'kode_order' => $order->kode_order,
                    'total' => $order->total_harga,
                    'sisa_saldo' => $user->saldo,
                    'sisa_hutang' => $user->hutang_limit - $user->hutang_used
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pay hutang error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membayar hutang: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process Payment dengan Saldo
     */
    private function processSaldo($payment, $carts, $total, $orderId)
    {
        $userId = session('user_id');
        $user = User::find($userId);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User tidak ditemukan.'
            ];
        }

        if ($user->saldo < $total) {
            return [
                'success' => false,
                'message' => 'Saldo tidak mencukupi. Saldo Anda: Rp ' . number_format($user->saldo, 0, ',', '.') . 
                            ', Total: Rp ' . number_format($total, 0, ',', '.')
            ];
        }

        DB::beginTransaction();
        try {
            $user->saldo -= $total;
            $user->save();

            $pending = PendingPayment::where('order_id', $orderId)->first();
            if ($pending) {
                $pending->status = 'completed';
                $pending->save();
            }

            $orderIds = [];
            foreach ($carts as $cartData) {
                if (is_object($cartData)) {
                    $cartArray = $cartData->toArray();
                } else {
                    $cartArray = $cartData;
                }

                $uniqueOrderId = $orderId . '-' . strtoupper(substr(uniqid(), -4));

                $tanggalMenu = null;
            
                if (isset($cartArray['menu']['tanggal'])) {
                    $tanggalMenu = $cartArray['menu']['tanggal'];
                } elseif (isset($cartArray['tanggal'])) {
                    $tanggalMenu = $cartArray['tanggal'];
                }
                
                if (!$tanggalMenu) {
                    $tanggalMenu = now('Asia/Jakarta');
                }
                
                if ($tanggalMenu instanceof \Carbon\Carbon) {
                    $tanggalMenu = $tanggalMenu->setTimezone('Asia/Jakarta')->toDateString();
                } elseif (is_string($tanggalMenu)) {
                    try {
                        $tanggalMenu = \Carbon\Carbon::parse($tanggalMenu, 'Asia/Jakarta')
                            ->setTimezone('Asia/Jakarta')
                            ->toDateString();
                    } catch (\Exception $e) {
                        $tanggalMenu = now('Asia/Jakarta')->toDateString();
                    }
                } else {
                    $tanggalMenu = now('Asia/Jakarta')->toDateString();
                }

                $order = Order::create([
                    'kode_order' => $uniqueOrderId,
                    'tanggal_order' => now(),
                    'tanggal_menu' => $tanggalMenu,
                    'id_menu' => $cartArray['menu_id'],
                    'id_customer' => $userId,
                    'paket' => $cartArray['paket'],
                    'harga_satuan' => $cartArray['price'],
                    'total_harga' => $cartArray['subtotal'],
                    'payment_status' => 'paid',
                    'payment_method' => 'saldo',
                    'payment_id' => $payment->id,
                    'paid_at' => now(),
                ]);

                $orderIds[] = $order->id;

                Report::create([
                    'tanggal_laporan' => now(),
                    'keterangan' => 'Pembayaran dengan Saldo: ' . $order->kode_order . ' - ' . $user->name,
                    'debit' => $order->total_harga,
                    'kredit' => 0,
                    'saldo' => $user->saldo,
                    'kategori' => 'Pendapatan',
                ]);
            }

            Cart::where('user_id', $userId)->delete();

            DB::commit();

            return [
                'success' => true,
                'payment_gateway' => 'saldo',
                'payment_type' => 'saldo',
                'order_id' => $orderId,
                'message' => 'Pembayaran dengan saldo berhasil!',
                'sisa_saldo' => $user->saldo,
                'redirect_url' => route('customer.payment.success', ['order_id' => $orderId]),
                'user' => $user
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Saldo payment error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Process Cash Payment dengan QR Code
     */
    private function processCash($payment, $carts, $total, $orderId)
    {
        $user = $this->getAuthUser();
        
        $qrData = json_encode([
            'order_id' => $orderId,
            'total' => $total,
            'customer' => $user->name,
            'date' => now('Asia/Jakarta')->toDateTimeString(),
            'type' => 'cash_payment'
        ]);
        
        try {
            $qrCodeSvg = QrCode::format('svg')
                ->size(300)
                ->margin(10)
                ->errorCorrection('H')
                ->generate($qrData);
            
            $qrCodeString = (string) $qrCodeSvg;
            $qrImageBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrCodeString);
            
            $pending = PendingPayment::where('order_id', $orderId)->first();
            if ($pending) {
                $pending->qr_code = $qrData;
                $pending->qr_image = $qrImageBase64;
                $pending->qr_path = 'qrcodes/cash_' . $orderId . '.svg';
                $pending->save();
            }
            
            session()->put('cash_qr_data', [
                'order_id' => $orderId,
                'qr_svg' => $qrCodeString,
                'qr_image' => $qrImageBase64,
                'total' => 'Rp ' . number_format($total, 0, ',', '.'),
                'expires_at' => now('Asia/Jakarta')->addHours(1)->toDateTimeString(),
            ]);
            
            return [
                'success' => true,
                'payment_gateway' => 'cash',
                'payment_type' => 'cash',
                'order_id' => $orderId,
                'redirect_url' => route('cash.qr.show', $orderId),
                'message' => 'Silakan lihat QR Code untuk pembayaran tunai',
                'status' => 'pending',
                'expires_at' => now('Asia/Jakarta')->addHours(1)->toDateTimeString(),
            ];
            
        } catch (\Exception $e) {
            Log::error('QR Code generation error: ' . $e->getMessage());
            
            return [
                'success' => true,
                'payment_gateway' => 'cash',
                'payment_type' => 'cash',
                'order_id' => $orderId,
                'total' => 'Rp ' . number_format($total, 0, ',', '.'),
                'qr_code' => $qrData,
                'qr_image' => null,
                'qr_svg' => null,
                'message' => 'Silakan tunjukkan kode ini ke kasir: ' . $orderId,
                'status' => 'pending',
                'expires_at' => now('Asia/Jakarta')->addHours(1)->toDateTimeString(),
                'fallback' => true,
            ];
        }
    }
    
    private function processMidtrans($payment, $carts, $total, $orderId, $paymentMethod)
    {
        $user = $this->getAuthUser();
        $clientKey = $payment->api_key;
        $serverKey = $payment->api_secret;

        if (empty($clientKey) || empty($serverKey)) {
            return [
                'success' => false,
                'message' => 'Client Key atau Server Key Midtrans belum diatur!'
            ];
        }

        $isSandbox = (bool) $payment->is_sandbox;
        $snapUrl = $isSandbox 
            ? 'https://app.sandbox.midtrans.com/snap/v1/transactions' 
            : 'https://app.midtrans.com/snap/v1/transactions';

        // ===== 🔥 GENERATE ORDER ID UNIK =====
        // Tambahkan timestamp dan random string agar UNIK
        $uniqueOrderId = $orderId . '-' . time() . '-' . strtoupper(substr(uniqid(), -4));
        
        Log::info('MIDTRANS - Generating Unique Order ID', [
            'original_order_id' => $orderId,
            'unique_order_id' => $uniqueOrderId
        ]);

        // ===== SEMUA METODE PEMBAYARAN MIDTRANS =====
        $allMidtransMethods = [
            'credit_card',
            'gopay',
            'shopeepay',
            'other_qris',
            'bank_transfer',
            'echannel',
            'bca_klikpay',
            'bca_klikbca',
            'bri_epay',
            'cimb_clicks',
            'danamon_online',
            'permata_va',
            'bca_va',
            'bni_va',
            'bri_va',
            'mandiri_va',
            'cstore',
            'alfamart',
            'indomaret',
            'akulaku',
            'uob_ezpay',
            'bnc',
            'kredivo',
            'h2h',
        ];

        $enabledPayments = $allMidtransMethods;

        $params = [
            'transaction_details' => [
                'order_id' => $uniqueOrderId, // 🔥 PAKAI UNIQUE ORDER ID
                'gross_amount' => (int) $total,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email ?? 'customer@example.com',
                'phone' => $user->no_hp ?? '08123456789',
            ],
            'item_details' => [],
            'enabled_payments' => $enabledPayments,
            'callbacks' => [
                'finish' => route('customer.payment.success', ['order_id' => $orderId]),
                'error' => route('customer.payment.failed'),
                'pending' => route('customer.payment.failed'),
            ],
        ];

        // ===== ITEM DETAILS =====
        foreach ($carts as $cart) {
            if (is_array($cart)) {
                $menuName = $cart['menu']['nama_menu'] ?? 'Menu';
                $paket = $cart['paket'] ?? 'Standar';
                $price = $cart['price'] ?? 0;
                $quantity = $cart['quantity'] ?? 1;
                $menuId = $cart['menu_id'] ?? 0;
            } else {
                $menuName = $cart->menu->nama_menu ?? 'Menu';
                $paket = $cart->paket ?? 'Standar';
                $price = $cart->price ?? 0;
                $quantity = $cart->quantity ?? 1;
                $menuId = $cart->menu_id ?? 0;
            }
            
            $params['item_details'][] = [
                'id' => $menuId,
                'price' => (int) $price,
                'quantity' => (int) $quantity,
                'name' => substr($menuName . ' (' . $paket . ')', 0, 50),
            ];
        }

        // ===== KONFIGURASI KHUSUS =====
        if ($paymentMethod === 'qris') {
            $params['qris'] = [
                'acquirer' => 'gopay',
            ];
        }

        Log::info('MIDTRANS - Request Params', [
            'order_id' => $uniqueOrderId,
            'params' => json_encode($params)
        ]);

        try {
            $response = Http::withOptions([
                'verify' => !app()->isLocal(),
                'timeout' => 30,
            ])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
            ])
            ->post($snapUrl, $params);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('MIDTRANS - Success Response', [
                    'order_id' => $uniqueOrderId,
                    'token' => $data['token'] ?? 'TIDAK ADA',
                    'redirect_url' => $data['redirect_url'] ?? 'TIDAK ADA'
                ]);

                $pendingId = session()->get('pending_payment_id');
                if ($pendingId) {
                    PendingPayment::where('id', $pendingId)->update([
                        'snap_token' => $data['token'] ?? null,
                        'status' => 'processing',
                        'midtrans_order_id' => $uniqueOrderId, // Simpan unique order ID
                        'payment_url' => $data['redirect_url'] ?? null,
                    ]);
                }
                
                return [
                    'success' => true,
                    'payment_gateway' => 'midtrans',
                    'payment_type' => $paymentMethod,
                    'order_id' => $orderId,
                    'midtrans_order_id' => $uniqueOrderId, // Kirim unique order ID
                    'token' => $data['token'] ?? null,
                    'client_key' => $clientKey,
                    'redirect_url' => $data['redirect_url'] ?? null,
                    'finish_url' => route('customer.payment.success', ['order_id' => $orderId]),
                    'error_url' => route('customer.payment.failed'),
                ];
            }

            // Handle error
            $errorBody = $response->body();
            Log::error('MIDTRANS - Error Response', [
                'order_id' => $uniqueOrderId,
                'status' => $response->status(),
                'body' => $errorBody
            ]);

            $errorMessage = 'Gagal memproses pembayaran.';
            $errorData = json_decode($errorBody, true);
            
            // ===== CEK ERROR ORDER ID SUDAH TERPAKAI =====
            if (isset($errorData['error_messages']) && is_array($errorData['error_messages'])) {
                $errorMessage = implode(' ', $errorData['error_messages']);
                
                // Jika error karena order_id sudah terpakai, coba generate ulang
                if (strpos($errorMessage, 'order_id has already been taken') !== false) {
                    Log::warning('MIDTRANS - Order ID taken, retrying with new ID');
                    
                    // Generate order ID baru dengan timestamp + random
                    $newOrderId = $orderId . '-' . time() . '-' . rand(1000, 9999);
                    
                    // Update params dengan order ID baru
                    $params['transaction_details']['order_id'] = $newOrderId;
                    
                    // Retry request
                    $retryResponse = Http::withOptions([
                        'verify' => !app()->isLocal(),
                        'timeout' => 30,
                    ])
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
                    ])
                    ->post($snapUrl, $params);
                    
                    if ($retryResponse->successful()) {
                        $data = $retryResponse->json();
                        
                        $pendingId = session()->get('pending_payment_id');
                        if ($pendingId) {
                            PendingPayment::where('id', $pendingId)->update([
                                'snap_token' => $data['token'] ?? null,
                                'status' => 'processing',
                                'midtrans_order_id' => $newOrderId,
                                'payment_url' => $data['redirect_url'] ?? null,
                            ]);
                        }
                        
                        return [
                            'success' => true,
                            'payment_gateway' => 'midtrans',
                            'payment_type' => $paymentMethod,
                            'order_id' => $orderId,
                            'midtrans_order_id' => $newOrderId,
                            'token' => $data['token'] ?? null,
                            'client_key' => $clientKey,
                            'redirect_url' => $data['redirect_url'] ?? null,
                            'finish_url' => route('customer.payment.success', ['order_id' => $orderId]),
                            'error_url' => route('customer.payment.failed'),
                        ];
                    }
                }
            }

            return [
                'success' => false,
                'message' => $errorMessage,
                'raw_response' => $errorData
            ];

        } catch (\Exception $e) {
            Log::error('MIDTRANS - Exception', [
                'order_id' => $orderId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke server pembayaran. Silakan coba lagi!',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Lanjutkan Pembayaran Pending - LANGSUNG KE GATEWAY
     */
    public function continuePayment($id)
    {
        $userId = session('user_id');
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.'
            ], 404);
        }

        $pending = PendingPayment::where('id', $id)
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing'])
            ->where('expires_at', '>', now())
            ->first();

        if (!$pending) {
            return redirect()->route('customer.pending-payments')
                ->with('error', 'Data pembayaran tidak ditemukan atau sudah kadaluwarsa.');
        }

        $payment = Payment::find($pending->payment_id);
        
        if (!$payment || !$payment->is_active) {
            return redirect()->route('customer.pending-payments')
                ->with('error', 'Metode pembayaran tidak aktif. Silakan hubungi admin.');
        }

        session()->put('last_order_id', $pending->order_id);
        session()->put('pending_payment_id', $pending->id);
        session()->put('payment_carts', $pending->cart_data);
        session()->put('payment_method', $pending->payment_method);
        session()->put('payment_id', $pending->payment_id);
        session()->put('total_amount', $pending->total);

        $carts = collect($pending->cart_data);
        $total = $pending->total;
        $orderId = $pending->order_id;
        $paymentMethod = $pending->payment_method;

        // ===== RESET SNAP_TOKEN AGAR BISA GENERATE ULANG =====
        PendingPayment::where('id', $pending->id)->update([
            'snap_token' => null,
            'midtrans_order_id' => null,
            'payment_url' => null
        ]);

        switch ($payment->gateway) {
            case 'midtrans':
                $result = $this->processMidtrans($payment, $carts, $total, $orderId, $paymentMethod);
                
                Log::info('Continue Payment - Midtrans Result', [
                    'success' => $result['success'],
                    'has_redirect_url' => isset($result['redirect_url']),
                    'has_token' => isset($result['token']),
                    'order_id' => $orderId
                ]);
                
                if ($result['success']) {
                    // Jika ada redirect_url, langsung redirect
                    if (isset($result['redirect_url']) && !empty($result['redirect_url'])) {
                        return redirect($result['redirect_url']);
                    }
                    
                    // Jika tidak ada redirect_url tapi ada token, buat URL sendiri
                    if (isset($result['token']) && !empty($result['token'])) {
                        $isSandbox = (bool) $payment->is_sandbox;
                        $baseUrl = $isSandbox 
                            ? 'https://app.sandbox.midtrans.com' 
                            : 'https://app.midtrans.com';
                        $redirectUrl = $baseUrl . '/snap/v4/redirection/' . $result['token'];
                        
                        Log::info('Continue Payment - Generated redirect_url from token', [
                            'generated_url' => $redirectUrl,
                            'token' => $result['token']
                        ]);
                        
                        return redirect($redirectUrl);
                    }
                }
                break;
                
            case 'tripay':
                $result = $this->processTripay($payment, $carts, $total, $orderId, $paymentMethod);
                if ($result['success'] && isset($result['payment_url'])) {
                    return redirect($result['payment_url']);
                }
                break;
                
            case 'xendit':
                $result = $this->processXendit($payment, $carts, $total, $orderId, $paymentMethod);
                if ($result['success'] && isset($result['redirect_url'])) {
                    return redirect($result['redirect_url']);
                }
                break;
                
            default:
                return redirect()->route('customer.pending-payments')
                    ->with('error', 'Payment gateway tidak dikenali!');
        }

        $errorMessage = $result['message'] ?? 'Gagal memproses pembayaran. Silakan coba lagi.';
        
        Log::error('Continue Payment - Failed', [
            'order_id' => $orderId,
            'message' => $errorMessage,
            'result' => $result ?? null
        ]);
        
        return redirect()->route('customer.pending-payments')
            ->with('error', $errorMessage);
    }

    /**
     * Halaman Pending Payments (Belum Dibayar)
     */
    public function pendingPayments()
    {
        $userId = session('user_id');
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.'
            ], 404);
        }

        $pendingPayments = PendingPayment::with('payment')
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing'])
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->get();

        $expiredPayments = PendingPayment::with('payment')
            ->where('user_id', $userId)
            ->where(function($query) {
                $query->where('status', 'expired')
                    ->orWhere('expires_at', '<=', now());
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('customer.pending_payment', compact('pendingPayments', 'expiredPayments', 'user'));
    }

    /**
     * Batalkan Pending Payment
     */
    public function cancelPendingPayment($id)
    {
        $userId = session('user_id');
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.'
            ], 404);
        }

        $pending = PendingPayment::where('id', $id)
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        if (!$pending) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan atau sudah tidak aktif.'
            ], 404);
        }

        $pending->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibatalkan.'
        ]);
    }
    
    /**
     * Cek Status Pembayaran (AJAX)
     */
    public function checkPaymentStatus($orderId)
    {
        if (!session()->has('user_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Silakan login terlebih dahulu.'
            ], 401);
        }
        
        $userId = $this->getAuthId();
        
        Log::info('Check payment status called', ['order_id' => $orderId]);
        
        try {
            $order = Order::where('kode_order', 'LIKE', $orderId . '%')
                ->where('id_customer', $userId)
                ->first();
            
            if ($order) {
                return response()->json([
                    'status' => 'paid',
                    'message' => 'Pembayaran berhasil',
                    'order_id' => $orderId
                ]);
            }
            
            $pending = PendingPayment::where('order_id', $orderId)
                ->where('user_id', $userId)
                ->first();
            
            if ($pending) {
                if ($pending->expires_at < now()) {
                    return response()->json([
                        'status' => 'expired',
                        'message' => 'QR Code sudah kadaluarsa',
                        'order_id' => $orderId
                    ]);
                }
                
                return response()->json([
                    'status' => 'pending',
                    'message' => 'Menunggu konfirmasi pembayaran',
                    'order_id' => $orderId,
                    'total' => $pending->total,
                    'qr_image' => $pending->qr_image,
                    'qr_code' => $pending->qr_code,
                    'expires_at' => $pending->expires_at
                ]);
            }
            
            return response()->json([
                'status' => 'not_found',
                'message' => 'Data tidak ditemukan',
                'order_id' => $orderId
            ]);
            
        } catch (\Exception $e) {
            Log::error('Check payment status error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Payment Success Callback (Redirect dari Midtrans)
     */
    public function success(Request $request)
    {
        Log::info('=== PAYMENT SUCCESS START ===');
        Log::info('Request all:', $request->all());
        Log::info('Session ID: ' . session()->getId());
        Log::info('Session all:', session()->all());

        if (!session()->has('user_id')) {
            Log::error('No user_id in session!');
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }
        
        $userId = $this->getAuthId();
        $user = $this->getAuthUser();
        
        if (!$user) {
            Log::error('User not found!');
            session()->forget(['user_id', 'user_name', 'user_role', 'user_username']);
            return redirect('/login')->with('error', 'Session tidak valid. Silakan login kembali.');
        }

        Log::info('User found:', ['user_id' => $userId, 'name' => $user->name]);
        
        // ===== 🔥 AMBIL ORDER_ID DARI REQUEST =====
        $orderIdFromRequest = $request->get('order_id');
        $orderIdFromSession = session()->get('last_order_id');
        
        Log::info('Order ID from request: ' . ($orderIdFromRequest ?? 'null'));
        Log::info('Order ID from session: ' . ($orderIdFromSession ?? 'null'));
        
        // ===== 🔥 CARI PENDING PAYMENT =====
        $pending = null;
        
        // 1. Coba cari berdasarkan order_id dari request (bisa jadi unique)
        if ($orderIdFromRequest) {
            // Cari di midtrans_order_id (unique)
            $pending = PendingPayment::where('midtrans_order_id', $orderIdFromRequest)
                ->where('user_id', $userId)
                ->first();
            
            if ($pending) {
                Log::info('Pending found by midtrans_order_id', [
                    'midtrans_order_id' => $orderIdFromRequest,
                    'pending_id' => $pending->id
                ]);
            }
            
            // Jika tidak ditemukan, coba cari di order_id (asli)
            if (!$pending) {
                $pending = PendingPayment::where('order_id', $orderIdFromRequest)
                    ->where('user_id', $userId)
                    ->first();
                
                if ($pending) {
                    Log::info('Pending found by order_id', [
                        'order_id' => $orderIdFromRequest,
                        'pending_id' => $pending->id
                    ]);
                }
            }
        }
        
        // 2. Jika masih tidak ditemukan, coba dari session
        if (!$pending && $orderIdFromSession) {
            $pending = PendingPayment::where('order_id', $orderIdFromSession)
                ->where('user_id', $userId)
                ->first();
            
            if ($pending) {
                Log::info('Pending found by session order_id', [
                    'order_id' => $orderIdFromSession,
                    'pending_id' => $pending->id
                ]);
            }
        }
        
        // 3. Jika masih tidak ditemukan, coba cari tanpa user_id (untuk berjaga-jaga)
        if (!$pending && $orderIdFromRequest) {
            $pending = PendingPayment::where('midtrans_order_id', $orderIdFromRequest)->first();
            
            if ($pending) {
                Log::info('Pending found by midtrans_order_id (without user filter)', [
                    'midtrans_order_id' => $orderIdFromRequest,
                    'pending_id' => $pending->id
                ]);
            }
        }

        Log::info('Pending payment found: ' . ($pending ? 'YES' : 'NO'));
        
        if (!$pending) {
            Log::error('No pending payment found!', [
                'order_id_from_request' => $orderIdFromRequest,
                'order_id_from_session' => $orderIdFromSession,
                'user_id' => $userId
            ]);
            return redirect()->route('customer.orders')->with('error', 'Data pembayaran tidak ditemukan!');
        }
        
        // ===== 🔥 CEK APAKAH ORDER SUDAH ADA =====
        $existingOrders = Order::where('id_customer', $userId)
            ->where('kode_order', 'LIKE', $pending->order_id . '%')
            ->get();
        
        Log::info('Existing orders count: ' . $existingOrders->count());
        
        if ($existingOrders->count() > 0) {
            Log::info('Orders already exist, returning success view');
            $this->cleanPaymentSession();
            return view('customer.payment_success', [
                'order_id' => $pending->order_id,
                'orders' => $existingOrders,
                'user' => $user
            ]);
        }
        
        Log::info('Pending payment details:', [
            'id' => $pending->id,
            'status' => $pending->status,
            'order_id' => $pending->order_id,
            'midtrans_order_id' => $pending->midtrans_order_id,
            'cart_count' => is_array($pending->cart_data) ? count($pending->cart_data) : 0,
            'total' => $pending->total,
        ]);
        
        $carts = $pending->cart_data;
        
        if (empty($carts) || !is_array($carts)) {
            Log::error('No carts in pending payment!');
            return redirect()->route('customer.orders')->with('error', 'Data keranjang tidak ditemukan!');
        }
        
        DB::beginTransaction();
        try {
            $orderIds = [];
            $paymentMethod = $pending->payment_method;
            $paymentId = $pending->payment_id;
            $baseOrderId = $pending->order_id; // Gunakan order_id asli dari database
            
            Log::info('Saving orders...', [
                'base_order_id' => $baseOrderId,
                'payment_method' => $paymentMethod,
                'payment_id' => $paymentId,
                'total_items' => count($carts)
            ]);
            
            foreach ($carts as $index => $cartData) {
                $cartArray = is_object($cartData) ? $cartData->toArray() : $cartData;

                $tanggalMenu = $cartArray['menu']['tanggal'] ?? null;
                
                if ($tanggalMenu) {
                    if ($tanggalMenu instanceof \Carbon\Carbon) {
                        $tanggalMenu = $tanggalMenu->setTimezone('Asia/Jakarta')->toDateString();
                    } elseif (is_string($tanggalMenu)) {
                        $tanggalMenu = \Carbon\Carbon::parse($tanggalMenu, 'Asia/Jakarta')
                            ->setTimezone('Asia/Jakarta')
                            ->toDateString();
                    }
                } else {
                    $tanggalMenu = now('Asia/Jakarta')->toDateString();
                }
                
                // ===== 🔥 GENERATE UNIQUE ORDER ID UNTUK SETIAP ITEM =====
                $uniqueOrderId = $baseOrderId . '-' . strtoupper(substr(uniqid(), -4));
                
                $order = Order::create([
                    'kode_order' => $uniqueOrderId,
                    'tanggal_order' => now(),
                    'tanggal_menu' => $tanggalMenu,
                    'id_menu' => $cartArray['menu_id'],
                    'id_customer' => $userId,
                    'paket' => $cartArray['paket'] ?? 'Standar',
                    'harga_satuan' => $cartArray['price'] ?? 0,
                    'total_harga' => $cartArray['subtotal'] ?? 0,
                    'payment_status' => 'paid',
                    'payment_method' => $paymentMethod,
                    'payment_id' => $paymentId,
                    'paid_at' => now(),
                ]);
                
                $orderIds[] = $order->id;
                
                Log::info('Order saved', [
                    'order_id' => $order->id, 
                    'kode_order' => $order->kode_order
                ]);
                
                Report::create([
                    'tanggal_laporan' => now(),
                    'keterangan' => 'Pembayaran Order: ' . $order->kode_order . ' - ' . $user->name,
                    'debit' => $order->total_harga,
                    'kredit' => 0,
                    'saldo' => $order->total_harga,
                    'kategori' => 'Pendapatan',
                ]);
            }
            
            Cart::where('user_id', $userId)->delete();
            Log::info('Cart deleted for user: ' . $userId);
            
            $pending->update([
                'status' => 'completed',
            ]);
            Log::info('Pending payment updated to completed');
            
            DB::commit();
            
            Log::info('=== PAYMENT SUCCESS COMPLETED ===', ['order_ids' => $orderIds]);
            
            $this->cleanPaymentSession();
            
            $orders = Order::whereIn('id', $orderIds)->get();
            
            return view('customer.payment_success', [
                'order_id' => $baseOrderId,
                'orders' => $orders,
                'user' => $user
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('=== PAYMENT SUCCESS FAILED ===');
            Log::error('Error: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return redirect()->route('customer.orders')->with('error', 'Gagal menyimpan pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Clean payment session
     */
    private function cleanPaymentSession()
    {
        session()->forget([
            'pending_payment_id',
            'last_order_id',
            'payment_carts',
            'payment_method',
            'payment_id',
            'total_amount',
            'snap_token'
        ]);
    }

    /**
     * Payment Failed Callback
     */
    public function failed(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }
        
        $userId = $this->getAuthId();
        
        $pendingId = session()->get('pending_payment_id');
        if ($pendingId) {
            PendingPayment::where('id', $pendingId)
                ->where('user_id', $userId)
                ->update(['status' => 'failed']);
        }
        
        $this->cleanPaymentSession();
        
        return view('customer.payment_failed');
    }

    /**
     * Download Receipt
     */
    public function downloadReceipt($orderId)
    {
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }
        
        $userId = $this->getAuthId();
        
        $orders = Order::where('kode_order', 'LIKE', $orderId . '%')
            ->where('id_customer', $userId)
            ->with('menu')
            ->get();
        
        if ($orders->count() == 0) {
            return redirect()->route('customer.orders')->with('error', 'Pesanan tidak ditemukan');
        }
        
        $total = $orders->sum('total_harga');
        $order_id = $orderId;
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('customer.receipt_pdf', compact('orders', 'total', 'order_id'));
        return $pdf->download('Bukti_Transaksi_' . $orderId . '.pdf');
    }

    /**
     * Handle Midtrans Webhook
     */
    public function webhookMidtrans(Request $request)
    {
        $data = $request->all();
        $orderId = $data['order_id'] ?? null;
        $status = $data['transaction_status'] ?? null;

        Log::info('Midtrans Webhook received', ['order_id' => $orderId, 'status' => $status, 'data' => $data]);

        if ($orderId && ($status == 'capture' || $status == 'settlement')) {
            $pending = PendingPayment::where('order_id', $orderId)
                ->where('status', 'pending')
                ->first();
                
            if (!$pending) {
                Log::warning('No pending payment found for webhook', ['order_id' => $orderId]);
                return response()->json(['status' => 'no_data']);
            }
            
            $existingOrders = Order::where('kode_order', 'LIKE', $orderId . '%')
                ->where('id_customer', $pending->user_id)
                ->get();
                
            if ($existingOrders->count() > 0) {
                $pending->update(['status' => 'completed']);
                Log::info('Orders already saved, updated webhook status', ['order_id' => $orderId]);
                return response()->json(['status' => 'already_saved']);
            }
            
            DB::beginTransaction();
            try {
                $carts = $pending->cart_data;
                $user = $pending->user;
                
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
                        'payment_method' => $pending->payment_method,
                        'payment_id' => $pending->payment_id,
                        'paid_at' => now(),
                    ]);
                    
                    Report::create([
                        'tanggal_laporan' => now(),
                        'keterangan' => 'Pembayaran Order: ' . $order->kode_order . ' - ' . ($user->name ?? 'Customer'),
                        'debit' => $order->total_harga,
                        'kredit' => 0,
                        'saldo' => $order->total_harga,
                        'kategori' => 'Pendapatan',
                    ]);
                }
                
                Cart::where('user_id', $pending->user_id)->delete();
                
                $pending->update(['status' => 'completed']);
                
                DB::commit();
                Log::info('Orders saved via webhook', ['order_id' => $orderId]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to save order via webhook: ' . $e->getMessage());
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Process Tripay Payment
     */
    private function processTripay($payment, $carts, $total, $orderId, $paymentMethod)
    {
        return [
            'success' => false,
            'message' => 'Tripay not implemented yet'
        ];
    }

    /**
     * Process Xendit Payment
     */
    private function processXendit($payment, $carts, $total, $orderId, $paymentMethod)
    {
        return [
            'success' => false,
            'message' => 'Xendit not implemented yet'
        ];
    }
}