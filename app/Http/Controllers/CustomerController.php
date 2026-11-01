<?php
// app/Http/Controllers/CustomerController.php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PendingPayment;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
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
     * Halaman Profile Customer
     */
    public function profile()
    {
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }
        
        $userId = session('user_id');
        $user = User::find($userId);
        
        if (!$user) {
            session()->forget(['user_id', 'user_name', 'user_role', 'user_username']);
            return redirect('/login')->with('error', 'Session tidak valid. Silakan login kembali.');
        }
        
        return view('customer.profile', compact('user'));
    }

    /**
     * Update Profile Customer
     */
    public function updateProfile(Request $request)
    {
        if (!session()->has('user_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.'
            ], 401);
        }
        
        $userId = session('user_id');
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'no_hp' => [
                'required',
                'string',
                'max:15',
                'regex:/^[0-9+\-\s]+$/',
            ],
            'kelas' => 'required|string|max:2',
            'nama_kelas' => 'required|string|max:50',
            'alergi' => 'nullable|string|max:255',
        ], [
            'no_hp.regex' => 'Format nomor HP tidak valid.',
            'no_hp.max' => 'Nomor HP maksimal 15 karakter.',
            'kelas.required' => 'Kelas wajib dipilih.',
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // ===== GABUNGKAN KELAS + NAMA KELAS =====
            $kelas = $request->kelas . '-' . $request->nama_kelas;
            
            // Bersihkan no_hp
            $noHp = preg_replace('/\s+/', '', $request->no_hp);
            
            $user->update([
                'name' => $request->name,
                'no_hp' => $noHp,
                'kelas' => $kelas,
                'alergi' => $request->alergi ?? '-',
            ]);
            
            // Update session
            session(['user_name' => $user->name]);

            // Log aktivitas
            \Illuminate\Support\Facades\Log::info('Customer update profile', [
                'user_id' => $user->id,
                'username' => $user->username,
                'kelas' => $kelas,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile berhasil diupdate!',
                'data' => [
                    'name' => $user->name,
                    'no_hp' => $user->no_hp,
                    'kelas' => $user->kelas,
                    'alergi' => $user->alergi,
                ]
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Update profile error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate profile. Silakan coba lagi!'
            ], 500);
        }
    }

    /**
     * Customer Dashboard
     */
    public function dashboard()
    {
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }
        
        $userId = session('user_id');
        $user = User::find($userId);
        
        if (!$user) {
            session()->forget(['user_id', 'user_name', 'user_role', 'user_username']);
            return redirect('/login')->with('error', 'Session tidak valid. Silakan login kembali.');
        }
        
        // ===== AMBIL KELAS CUSTOMER =====
        $kelasCustomer = explode('-', $user->kelas)[0] ?? '0';
        $isKelas1 = ($kelasCustomer == '1');
        
        // ===== AMBIL MENU UNTUK 30 HARI KE DEPAN (HANYA YANG ADA DATA) =====
        $startDate = now()->startOfDay(); // Hari ini
        $endDate = now()->addDays(30)->endOfDay(); // 30 hari ke depan
        
        $menus = Menu::where('status', 'available')
            ->whereBetween('tanggal', [$startDate, $endDate]) // FILTER: 30 hari ke depan
            ->when($isKelas1, function($query) {
                // Customer kelas 1: HANYA lihat menu kelas_1
                return $query->where('tipe_menu', 'kelas_1');
            }, function($query) {
                // Customer kelas 2-12: HANYA lihat menu umum
                return $query->where('tipe_menu', 'umum');
            })
            ->orderBy('tanggal', 'asc')
            ->orderBy('tipe_menu')
            ->orderBy('nama_menu')
            ->get();

        // Group by date - HANYA TANGGAL YANG ADA MENU-NYA
        if ($menus->isNotEmpty()) {
            $menusByDate = $menus->groupBy(function($menu) {
                if ($menu->tanggal instanceof \Carbon\Carbon) {
                    return $menu->tanggal->format('Y-m-d');
                }
                return $menu->tanggal;
            });
        } else {
            $menusByDate = collect();
        }

        // Get today's date
        $today = Carbon::now()->format('Y-m-d');
        
        // Get dates for 30 days - HANYA YANG ADA MENU
        $availableDates = [];
        if ($menus->isNotEmpty()) {
            // Ambil semua tanggal unik dari menu yang ada
            $uniqueDates = $menus->pluck('tanggal')
                ->map(function($date) {
                    if ($date instanceof \Carbon\Carbon) {
                        return $date->format('Y-m-d');
                    }
                    return $date;
                })
                ->unique()
                ->sort()
                ->values();
            
            // Format tanggal untuk ditampilkan
            foreach ($uniqueDates as $date) {
                $carbonDate = Carbon::parse($date);
                $availableDates[] = [
                    'date' => $date,
                    'day' => $carbonDate->format('d/m/Y'),
                    'day_name' => $carbonDate->format('l'),
                    'is_today' => $date == $today,
                    'menu_count' => $menus->where('tanggal', $date)->count()
                ];
            }
        }

        // Get customer orders
        $orders = Order::where('id_customer', $userId)
            ->with('menu')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('customer.dashboard', compact(
            'menus', 
            'menusByDate', 
            'today', 
            'orders', 
            'user', 
            'isKelas1', 
            'availableDates'  // Ganti weekDates dengan availableDates
        ));
    }

    

    /**
     * Process Midtrans Payment
     */
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

        // ===== TAMBAHKAN: Ambil dari database =====
        $isSandbox = $payment->is_sandbox ?? true; // Default true jika null
        $baseUrl = $isSandbox 
            ? 'https://app.sandbox.midtrans.com' 
            : 'https://app.midtrans.com'; // <-- PRODUCTION URL

        // Log untuk debug
        Log::info('Midtrans Payment Request', [
            'order_id' => $orderId,
            'is_sandbox' => $isSandbox,
            'base_url' => $baseUrl,
            'server_key_prefix' => substr($serverKey, 0, 10) . '...',
            'amount' => $total,
            'payment_method' => $paymentMethod
        ]);

        $methodMapping = [
            'qris' => 'qris',
            'bank_transfer' => 'bank_transfer',
            'credit_card' => 'credit_card',
            'ewallet' => 'gopay',
            'retail' => 'cstore',
        ];

        $midtransMethod = $methodMapping[$paymentMethod] ?? 'qris';

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $total,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email ?? 'customer@example.com',
                'phone' => $user->no_hp ?? '08123456789',
            ],
            'item_details' => [],
            'enabled_payments' => [$midtransMethod],
            
            'callbacks' => [
                'finish' => route('customer.payment.success', ['order_id' => $orderId]),
                'error' => route('customer.payment.failed'),
                'pending' => route('customer.payment.failed'),
            ],
        ];

        foreach ($carts as $cart) {
            // Jika $cart adalah array (dari pending_payment)
            if (is_array($cart)) {
                $menuName = $cart['menu']['nama_menu'] ?? 'Menu';
                $paket = $cart['paket'] ?? 'Standar';
                $price = $cart['price'] ?? 0;
                $quantity = $cart['quantity'] ?? 1;
                $menuId = $cart['menu_id'] ?? 0;
            } else {
                // Jika $cart adalah object (dari Cart model)
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
                'name' => substr($menuName . ' (' . $paket . ')', 0, 50), // Max 50 chars
            ];
        }

        switch ($paymentMethod) {
            case 'bank_transfer':
                $params['bank_transfer'] = ['bank' => 'bca'];
                break;
            case 'credit_card':
                $params['credit_card'] = ['secure' => true];
                break;
            case 'ewallet':
                $params['gopay'] = [
                    'enable_callback' => true,
                    'callback_url' => route('customer.payment.success', ['order_id' => $orderId]),
                ];
                break;
            case 'retail':
                $params['cstore'] = ['store' => 'alfamart'];
                break;
        }

        try {
            // ===== PERBAIKAN: Gunakan baseUrl dari database =====
            $response = Http::withOptions([
                'verify' => !app()->isLocal(),
                'timeout' => 30,
            ])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
            ])
            ->post($baseUrl . '/snap/v1/transactions', $params); // <-- PERBAIKAN

            // Log response untuk debug
            Log::info('Midtrans Response', [
                'order_id' => $orderId,
                'status' => $response->status(),
                'success' => $response->successful()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Update pending payment dengan snap token baru
                $pendingId = session()->get('pending_payment_id');
                if ($pendingId) {
                    PendingPayment::where('id', $pendingId)->update([
                        'snap_token' => $data['token'],
                        'status' => 'processing',
                        'midtrans_order_id' => $orderId,
                    ]);
                }
                
                return [
                    'success' => true,
                    'payment_gateway' => 'midtrans',
                    'payment_type' => $paymentMethod,
                    'order_id' => $orderId,
                    'token' => $data['token'],
                    'client_key' => $clientKey,
                    'redirect_url' => $data['redirect_url'],
                    'finish_url' => route('customer.payment.success', ['order_id' => $orderId]),
                    'error_url' => route('customer.payment.failed'),
                ];
            }

            // ===== TAMBAHKAN: Error handling lebih detail =====
            $errorBody = $response->body();
            Log::error('Midtrans Error Response', [
                'order_id' => $orderId,
                'status' => $response->status(),
                'body' => $errorBody
            ]);

            // Parse error message
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
     * Tampilkan menu berdasarkan tanggal
     */
    public function menusByDate($date)
    {
        $menus = Menu::where('tanggal', $date)
            ->where('status', 'available')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $menus
        ]);
    }

    /**
     * Tambah ke keranjang
     */
    public function addToCart(Request $request)
    {
        try {
            if (!session()->has('user_id')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu.'
                ], 401);
            }
            
            $userId = session('user_id');
            $user = User::find($userId);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan.'
                ], 404);
            }

            Log::info('Add to cart called', [
                'user_id' => $userId,
                'request' => $request->all()
            ]);

            $validator = Validator::make($request->all(), [
                'menu_id' => 'required|exists:menus,id',
                'paket' => 'required|in:best,happy,jumbo,smart',
                'quantity' => 'required|integer|min:1',
                'price' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $menu = Menu::find($request->menu_id);
            
            if (!$menu) {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu tidak ditemukan'
                ], 404);
            }

            $kelasCustomer = explode('-', $user->kelas)[0] ?? '0';
            $harga = $request->price;

            $tanggalMenu = $menu->tanggal;

            // CEK ORDER
            $existingOrder = Order::where('id_customer', $userId)
                ->whereDate('tanggal_menu', $tanggalMenu)
                ->where('payment_status', '!=', 'cancelled')
                ->exists();

            if ($existingOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah memesan untuk tanggal ' . date('d-m-Y', strtotime($tanggalMenu)) . '. Hanya boleh 1 pesanan per hari!'
                ], 400);
            }

            // CEK PENDING PAYMENT
            $existingPending = PendingPayment::where('user_id', $userId)
                ->whereDate('created_at', $tanggalMenu)
                ->whereIn('status', ['pending', 'processing'])
                ->exists();
                
            if ($existingPending) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maaf, ada pesanan pending untuk tanggal ' . date('d-m-Y', strtotime($tanggalMenu)) . '. Silakan selesaikan pembayaran atau batalkan!'
                ], 400);
            }

            // CEK CART
            $existingCart = Cart::where('user_id', $userId)
                ->whereHas('menu', function($query) use ($tanggalMenu) {
                    $query->whereDate('tanggal', $tanggalMenu);
                })
                ->exists();

            if ($existingCart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah memiliki pesanan di keranjang untuk tanggal ' . date('d-m-Y', strtotime($tanggalMenu)) . '. Silakan checkout terlebih dahulu!'
                ], 400);
            }

            // CEK CART MENU SAMA
            $cart = Cart::where('user_id', $userId)
                ->where('menu_id', $request->menu_id)
                ->where('paket', $request->paket)
                ->first();

            if ($cart) {
                $cart->quantity += $request->quantity;
                $cart->subtotal = $cart->price * $cart->quantity;
                $cart->save();
                $message = 'Jumlah menu berhasil diupdate di keranjang';
            } else {
                $cart = Cart::create([
                    'user_id' => $userId,
                    'menu_id' => $request->menu_id,
                    'paket' => $request->paket,
                    'quantity' => $request->quantity,
                    'price' => $harga,
                    'subtotal' => $harga * $request->quantity,
                ]);
                $message = 'Menu berhasil ditambahkan ke keranjang';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $cart
            ]);

        } catch (\Exception $e) {
            Log::error('Add to cart error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Proses Checkout / Bayar (Dengan Validasi 1 Pesanan per Tanggal)
     */
    public function checkout(Request $request)
    {
        // Cek login
        if (!session()->has('user_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.'
            ], 401);
        }
        
        $userId = session('user_id');
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.'
            ], 404);
        }

        $carts = Cart::with('menu')
            ->where('user_id', $userId) // Ganti Auth::id()
            ->get();

        if ($carts->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang kosong'
            ], 400);
        }

        // CEK APAKAH SUDAH PESAN DI TANGGAL YANG SAMA
        foreach ($carts as $cart) {
            $tanggalMenu = $cart->menu->tanggal;
            
            $existingOrder = Order::where('id_customer', $userId) // Ganti Auth::id()
                ->whereDate('tanggal_menu', $tanggalMenu)
                ->where('payment_status', '!=', 'cancelled')
                ->exists();

            if ($existingOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah memesan untuk tanggal ' . date('d-m-Y', strtotime($tanggalMenu)) . '. Hanya boleh 1 pesanan per hari!'
                ], 400);
            }
        }

        // Buat order untuk setiap item di cart dengan KODE ORDER BERBEDA
        foreach ($carts as $cart) {
            // Generate kode order UNIK untuk setiap item
            $kodeOrder = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            
            $order = Order::create([
                'kode_order' => $kodeOrder,
                'tanggal_order' => now(),
                'tanggal_menu' => $cart->menu->tanggal,
                'id_menu' => $cart->menu_id,
                'id_customer' => $userId, // Ganti Auth::id()
                'paket' => $cart->paket,
                'harga_satuan' => $cart->price,
                'total_harga' => $cart->subtotal,
                'payment_status' => 'paid',
            ]);

            // Tambahkan ke laporan keuangan (debit)
            Report::create([
                'tanggal_laporan' => now(),
                'keterangan' => 'Pembayaran Order: ' . $kodeOrder . ' - ' . $user->name . ' (' . $cart->menu->nama_menu . ')',
                'debit' => $cart->subtotal,
                'kredit' => 0,
                'saldo' => $cart->subtotal,
                'kategori' => 'Pendapatan',
            ]);
        }

        // Hapus keranjang
        Cart::where('user_id', $userId)->delete(); // Ganti Auth::id()

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil! ' . $carts->count() . ' item telah dipesan.',
            'total_item' => $carts->count()
        ]);
    }

    /**
     * Cek apakah customer sudah pesan di tanggal tertentu (AJAX)
     */
    public function checkOrderByDate($date)
    {
        // Cek login
        if (!session()->has('user_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.'
            ], 401);
        }
        
        $userId = session('user_id');
        
        $existingOrder = Order::where('id_customer', $userId) // Ganti Auth::id()
            ->whereDate('tanggal_menu', $date)
            ->where('payment_status', '!=', 'cancelled')
            ->exists();

        $existingCart = Cart::where('user_id', $userId) // Ganti Auth::id()
            ->whereHas('menu', function($query) use ($date) {
                $query->whereDate('tanggal', $date);
            })
            ->exists();

        return response()->json([
            'success' => true,
            'has_order' => $existingOrder,
            'has_cart' => $existingCart,
            'message' => $existingOrder ? 'Anda sudah pesan untuk tanggal ini' : ($existingCart ? 'Ada pesanan di keranjang untuk tanggal ini' : 'Bisa pesan')
        ]);
    }

    /**
     * Halaman Keranjang (Cart)
     */
    public function cart()
    {
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }
        
        // Ambil user dari session
        $userId = session('user_id');
        $user = User::find($userId);
        
        if (!$user) {
            session()->forget(['user_id', 'user_name', 'user_role', 'user_username']);
            return redirect('/login')->with('error', 'Session tidak valid. Silakan login kembali.');
        }
        
        $carts = Cart::with('menu')
            ->where('user_id', $userId) // Ganti Auth::id()
            ->get();

        $total = $carts->sum('subtotal');

        return view('customer.cart', compact('carts', 'total', 'user'));
    }

    /**
     * Update Quantity di Keranjang
     */
    public function updateCartQuantity(Request $request, $id)
    {
        // Cek login
        if (!session()->has('user_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.'
            ], 401);
        }
        
        $userId = session('user_id');

        $cart = Cart::where('id', $id)
            ->where('user_id', $userId) // Ganti Auth::id()
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $cart->quantity = $request->quantity;
        $cart->subtotal = $cart->price * $cart->quantity;
        $cart->save();

        return response()->json([
            'success' => true,
            'message' => 'Jumlah berhasil diupdate',
            'data' => $cart
        ]);
    }

    /**
     * Hapus dari Keranjang
     */
    public function removeFromCart($id)
    {
        // Cek login
        if (!session()->has('user_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.'
            ], 401);
        }
        
        $userId = session('user_id');

        $cart = Cart::where('id', $id)
            ->where('user_id', $userId) // Ganti Auth::id()
            ->first();

        if ($cart) {
            $cart->delete();
            return response()->json([
                'success' => true,
                'message' => 'Item berhasil dihapus dari keranjang'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Item tidak ditemukan'
        ], 404);
    }

    /**
     * Halaman History Order (Pesanan Saya)
     */
    public function orders(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }
        
        // Ambil user dari session
        $userId = session('user_id');
        $user = User::find($userId);
        
        if (!$user) {
            session()->forget(['user_id', 'user_name', 'user_role', 'user_username']);
            return redirect('/login')->with('error', 'Session tidak valid. Silakan login kembali.');
        }
        
        $query = Order::with(['menu', 'payment'])
            ->where('id_customer', $userId) // Ganti Auth::id()
            ->orderBy('created_at', 'desc');
        
        if ($request->status && in_array($request->status, ['pending', 'paid', 'unpaid'])) {
            $query->where('payment_status', $request->status);
        }
        
        $orders = $query->paginate(10);
        
        return view('customer.order', compact('orders', 'user'));
    }

    /**
     * Detail Order
     */
    public function detail($id)
    {
        // Cek login
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }
        
        $userId = session('user_id');
        $user = User::find($userId);

        $order = Order::with(['menu', 'payment', 'customer'])
            ->where('id_customer', $userId) // Ganti Auth::id()
            ->findOrFail($id);
            
        return view('customer.order_detail', compact('order', 'user'));
    }

    /**
     * Detail Order (Alias)
     */
    public function orderDetail($id)
    {
        return $this->detail($id);
    }

    /**
     * Bayar Order (Update status jadi paid) - Untuk order yang belum dibayar
     */
    public function payOrder($id)
    {
        // Cek login
        if (!session()->has('user_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.'
            ], 401);
        }
        
        $userId = session('user_id');
        $user = User::find($userId);

        $order = Order::where('id_customer', $userId) // Ganti Auth::id()
            ->where('id', $id)
            ->where('payment_status', 'unpaid')
            ->firstOrFail();

        $order->update(['payment_status' => 'paid']);

        // Tambahkan ke laporan keuangan (debit)
        Report::create([
            'tanggal_laporan' => now(),
            'keterangan' => 'Pembayaran Order: ' . $order->kode_order . ' - ' . $user->name,
            'debit' => $order->total_harga,
            'kredit' => 0,
            'saldo' => $order->total_harga,
            'kategori' => 'Pendapatan',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil!'
        ]);
    }

    /**
     * Hapus Order (Batal) - Hanya untuk order yang belum dibayar
     */
    public function cancelOrder($id)
    {
        // Cek login
        if (!session()->has('user_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.'
            ], 401);
        }
        
        $userId = session('user_id');

        $order = Order::where('id_customer', $userId) // Ganti Auth::id()
            ->where('id', $id)
            ->where('payment_status', 'unpaid')
            ->firstOrFail();

        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil dibatalkan!'
        ]);
    }

    /**
     * Update Payment Status (untuk simulasi pembayaran)
     */
    public function updatePayment($id)
    {
        return $this->payOrder($id);
    }
}