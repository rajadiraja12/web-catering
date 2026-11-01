<?php
// app/Http/Controllers/Kasir/OrderManualController.php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\LogActivity;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderManualController extends Controller
{
    /**
     * Halaman Order Manual
     */
    public function index(Request $request)
    {
        // Cek login kasir
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $userId = session('user_id');
        $user = User::find($userId);
        
        if (!$user || $user->role !== 'kasir') {
            return redirect('/login')->with('error', 'Akses ditolak. Hanya untuk kasir.');
        }

        // ===== GENERATE 30 HARI KE DEPAN =====
        $tanggalList = collect();
        for ($i = 0; $i < 30; $i++) {
            $date = now()->addDays($i);
            $tanggalList->push($date->toDateString());
        }

        // ===== AMBIL TANGGAL YANG ADA MENU DARI DATABASE =====
        try {
            $startDate = now()->toDateString();
            $endDate = now()->addDays(29)->toDateString();
            
            // FIX: Gunakan whereDate dengan parameter array yang benar
            $tanggalMenuDB = Menu::whereIn('tipe_menu', ['kelas_1', 'umum'])
                ->whereDate('tanggal', '>=', $startDate)
                ->whereDate('tanggal', '<=', $endDate)
                ->select('tanggal')
                ->distinct()
                ->orderBy('tanggal', 'asc')
                ->get()
                ->pluck('tanggal')
                ->map(function($date) {
                    if ($date instanceof \Carbon\Carbon) {
                        return $date->toDateString();
                    }
                    if (is_string($date)) {
                        try {
                            return Carbon::parse($date)->toDateString();
                        } catch (\Exception $e) {
                            return '';
                        }
                    }
                    return '';
                })
                ->filter() // Hapus nilai kosong
                ->values() // Reset array keys
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error fetching menu dates: ' . $e->getMessage());
            $tanggalMenuDB = [];
        }

        // Gabungkan semua tanggal (30 hari) + beri tanda jika ada menu
        $tanggalMenu = collect();
        foreach ($tanggalList as $date) {
            $hasMenu = in_array($date, $tanggalMenuDB);
            $carbonDate = Carbon::parse($date);
            $tanggalMenu->push([
                'value' => $date,
                'label' => $carbonDate->format('d/m/Y') . ' (' . $carbonDate->format('l') . ')' . ($hasMenu ? ' ✅' : ''),
                'has_menu' => $hasMenu
            ]);
        }

        // Ambil menu berdasarkan tanggal (default: hari ini)
        $tanggalDefault = $request->input('tanggal', now()->toDateString());
        
        // FIX: Validasi tanggal default
        try {
            $tanggalDefault = Carbon::parse($tanggalDefault)->toDateString();
        } catch (\Exception $e) {
            $tanggalDefault = now()->toDateString();
        }
        
        // Ambil menu berdasarkan tanggal yang dipilih
        try {
            $menus = Menu::whereDate('tanggal', $tanggalDefault)
                ->whereIn('tipe_menu', ['kelas_1', 'umum'])
                ->orderBy('nama_menu')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching menus: ' . $e->getMessage());
            $menus = collect();
        }

        // Ambil customer untuk dropdown
        $customers = User::where('role', 'customer')
            ->where('status', 'Active')
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'kelas']);

        // Ambil order hari ini
        try {
            $orders = Order::with(['customer', 'menu'])
                ->whereDate('created_at', now()->toDateString())
                ->where('payment_method', 'kasir')
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching orders: ' . $e->getMessage());
            $orders = collect();
        }

        return view('kasir.order_manual', compact('menus', 'customers', 'orders', 'user', 'tanggalMenu', 'tanggalDefault'));
    }

    /**
     * Get Menu by Date (AJAX)
     */
    public function getMenuByDate(Request $request)
    {
        $tanggal = $request->query('tanggal');
        
        if (!$tanggal) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal tidak ditemukan'
            ]);
        }

        try {
            // FIX: Validasi dan parse tanggal
            $parsedDate = Carbon::parse($tanggal)->toDateString();
            
            $menus = Menu::whereDate('tanggal', $parsedDate)
                ->whereIn('tipe_menu', ['kelas_1', 'umum'])
                ->orderBy('nama_menu')
                ->get(['id', 'nama_menu', 'tipe_menu', 'harga_paket_kecil', 'harga_paket_sedang', 'harga_paket_besar', 'harga_paket_khusus']);

            return response()->json([
                'success' => true,
                'data' => $menus
            ]);
        } catch (\Exception $e) {
            Log::error('getMenuByDate error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching menus: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cetak Struk Order
     */
    public function printStruk($id)
    {
        try {
            $order = Order::with(['customer', 'menu'])
                ->whereIn('payment_method', ['kasir', 'cash'])
                ->findOrFail($id);
            
            // Format data untuk struk
            $data = [
                'order' => $order,
                'customer' => $order->customer,
                'menu' => $order->menu,
                'tanggal' => now()->format('d M Y H:i'),
                'total' => $order->total_harga,
                'bayar' => $order->total_harga,
                'kembali' => 0,
            ];
            
            return view('kasir.struk_print', compact('data'));
        } catch (\Exception $e) {
            Log::error('printStruk error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mencetak struk: ' . $e->getMessage());
        }
    }
    
    public function print($id)
    {
        try {
            $order = Order::with(['customer', 'menu'])
                ->findOrFail($id);
            
            // Format data untuk struk
            $data = [
                'order' => $order,
                'customer' => $order->customer,
                'menu' => $order->menu,
                'tanggal' => now()->format('d M Y H:i'),
                'total' => $order->total_harga,
                'bayar' => $order->total_harga,
                'kembali' => 0,
            ];
            
            return view('kasir.struk_print', compact('data'));
        } catch (\Exception $e) {
            Log::error('print error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mencetak: ' . $e->getMessage());
        }
    }

    /**
     * Cari Customer
     */
    public function searchCustomer(Request $request)
    {
        try {
            $search = $request->search;
            
            if (empty($search)) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }
            
            $customers = User::where('role', 'customer')
                ->where('status', 'Active')
                ->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('username', 'LIKE', "%{$search}%")
                      ->orWhere('no_hp', 'LIKE', "%{$search}%");
                })
                ->limit(10)
                ->get(['id', 'name', 'username', 'kelas']);

            return response()->json([
                'success' => true,
                'data' => $customers
            ]);
        } catch (\Exception $e) {
            Log::error('searchCustomer error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error searching customers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simpan Order Manual
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:users,id',
            'menu_id' => 'required|exists:menus,id',
            'paket' => 'required|in:best,happy,jumbo,smart',
            'quantity' => 'required|integer|min:1',
            'payment_status' => 'required|in:paid,unpaid',
            'catatan' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $customer = User::find($request->customer_id);
            $menu = Menu::find($request->menu_id);

            if (!$customer || !$menu) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer atau Menu tidak ditemukan!'
                ], 404);
            }

            // Tentukan harga sesuai paket
            $harga = $this->getHargaByPaket($menu, $request->paket);

            if ($harga <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Harga untuk paket ini tidak tersedia!'
                ], 400);
            }

            $totalHarga = $harga * $request->quantity;

            DB::beginTransaction();
            try {
                // Generate kode order
                $kodeOrder = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
                $hutang = ($request->payment_status == 'unpaid') ? '1' : '0';

                $order = Order::create([
                    'kode_order' => $kodeOrder,
                    'tanggal_order' => now(),
                    'tanggal_menu' => $menu->tanggal,
                    'id_menu' => $request->menu_id,
                    'id_customer' => $request->customer_id,
                    'paket' => $request->paket,
                    'harga_satuan' => $harga,
                    'total_harga' => $totalHarga,
                    'payment_status' => $request->payment_status,
                    'is_hutang' => $hutang,
                    'payment_method' => 'kasir',
                    'catatan' => $request->catatan ?? null,
                    'confirmed_by' => session('user_id'),
                    'confirmed_at' => ($request->payment_status == 'paid') ? now() : null,
                    'paid_at' => ($request->payment_status == 'paid') ? now() : null,
                ]);

                // Jika sudah dibayar, catat ke laporan
                if ($request->payment_status == 'paid') {
                    Report::create([
                        'tanggal_laporan' => now(),
                        'keterangan' => 'Pembayaran Kasir: ' . $kodeOrder . ' - ' . $customer->name . ' (' . $menu->nama_menu . ')',
                        'debit' => $totalHarga,
                        'kredit' => 0,
                        'saldo' => $totalHarga,
                        'kategori' => 'Pendapatan',
                        // 'order_id' => $order->id, // Tambahkan ini jika ada kolom order_id
                    ]);
                }

                // ===== CATAT KE LOG ACTIVITY =====
                $kasir = User::find(session('user_id'));
                
                LogActivity::create([
                    'user_id' => session('user_id'),
                    'ip' => request()->ip(),
                    'aksi' => 'create',
                    'module' => 'Order Manual',
                    'keterangan' => "Kasir {$kasir->name} membuat order manual untuk customer {$customer->name} dengan kode order {$kodeOrder}, total Rp " . number_format($totalHarga, 0, ',', '.'),
                    'old_data' => null,
                    'new_data' => [
                        // 'order_id' => $order->id,
                        'kode_order' => $kodeOrder,
                        'customer_id' => $request->customer_id,
                        'customer_name' => $customer->name,
                        'menu_id' => $request->menu_id,
                        'menu_name' => $menu->nama_menu,
                        'paket' => $request->paket,
                        'quantity' => $request->quantity,
                        'harga_satuan' => $harga,
                        'total_harga' => $totalHarga,
                        'payment_status' => $request->payment_status,
                        'tanggal_menu' => $menu->tanggal,
                    ],
                    'user_agent' => request()->userAgent(),
                ]);

                DB::commit();

                Log::info('Order manual by kasir', [
                    'kasir_id' => session('user_id'),
                    // 'order_id' => $order->id,
                    'kode_order' => $kodeOrder,
                    'customer' => $customer->name,
                    'total' => $totalHarga,
                    'payment_status' => $request->payment_status
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Order berhasil ditambahkan!',
                    'data' => $order
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Order manual error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan order: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil harga sesuai paket
     */
    private function getHargaByPaket($menu, $paket)
    {
        $paketMap = [
            'best' => 'harga_paket_kecil',
            'happy' => 'harga_paket_sedang',
            'jumbo' => 'harga_paket_besar',
            'smart' => 'harga_paket_khusus',
        ];

        $field = $paketMap[$paket] ?? 'harga_paket_kecil';
        return $menu->$field ?? 0;
    }

    /**
     * Update Payment Status
     */
    public function updatePayment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payment_status' => 'required|in:paid,unpaid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $order = Order::whereIn('payment_method', ['kasir'])
                ->with(['customer', 'menu'])
                ->findOrFail($id);

            DB::beginTransaction();
            try {
                $oldStatus = $order->payment_status;
                $order->payment_status = $request->payment_status;
                
                if ($request->payment_status == 'unpaid') {
                    $order->is_hutang = '1';
                    $order->paid_at = null;
                    $order->confirmed_by = null;
                    $order->confirmed_at = null;
                    
                    // HAPUS DATA REPORT YANG TERKAIT DENGAN ORDER INI
                    Report::where(function($query) use ($order) {
                            $query->where('keterangan', 'LIKE', '%Pembayaran Kasir: ' . $order->kode_order . '%')
                                  ->orWhere('keterangan', 'LIKE', '%' . $order->kode_order . '%');
                        })
                        // ->where('order_id', $order->id) // Tambahkan ini jika ada kolom order_id
                        ->delete();
                }
                
                if ($request->payment_status == 'paid') {
                    $order->paid_at = now();
                    $order->is_hutang = '0';
                    $order->confirmed_by = session('user_id');
                    $order->confirmed_at = now();

                    // Report
                    Report::create([
                        'tanggal_laporan' => now(),
                        'keterangan' => 'Pembayaran Kasir: ' . $order->kode_order . ' - ' . ($order->customer->name ?? 'Customer'),
                        'debit' => $order->total_harga,
                        'kredit' => 0,
                        'saldo' => $order->total_harga,
                        'kategori' => 'Pendapatan',
                        // 'order_id' => $order->id, // Tambahkan ini jika ada kolom order_id
                    ]);
                }

                $order->save();

                // ===== CATAT KE LOG ACTIVITY =====
                $kasir = User::find(session('user_id'));
                $customerName = $order->customer->name ?? 'Customer';
                
                $statusLabel = [
                    'paid' => 'Lunas',
                    'unpaid' => 'Belum Bayar'
                ];
                
                LogActivity::create([
                    'user_id' => session('user_id'),
                    'ip' => request()->ip(),
                    'aksi' => 'update',
                    'module' => 'Order Manual',
                    'keterangan' => "Kasir {$kasir->name} mengubah status pembayaran order {$order->kode_order} untuk customer {$customerName} dari " . ($statusLabel[$oldStatus] ?? $oldStatus) . " menjadi " . ($statusLabel[$request->payment_status] ?? $request->payment_status),
                    'old_data' => [
                        'payment_status' => $oldStatus,
                        'payment_status_label' => $statusLabel[$oldStatus] ?? $oldStatus
                    ],
                    'new_data' => [
                        'payment_status' => $request->payment_status,
                        'payment_status_label' => $statusLabel[$request->payment_status] ?? $request->payment_status,
                        // 'order_id' => $order->id,
                        'kode_order' => $order->kode_order,
                        'customer_id' => $order->id_customer,
                        'customer_name' => $customerName,
                        'menu_name' => $order->menu->nama_menu ?? '-',
                        'paket' => $order->paket,
                        'total_harga' => $order->total_harga,
                        'updated_by' => $kasir->name,
                        'updated_at' => now()->toDateTimeString()
                    ],
                    'user_agent' => request()->userAgent(),
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Status pembayaran berhasil diupdate!',
                    'data' => $order
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Update payment error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupdate status: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('updatePayment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus Order (hanya yang belum dibayar)
     */
    public function destroy($id)
    {
        try {
            $order = Order::whereIn('payment_method', ['kasir', 'cash'])
                ->where('payment_status', 'unpaid')
                ->with(['customer', 'menu'])
                ->findOrFail($id);

            // Simpan data sebelum dihapus untuk log
            $orderData = [
                'id' => $order->id,
                'kode_order' => $order->kode_order,
                'customer_id' => $order->id_customer,
                'customer_name' => $order->customer->name ?? 'Customer',
                'menu_id' => $order->id_menu,
                'menu_name' => $order->menu->nama_menu ?? '-',
                'paket' => $order->paket,
                'quantity' => $order->quantity,
                'harga_satuan' => $order->harga_satuan,
                'total_harga' => $order->total_harga,
                'payment_status' => $order->payment_status,
                'tanggal_menu' => $order->tanggal_menu,
                'catatan' => $order->catatan,
                'created_at' => $order->created_at->toDateTimeString(),
            ];

            $kasir = User::find(session('user_id'));
            $customerName = $order->customer->name ?? 'Customer';

            $order->delete();

            // ===== CATAT KE LOG ACTIVITY =====
            LogActivity::create([
                'user_id' => session('user_id'),
                'ip' => request()->ip(),
                'aksi' => 'delete',
                'module' => 'Order Manual',
                'keterangan' => "Kasir {$kasir->name} menghapus order {$order->kode_order} untuk customer {$customerName} dengan total Rp " . number_format($order->total_harga, 0, ',', '.'),
                'old_data' => $orderData,
                'new_data' => null,
                'user_agent' => request()->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            Log::error('destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus order: ' . $e->getMessage()
            ], 500);
        }
    }
}