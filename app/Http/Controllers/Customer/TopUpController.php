<?php
// app/Http/Controllers/Customer/TopUpController.php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\TopUp;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TopUpController extends Controller
{
    /**
     * Halaman TopUp
     */
    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $userId = session('user_id');
        $user = User::find($userId);

        if (!$user) {
            return redirect('/login')->with('error', 'User tidak ditemukan.');
        }

        $topups = TopUp::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('customer.topup.index', compact('user', 'topups'));
    }

    /**
     * Form TopUp
     */
    public function create()
    {
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = User::find(session('user_id'));

        // Ambil payment gateway Midtrans
        $midtrans = Payment::where('gateway', 'midtrans')
            ->where('is_active', true)
            ->first();

        return view('customer.topup.create', compact('user', 'midtrans'));
    }

    /**
     * Proses TopUp via Midtrans
     */
    public function store(Request $request)
    {
        if (!session()->has('user_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'nominal' => 'required|numeric|min:10000|max:10000000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = session('user_id');
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.'
            ], 404);
        }

        // Ambil payment gateway Midtrans
        $payment = Payment::where('gateway', 'midtrans')
            ->where('is_active', true)
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment gateway Midtrans tidak aktif. Silakan hubungi admin.'
            ], 400);
        }

        $paymentMethod = $request->payment_method ?? 'qris';

        DB::beginTransaction();
        try {
            // Generate kode topup
            $kodeTopup = TopUp::generateCode();
            $orderId = 'TP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

            // Create topup
            $topup = TopUp::create([
                'user_id' => $userId,
                'kode_topup' => $kodeTopup,
                'nominal' => $request->nominal,
                'metode_pembayaran' => 'midtrans',
                'status' => 'pending',
                'expires_at' => now()->addHours(24),
            ]);

            // Proses pembayaran ke Midtrans
            $result = $this->processMidtransPayment($payment, $topup, $user, $paymentMethod);

            if (!$result['success']) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

            // Update topup dengan data Midtrans
            $topup->payment_token = $result['token'];
            $topup->payment_url = $result['redirect_url'];
            $topup->midtrans_order_id = $result['order_id'];
            $topup->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Silakan selesaikan pembayaran melalui Midtrans',
                'data' => [
                    'topup' => $topup,
                    'payment_url' => $result['redirect_url'],
                    'payment_token' => $result['token'],
                    'payment_type' => $paymentMethod,
                    'qr_code' => $result['qr_code'] ?? null,
                    'qr_image' => $result['qr_image'] ?? null,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('TopUp error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses topup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Proses pembayaran ke Midtrans
     */
    private function processMidtransPayment($payment, $topup, $user, $paymentMethod = 'qris')
    {
        $serverKey = $payment->api_secret;
        $clientKey = $payment->api_key;

        if (empty($clientKey) || empty($serverKey)) {
            return [
                'success' => false,
                'message' => 'Client Key atau Server Key Midtrans belum diatur!'
            ];
        }

        // ===== TAMBAHKAN: URL berdasarkan environment =====
        $isSandbox = (bool) ($payment->is_sandbox ?? true);
        $snapUrl = $isSandbox 
            ? 'https://app.sandbox.midtrans.com/snap/v1/transactions' 
            : 'https://app.midtrans.com/snap/v1/transactions';  // <-- PRODUCTION URL

        // Log untuk debug
        Log::info('Midtrans TopUp Request', [
            'order_id' => $orderId ?? 'not_generated',
            'is_sandbox' => $isSandbox,
            'snap_url' => $snapUrl,
            'server_key_prefix' => substr($serverKey, 0, 15) . '...',
            'nominal' => $topup->nominal,
            'payment_method' => $paymentMethod
        ]);

        $orderId = 'TOPUP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

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

        // ===== KONFIGURASI METODE PEMBAYARAN =====
        $enabledPayments = $allMidtransMethods;
        
        switch ($paymentMethod) {
            case 'qris':
                $enabledPayments = ['other_qris'];
                break;
            case 'bank_transfer':
                $enabledPayments = ['bank_transfer'];
                break;
            case 'ewallet':
                $enabledPayments = ['gopay', 'shopeepay', 'dana', 'ovo'];
                break;
            case 'credit_card':
                $enabledPayments = ['credit_card'];
                break;
            default:
                $enabledPayments = ['qris'];
        }

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $topup->nominal,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email ?? 'customer@example.com',
                'phone' => $user->no_hp ?? '08123456789',
            ],
            'item_details' => [
                [
                    'id' => 'TOPUP-' . $topup->id,
                    'price' => (int) $topup->nominal,
                    'quantity' => 1,
                    'name' => 'TopUp Saldo - ' . $topup->kode_topup,
                ]
            ],
            'enabled_payments' => $enabledPayments,
            'callbacks' => [
                'finish' => route('customer.topup.callback'),
                'error' => route('customer.topup.failed'),
                'pending' => route('customer.topup.failed'),
            ],
        ];

        // ===== TAMBAHKAN: QRIS Specific Parameters =====
        if ($paymentMethod === 'qris') {
            $params['qris'] = [
                'acquirer' => 'gopay',
                'payment_type' => 'dynamic_qr',
            ];
        }

        // ===== TAMBAHKAN: Bank Transfer Specific =====
        if ($paymentMethod === 'bank_transfer') {
            $params['bank_transfer'] = [
                'bank' => 'bca',
                'va_number' => null,
            ];
        }

        try {
            // ===== PERBAIKAN: Gunakan URL yang benar =====
            $response = Http::withOptions([
                'verify' => !app()->isLocal(),
                'timeout' => 30,
            ])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
            ])
            ->post($snapUrl, $params);  // <-- PASTIKAN URL INI

            // Log response
            Log::info('Midtrans TopUp Response', [
                'order_id' => $orderId,
                'status' => $response->status(),
                'success' => $response->successful()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($paymentMethod === 'qris' && isset($data['actions'])) {
                    Log::info('QRIS Response', [
                        'order_id' => $orderId,
                        'qr_string' => $data['qr_string'] ?? null,
                        'qr_url' => $this->getQRCodeUrl($data)
                    ]);
                }

                return [
                    'success' => true,
                    'token' => $data['token'],
                    'redirect_url' => $data['redirect_url'],
                    'order_id' => $orderId,
                    'payment_type' => $paymentMethod,
                    'qr_code' => $data['qr_string'] ?? null,
                    'qr_image' => $this->getQRCodeUrl($data),
                ];
            }

            // ===== ERROR HANDLING LEBIH BAIK =====
            $errorBody = $response->body();
            Log::error('Midtrans TopUp Error', [
                'order_id' => $orderId,
                'status' => $response->status(),
                'body' => $errorBody
            ]);

            $errorMessage = 'Gagal memproses pembayaran.';
            $errorData = json_decode($errorBody, true);
            if (isset($errorData['error_messages']) && is_array($errorData['error_messages'])) {
                $errorMessage = implode(' ', $errorData['error_messages']);
            } elseif (isset($errorData['message'])) {
                $errorMessage = $errorData['message'];
            }

            return [
                'success' => false,
                'message' => $errorMessage
            ];

        } catch (\Exception $e) {
            Log::error('Midtrans Exception: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke server pembayaran. Silakan coba lagi!'
            ];
        }
    }

    /**
     * Get QR Code URL from Midtrans response
     */
    private function getQRCodeUrl($data)
    {
        if (isset($data['actions']) && is_array($data['actions'])) {
            foreach ($data['actions'] as $action) {
                if ($action['name'] === 'generate-qr-code') {
                    return $action['url'] ?? null;
                }
            }
        }
        return null;
    }

    /**
     * Callback dari Midtrans (Success)
     */
    public function callback(Request $request)
    {
        $orderId = $request->get('order_id');
        
        Log::info('TopUp Callback', [
            'order_id' => $orderId,
            'all' => $request->all()
        ]);

        // Cari topup berdasarkan midtrans_order_id
        $topup = TopUp::where('midtrans_order_id', $orderId)->first();

        if (!$topup) {
            Log::warning('TopUp not found for callback', ['order_id' => $orderId]);
            return redirect()->route('customer.topup.index')
                ->with('error', 'Data topup tidak ditemukan');
        }

        // Cek status pembayaran ke Midtrans
        $payment = Payment::where('gateway', 'midtrans')
            ->where('is_active', true)
            ->first();

        if (!$payment) {
            return redirect()->route('customer.topup.index')
                ->with('error', 'Payment gateway tidak aktif');
        }

        $status = $this->checkPaymentStatus($payment, $orderId);

        if ($status == 'settlement' || $status == 'capture') {
            // Pembayaran berhasil
            if ($topup->status != 'verified') {
                DB::beginTransaction();
                try {
                    // Tambah saldo user
                    $user = User::find($topup->user_id);
                    $user->saldo += $topup->nominal;
                    $user->saldo_total += $topup->nominal;
                    $user->save();

                    // Update topup
                    $topup->status = 'verified';
                    $topup->paid_at = now();
                    $topup->save();

                    DB::commit();

                    Log::info('TopUp verified via callback', [
                        'topup_id' => $topup->id,
                        'user_id' => $topup->user_id,
                        'nominal' => $topup->nominal
                    ]);

                    return redirect()->route('customer.topup.index')
                        ->with('success', 'TopUp berhasil! Saldo Anda bertambah Rp ' . number_format($topup->nominal, 0, ',', '.'));

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('TopUp callback error: ' . $e->getMessage());
                    return redirect()->route('customer.topup.index')
                        ->with('error', 'Gagal memproses topup: ' . $e->getMessage());
                }
            }
        } elseif ($status == 'pending') {
            return redirect()->route('customer.topup.index')
                ->with('info', 'Pembayaran masih pending. Silakan tunggu konfirmasi.');
        } else {
            $topup->status = 'expired';
            $topup->save();
            return redirect()->route('customer.topup.index')
                ->with('error', 'Pembayaran gagal atau kadaluarsa.');
        }

        return redirect()->route('customer.topup.index');
    }

    /**
     * Cek status pembayaran ke Midtrans
     */
    private function checkPaymentStatus($payment, $orderId)
    {
        $serverKey = $payment->api_secret;
        $isSandbox = $payment->is_sandbox;
        $baseUrl = $isSandbox 
            ? 'https://api.sandbox.midtrans.com' 
            : 'https://api.midtrans.com';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
                'Accept' => 'application/json',
            ])->get("{$baseUrl}/v2/{$orderId}/status");

            if ($response->successful()) {
                $data = $response->json();
                return $data['transaction_status'] ?? 'unknown';
            }

            return 'unknown';

        } catch (\Exception $e) {
            Log::error('Check payment status error: ' . $e->getMessage());
            return 'unknown';
        }
    }

    /**
     * Failed callback
     */
    public function failed(Request $request)
    {
        return redirect()->route('customer.topup.index')
            ->with('error', 'Pembayaran dibatalkan atau gagal.');
    }

    /**
     * Detail TopUp
     */
    public function detail($id)
    {
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $userId = session('user_id');
        $user = User::find($userId);

        if (!$user) {
            return redirect('/login')->with('error', 'User tidak ditemukan.');
        }

        // Cari topup milik user
        $topup = TopUp::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Cek expired jika masih pending
        if ($topup->status == 'pending' && $topup->created_at < now()->subHours(24)) {
            $topup->status = 'expired';
            $topup->save();
        }

        return view('customer.topup.detail', compact('topup', 'user'));
    }
}