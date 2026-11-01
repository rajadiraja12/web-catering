<?php
// app/Http/Controllers/AdminController.php

namespace App\Http\Controllers;

use App\Exports\OrderBackupExport;
use App\Helpers\ActivityHelper;
use App\Models\Backup;
use App\Models\Cart;
use App\Models\LogActivity;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Report;
use App\Models\User;
use App\Traits\CheckBackupTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AdminController extends Controller
{
    use CheckBackupTrait; // <-- TAMBAHKAN
    /**
     * Dashboard Admin
     */
    public function dashboard()
    {
        $this->checkAndCreateBackup();
        
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
        
        // Statistik
        $totalUsers = User::count();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalActiveUsers = User::where('status', 'Active')->count();
        $totalMenus = Menu::count();
        $totalOrders = Order::count();
        $totalOrdersToday = Order::whereDate('created_at', today())->count();
        
        // ===== PERUBAHAN: Ambil dari Reports (DEBIT = Pendapatan) =====
        // Total Pendapatan dari Report (Debit)
        $totalRevenue = Report::sum('debit');
        
        // Total Pendapatan Hari Ini dari Report
        $totalRevenueToday = Report::whereDate('tanggal_laporan', today())->sum('debit');
        
        // Total Pengeluaran dari Report (Kredit)
        $totalExpense = Report::sum('kredit');
        
        // Total Pengeluaran Hari Ini
        $totalExpenseToday = Report::whereDate('tanggal_laporan', today())->sum('kredit');
        
        // Saldo / Laba
        $totalBalance = $totalRevenue - $totalExpense;
        $totalBalanceToday = $totalRevenueToday - $totalExpenseToday;

        // Data untuk grafik (7 hari terakhir)
        $chartData = $this->getChartData();

        // Log aktivitas terbaru
        $recentActivities = LogActivity::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'user',
            'totalUsers',
            'totalCustomers',
            'totalActiveUsers',
            'totalMenus',
            'totalOrders',
            'totalOrdersToday',
            'totalRevenue',        // Pendapatan dari Report
            'totalRevenueToday',   // Pendapatan hari ini dari Report
            'totalExpense',        // Pengeluaran dari Report
            'totalExpenseToday',   // Pengeluaran hari ini dari Report
            'totalBalance',        // Saldo
            'totalBalanceToday',   // Saldo hari ini
            'chartData',
            'recentActivities'
        ));
    }

    /**
    * Show edit user form
    */
    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'no_hp' => 'required|string|max:15',
            'kelas' => 'required|string|max:50',
            'nama_kelas' => 'required|string|max:50',
            'alergi' => 'nullable|string|max:255',
            'role' => 'required|in:customer,kasir,admin',
            'status' => 'required|in:Active,Non Aktif',
            'password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Gabungkan kelas dan nama_kelas
            $kelas = $request->kelas . '-' . $request->nama_kelas;
            
            $data = $request->except(['password', 'kelas', 'nama_kelas']);
            $data['kelas'] = $kelas;
            
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            Log::info('User updated', [
                'admin_id' => session('user_id'),
                'user_id' => $user->id,
                'username' => $user->username
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil diupdate!'
            ]);

        } catch (\Exception $e) {
            Log::error('Update user error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate user: ' . $e->getMessage()
            ], 500);
        }
    }
    // private function checkAndCreateBackup()
    // {
    //     // Cek apakah hari ini tanggal 1
    //     if (Carbon::now()->day == 1) {
    //         $monthName = Carbon::now()->subMonth()->format('F_Y');
    //         $backupExists = Backup::where('nama_backup', 'LIKE', '%' . $monthName . '%')->exists();
            
    //         if (!$backupExists) {
    //             $this->createMonthlyBackup();
    //         }
    //     }
    // }

    /**
     * Data untuk grafik dashboard
     */
    private function getChartData()
    {
        $labels = [];
        $orders = [];
        $revenues = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('D');
            
            $orderCount = Order::whereDate('created_at', $date)->count();
            $orders[] = $orderCount;
            
            $revenue = Order::where('payment_status', 'paid')
                ->whereDate('created_at', $date)
                ->sum('total_harga');
            $revenues[] = (float) $revenue;
        }

        return [
            'labels' => $labels,
            'orders' => $orders,
            'revenues' => $revenues,
        ];
    }

    /**
     * Manajemen User
     */
    public function manageUsers(Request $request)
    {
        $query = User::query();

        // Filter pencarian
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhere('kelas', 'LIKE', "%{$search}%")
                  ->orWhere('no_hp', 'LIKE', "%{$search}%");
            });
        }

        // Filter role
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        // Filter status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(5);

        // Jika request AJAX untuk pencarian
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.partials.user_table', compact('users'))->render(),
                'pagination' => $users->links()->toHtml(),
            ]);
        }

        return view('admin.manage_user', compact('users'));
    }

    /**
     * Update Hutang Limit (Admin)
     */
    public function updateHutangLimit(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'hutang_limit' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::findOrFail($userId);
        $user->hutang_limit = $request->hutang_limit;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Limit hutang berhasil diupdate!',
            'data' => [
                'hutang_limit' => $user->hutang_limit,
                'hutang_used' => $user->hutang_used,
                'hutang_available' => $user->hutang_limit - $user->hutang_used
            ]
        ]);
    }

    /**
     * Detail Hutang
     */
    public function detail($id)
    {
        $hutang = Order::with(['customer', 'menu'])
            ->where('is_hutang', true)
            ->findOrFail($id);

        return view('admin.hutang.detail', compact('hutang'));
    }

    /**
     * Konfirmasi Pembayaran Hutang
     */
    public function confirm(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'bukti_pembayaran' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'catatan' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::where('is_hutang', true)
            ->where('payment_status', 'unpaid')
            ->findOrFail($id);

        DB::beginTransaction();
        try {
            // Upload bukti pembayaran
            $buktiPath = null;
            if ($request->hasFile('bukti_pembayaran')) {
                $file = $request->file('bukti_pembayaran');
                $filename = 'hutang_' . time() . '_' . $order->id . '.' . $file->getClientOriginalExtension();
                $buktiPath = $file->storeAs('hutang/bukti', $filename, 'public');
            }

            // Update order
            $order->payment_status = 'paid';
            $order->paid_at = now();
            $order->confirmed_by = session('user_id');
            $order->confirmed_at = now();
            // $order->bukti_pembayaran = $buktiPath;
            $order->save();

            // Kurangi hutang user
            $user = User::find($order->id_customer);
            if ($user) {
                $user->hutang_used -= $order->total_harga;
                $user->save();
            }

            // Report
            Report::create([
                'tanggal_laporan' => now(),
                'keterangan' => 'Pembayaran Hutang: ' . $order->kode_order . ' - ' . ($user->name ?? 'Customer'),
                'debit' => $order->total_harga,
                'kredit' => 0,
                'saldo' => $order->total_harga,
                'kategori' => 'Pendapatan',
            ]);

            DB::commit();

            Log::info('Hutang confirmed by admin', [
                'order_id' => $order->id,
                'admin_id' => session('user_id'),
                'kode_order' => $order->kode_order
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran hutang berhasil dikonfirmasi!',
                'data' => $order
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Confirm hutang error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal konfirmasi pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tolak Konfirmasi Hutang
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'alasan' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::where('is_hutang', true)
            ->where('payment_status', 'unpaid')
            ->findOrFail($id);

        // Hanya update catatan (status tetap unpaid)
        $order->catatan = $request->alasan;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Konfirmasi ditolak. Catatan: ' . $request->alasan,
        ]);
    }

    public function hutang(Request $request)
    {
        $query = Order::with(['customer', 'menu'])
            ->where('is_hutang', true)
            ->where('payment_status', 'unpaid')
            ->orderBy('created_at', 'desc'); // Data terbaru

        // Filter pencarian
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('kode_order', 'LIKE', '%' . $request->search . '%')
                ->orWhereHas('customer', function($cq) use ($request) {
                    $cq->where('name', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('username', 'LIKE', '%' . $request->search . '%');
                });
            });
        }

        // Filter status overdue
        if ($request->status === 'overdue') {
            $query->where('due_date', '<', now());
        }

        $hutangs = $query->paginate(20);
        
        // Statistik dengan satu query base
        $baseQuery = Order::where('is_hutang', true)
            ->where('payment_status', 'unpaid');
        
        $statistics = [
            'total' => $baseQuery->count(),
            'total_nominal' => $baseQuery->sum('total_harga'),
            'overdue' => (clone $baseQuery)->where('due_date', '<', now())->count(),
            'overdue_nominal' => (clone $baseQuery)->where('due_date', '<', now())->sum('total_harga'),
        ];

        return view('admin.hutang.index', compact('hutangs', 'statistics'));
    }

    /**
     * Tambah User Baru
     */
    public function storeUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'no_hp' => 'required|string|max:15',
            'kelas' => 'required|string|max:50',
            'nama_kelas' => 'required|string|max:50',
            'alergi' => 'nullable|string|max:255',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,customer,kasir',
            'status' => 'required|in:Active,Non Aktif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Gabungkan kelas dan nama_kelas
            $kelas = $request->kelas . '-' . $request->nama_kelas;

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'no_hp' => $request->no_hp,
                'kelas' => $kelas,
                'alergi' => $request->alergi,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'status' => $request->status,
            ]);

            // Log aktivitas
            Log::info('User added', [
                'admin_id' => session('user_id'),
                'user_id' => $user->id,
                'username' => $user->username
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditambahkan',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('Store user error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan user: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Toggle Status User
     */
    public function toggleUserStatus($id)
    {
        $user = User::findOrFail($id);
        $newStatus = $user->status === 'Active' ? 'Non Aktif' : 'Active';
        
        $oldStatus = $user->status;
        $user->update(['status' => $newStatus]);

        // Log aktivitas
        $this->logActivity('Toggle Status User', 'User Management', "Mengubah status user {$user->username} dari {$oldStatus} menjadi {$newStatus}");

        return response()->json([
            'success' => true,
            'message' => "Status user berhasil diubah menjadi {$newStatus}",
            'data' => [
                'user' => $user,
                'status' => $newStatus
            ]
        ]);
    }

    /**
     * Hapus User
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        
        // Cek apakah user memiliki order
        if ($user->orders()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak dapat dihapus karena memiliki order aktif'
            ], 400);
        }

        $username = $user->username;
        $user->delete();

        // Log aktivitas
        $this->logActivity('Hapus User', 'User Management', "Menghapus user: {$username}");

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus'
        ]);
    }

    /**
     * Manajemen Menu
     */
    public function manageMenus(Request $request)
    {
        $query = Menu::query();

        // Filter pencarian
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('nama_menu', 'LIKE', "%{$search}%")
                ->orWhere('deskripsi', 'LIKE', "%{$search}%");
        }

        // Filter tipe menu
        if ($request->has('tipe_menu') && $request->tipe_menu) {
            $query->where('tipe_menu', $request->tipe_menu);
        }

        // Filter tanggal
        if ($request->has('tanggal') && $request->tanggal) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        $menus = $query->orderBy('tanggal', 'desc')->orderBy('tipe_menu')->paginate(10);

        return view('admin.manage_menu', compact('menus'));
    }
    
    /**
     * Store menu baru
     */
    public function storeMenu(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_menu' => 'required|string|max:255',
                'tanggal' => 'required|date|after_or_equal:today',
                'harga_paket_kecil' => 'required|numeric|min:0',
                'harga_paket_sedang' => 'required|numeric|min:0',
                'harga_paket_besar' => 'required|numeric|min:0',
                'harga_paket_khusus' => 'required|numeric|min:0',
                'deskripsi' => 'required|string',
                'tipe_menu' => 'required|in:umum,kelas_1',
                'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // VALIDASI: MAKSIMAL 5 MENU PER TANGGAL PER TIPE
            $tanggal = $request->tanggal;
            $tipe = $request->tipe_menu;
            $menuCount = Menu::whereDate('tanggal', $tanggal)
                ->where('tipe_menu', $tipe)
                ->count();
            
            if ($menuCount >= 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maaf, untuk tanggal ' . date('d-m-Y', strtotime($tanggal)) . ' tipe ' . ucfirst($tipe) . ' sudah mencapai maksimal 5 menu!'
                ], 422);
            }

            $data = $request->except('gambar');

            // Upload gambar
            if ($request->hasFile('gambar')) {
                $file = $request->file('gambar');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('image_menu'), $filename);
                $data['gambar'] = 'image_menu/' . $filename;
            }

            $menu = Menu::create($data);

            // Log aktivitas
            $this->logActivity('Tambah Menu', 'Menu Management', "Menambahkan menu baru: {$menu->nama_menu}");

            return response()->json([
                'success' => true,
                'message' => 'Menu berhasil ditambahkan!',
                'data' => $menu
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Store menu error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update menu
     */
    public function updateMenu(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_menu' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'harga_paket_kecil' => 'required|numeric|min:0',
            'harga_paket_sedang' => 'required|numeric|min:0',
            'harga_paket_besar' => 'required|numeric|min:0',
            'harga_paket_khusus' => 'required|numeric|min:0',
            'deskripsi' => 'required|string',
            'tipe_menu' => 'required|in:umum,kelas_1',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // ===== VALIDASI: MAKSIMAL 5 MENU PER TANGGAL PER TIPE =====
        $tanggal = $request->tanggal;
        $tipe = $request->tipe_menu;
        $menuCount = Menu::whereDate('tanggal', $tanggal)
            ->where('tipe_menu', $tipe)
            ->where('id', '!=', $id)
            ->count();
        
        if ($menuCount >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'Maaf, untuk tanggal ' . date('d-m-Y', strtotime($tanggal)) . ' tipe ' . ucfirst($tipe) . ' sudah mencapai maksimal 5 menu!'
            ], 422);
        }

        $oldData = $menu->toArray();
        $data = $request->except('gambar');

        // Upload gambar baru
        if ($request->hasFile('gambar')) {
            if ($menu->gambar && file_exists(public_path($menu->gambar))) {
                unlink(public_path($menu->gambar));
            }
            
            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('image_menu'), $filename);
            $data['gambar'] = 'image_menu/' . $filename;
        }

        $menu->update($data);

        $this->logActivity('Update Menu', 'Menu Management', "Mengupdate menu: {$menu->nama_menu}", $oldData, $menu->toArray());

        return redirect()->route('admin.menus')
            ->with('success', 'Menu berhasil diupdate!');
    }

    /**
     * Hapus Menu
     */
    public function deleteMenu($id)
    {
        $menu = Menu::findOrFail($id);

        // Cek apakah menu memiliki order
        if ($menu->orders()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Menu tidak dapat dihapus karena memiliki order terkait!');
        }

        // Hapus gambar jika ada
        if ($menu->gambar && file_exists(public_path($menu->gambar))) {
            unlink(public_path($menu->gambar));
        }

        $menu->delete();

        $this->logActivity('Hapus Menu', 'Menu Management', "Menghapus menu: {$menu->nama_menu}");

        return redirect()->route('admin.menus')
            ->with('success', 'Menu berhasil dihapus!');
    }

    /**
     * Cek kuota menu per tanggal per tipe
     */
    public function checkMenuQuota(Request $request)
    {
        $tanggal = $request->tanggal;
        $tipe = $request->tipe_menu ?? 'umum';
        
        $count = Menu::whereDate('tanggal', $tanggal)
            ->where('tipe_menu', $tipe)
            ->count();
        
        return response()->json([
            'success' => true,
            'count' => $count,
            'max' => 5,
            'sisa' => 5 - $count,
            'tipe' => $tipe
        ]);
    }

    /**
     * Toggle Status Menu
     */
    public function toggleMenuStatus($id)
    {
        $menu = Menu::findOrFail($id);
        $newStatus = $menu->status === 'available' ? 'unavailable' : 'available';
        
        $oldStatus = $menu->status;
        $menu->update(['status' => $newStatus]);

        $this->logActivity('Toggle Status Menu', 'Menu Management', "Mengubah status menu {$menu->nama_menu} dari {$oldStatus} menjadi {$newStatus}");

        return redirect()->route('admin.menus')
            ->with('success', 'Status menu berhasil diubah!');
    }

    /**
     * Manajemen Order
     */
    public function manageOrders(Request $request)
    {
        $query = Order::with(['customer', 'menu']);

        // Filter pencarian
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_order', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function($q2) use ($search) {
                      $q2->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('kelas', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Filter tanggal
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('tanggal_order', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('tanggal_order', '<=', $request->date_to);
        }

        // Filter status pembayaran
        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.partials.order_table', compact('orders'))->render(),
                'pagination' => $orders->links()->toHtml(),
            ]);
        }

        return view('admin.manage_order', compact('orders'));
    }

    /**
     * Update Status Payment Order
     */
    public function updateOrderPaymentStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'payment_status' => 'required|in:unpaid,paid'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $oldStatus = $order->payment_status;
        $order->update(['payment_status' => $request->payment_status]);

        // Jika status berubah menjadi paid, update report
        if ($request->payment_status === 'paid' && $oldStatus === 'unpaid') {
            $this->updateReport($order);
        }

        // Log aktivitas
        $this->logActivity('Update Payment Status', 'Order Management', "Mengubah status pembayaran order {$order->kode_order} dari {$oldStatus} menjadi {$request->payment_status}");

        return response()->json([
            'success' => true,
            'message' => 'Status pembayaran berhasil diupdate',
            'data' => $order
        ]);
    }

    /**
     * Update Report setelah pembayaran
     */
    private function updateReport($order)
    {
        Report::create([
            'tanggal_laporan' => now(),
            'keterangan' => "Pembayaran Order: {$order->kode_order} - {$order->customer->name}",
            'debit' => $order->total_harga,
            'kredit' => 0,
            'saldo' => $order->total_harga,
            'kategori' => 'Pendapatan',
        ]);
    }

    /**
     * Laporan Keuangan
     */
    public function reports(Request $request)
    {
        $query = Report::query();

        // Filter tanggal
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('tanggal_laporan', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('tanggal_laporan', '<=', $request->date_to);
        }

        // Filter kategori
        if ($request->has('kategori') && $request->kategori) {
            $query->where('kategori', $request->kategori);
        }

        $reports = $query->orderBy('tanggal_laporan', 'desc')->paginate(10);

        // Statistik
        $totalDebit = Report::sum('debit');
        $totalKredit = Report::sum('kredit');
        $totalSaldo = $totalDebit - $totalKredit;
        $totalTransaksi = Report::count(); // <-- TAMBAHKAN

        // Ambil tanggal filter atau default
        $startDate = $request->date_from ?? Report::min('tanggal_laporan');
        $endDate = $request->date_to ?? Report::max('tanggal_laporan');

        return view('admin.report', compact(
            'reports',
            'totalDebit',
            'totalKredit',
            'totalSaldo',
            'totalTransaksi', // <-- TAMBAHKAN
            'startDate',      // <-- TAMBAHKAN
            'endDate'         // <-- TAMBAHKAN
        ));
    }

    /**
     * Tambah Pengeluaran
     */
    public function storeExpense(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:500',
            'nominal' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $report = Report::create([
            'tanggal_laporan' => $request->tanggal,
            'keterangan' => $request->keterangan,
            'debit' => 0,
            'kredit' => $request->nominal,
            'saldo' => -$request->nominal,
            'kategori' => 'Pengeluaran',
        ]);

        // Log aktivitas
        $this->logActivity('Tambah Pengeluaran', 'Report Management', "Menambahkan pengeluaran: {$request->keterangan} - Rp " . number_format($request->nominal, 0, ',', '.'));

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil ditambahkan',
            'data' => $report
        ]);
    }

    /**
     * Create Backup (Manual)
     */
    public function createBackup(Request $request)
    {
        try {
            $startDate = Carbon::parse($request->start_date ?? Carbon::now()->startOfMonth());
            $endDate = Carbon::parse($request->end_date ?? Carbon::now()->endOfMonth());

            $backupName = 'Backup_Manual_' . Carbon::now()->format('d-m-Y_H-i-s');
            $filename = $backupName . '.xlsx';
            $path = 'backups/' . $filename;

            // Buat Excel sederhana
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Laporan Keuangan');

            // Header
            $sheet->setCellValue('A1', 'LAPORAN KEUANGAN');
            $sheet->mergeCells('A1:G1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValue('A2', 'Periode: ' . $startDate->format('d-m-Y') . ' s/d ' . $endDate->format('d-m-Y'));
            $sheet->mergeCells('A2:G2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValue('A3', 'Tanggal Backup: ' . Carbon::now()->format('d-m-Y H:i:s'));
            $sheet->mergeCells('A3:G3');
            $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row = 5;

            // Data Ringkasan
            $sheet->setCellValue('A' . $row, 'RINGKASAN KEUANGAN');
            $sheet->mergeCells('A' . $row . ':G' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle('A' . $row)->getFill()->getStartColor()->setARGB('FF4472C4');
            $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

            $row += 2;

            $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();
            $totalRevenue = Order::where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_harga');

            $sheet->setCellValue('B' . $row, 'Total Order: ' . $totalOrders);
            $row++;
            $sheet->setCellValue('B' . $row, 'Total Pendapatan: Rp ' . number_format($totalRevenue, 0, ',', '.'));

            $row += 2;

            // Data Laporan
            $sheet->setCellValue('A' . $row, 'DETAIL LAPORAN');
            $sheet->mergeCells('A' . $row . ':G' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle('A' . $row)->getFill()->getStartColor()->setARGB('FF4472C4');
            $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

            $row += 2;

            // Header tabel
            $sheet->setCellValue('A' . $row, 'Tanggal');
            $sheet->setCellValue('B' . $row, 'Keterangan');
            $sheet->setCellValue('C' . $row, 'Debit');
            $sheet->setCellValue('D' . $row, 'Kredit');
            $sheet->setCellValue('E' . $row, 'Saldo');
            $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':E' . $row)->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle('A' . $row . ':E' . $row)->getFill()->getStartColor()->setARGB('FFD9E1F2');

            $row++;

            $reports = Report::whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($reports as $report) {
                $sheet->setCellValue('A' . $row, $report->tanggal_laporan);
                $sheet->setCellValue('B' . $row, $report->keterangan);
                $sheet->setCellValue('C' . $row, 'Rp ' . number_format($report->debit, 0, ',', '.'));
                $sheet->setCellValue('D' . $row, 'Rp ' . number_format($report->kredit, 0, ',', '.'));
                $sheet->setCellValue('E' . $row, 'Rp ' . number_format($report->saldo, 0, ',', '.'));
                $row++;
            }

            // Auto size
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);

            // Simpan file
            $writer = new Xlsx($spreadsheet);
            $fullPath = storage_path('app/public/' . $path);
            
            if (!is_dir(storage_path('app/public/backups'))) {
                mkdir(storage_path('app/public/backups'), 0777, true);
            }
            
            $writer->save($fullPath);

            // Simpan ke database
            $backup = Backup::create([
                'nama_backup' => $backupName,
                'lokasi_file' => $path,
                'type' => 'manual',
                'keterangan_data' => "Backup manual periode " . $startDate->format('d-m-Y') . " s/d " . $endDate->format('d-m-Y'),
                'size' => Storage::disk('public')->size($path) / 1024,
                'created_by' => Auth::id(),
                'backup_date' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Backup berhasil dibuat',
                'data' => $backup
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate Excel Backup
     */
    private function generateBackupExcel($startDate, $endDate, $type)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Keuangan');

        // Header
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'LAPORAN KEUANGAN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', 'Periode: ' . $startDate->format('d-m-Y') . ' s/d ' . $endDate->format('d-m-Y'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:G3');
        $sheet->setCellValue('A3', 'Tanggal Backup: ' . Carbon::now()->format('d-m-Y H:i:s'));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 5;

        // Ringkasan
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->setCellValue('A' . $row, '=== RINGKASAN KEUANGAN ===');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle('A' . $row)->getFill()->getStartColor()->setARGB('FF4472C4');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

        $row += 2;

        // Total Order
        $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        $sheet->setCellValue('B' . $row, 'Total Order');
        $sheet->setCellValue('C' . $row, $totalOrders);
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);

        $row++;

        // Total Pendapatan
        $totalRevenue = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_harga');
        $sheet->setCellValue('B' . $row, 'Total Pendapatan');
        $sheet->setCellValue('C' . $row, 'Rp ' . number_format($totalRevenue, 0, ',', '.'));
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);
        $sheet->getStyle('C' . $row)->getFont()->setBold(true);
        $sheet->getStyle('C' . $row)->getFont()->getColor()->setARGB('FF00B050');

        $row++;

        // Total Belum Dibayar
        $totalUnpaid = Order::where('payment_status', 'unpaid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_harga');
        $sheet->setCellValue('B' . $row, 'Total Belum Dibayar');
        $sheet->setCellValue('C' . $row, 'Rp ' . number_format($totalUnpaid, 0, ',', '.'));
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);
        $sheet->getStyle('C' . $row)->getFont()->getColor()->setARGB('FFFF0000');

        $row += 2;

        // Detail Laporan
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->setCellValue('A' . $row, '=== DETAIL LAPORAN KEUANGAN ===');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle('A' . $row)->getFill()->getStartColor()->setARGB('FF4472C4');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

        $row += 2;

        // Header tabel
        $headers = ['Tanggal', 'Keterangan', 'Debit', 'Kredit', 'Saldo', 'Kategori'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle($col . $row)->getFill()->getStartColor()->setARGB('FFD9E1F2');
            $col++;
        }

        $row++;

        $reports = Report::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($reports->count() > 0) {
            foreach ($reports as $report) {
                $sheet->setCellValue('A' . $row, $report->tanggal_laporan);
                $sheet->setCellValue('B' . $row, $report->keterangan);
                $sheet->setCellValue('C' . $row, 'Rp ' . number_format($report->debit, 0, ',', '.'));
                $sheet->setCellValue('D' . $row, 'Rp ' . number_format($report->kredit, 0, ',', '.'));
                $sheet->setCellValue('E' . $row, 'Rp ' . number_format($report->saldo, 0, ',', '.'));
                $sheet->setCellValue('F' . $row, $report->kategori ?? '-');
                $row++;
            }
        }

        // Auto-size
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }
    
    /**
     * Download Backup
     */
    public function downloadBackup($id)
    {
        $backup = Backup::findOrFail($id);
        
        if (!Storage::disk('public')->exists($backup->lokasi_file)) {
            return response()->json([
                'success' => false,
                'message' => 'File backup tidak ditemukan'
            ], 404);
        }

        $this->logActivity('Download Backup', 'Backup Management', "Mengunduh backup: {$backup->nama_backup}");

        return Storage::disk('public')->download($backup->lokasi_file, $backup->nama_backup . '.xlsx');
    }

    /**
     * Delete Backup
     */
    public function deleteBackup($id)
    {
        $backup = Backup::findOrFail($id);
        
        if (Storage::disk('public')->exists($backup->lokasi_file)) {
            Storage::disk('public')->delete($backup->lokasi_file);
        }

        $namaBackup = $backup->nama_backup;
        $backup->delete();

        $this->logActivity('Delete Backup', 'Backup Management', "Menghapus backup: {$namaBackup}");

        return response()->json([
            'success' => true,
            'message' => 'Backup berhasil dihapus'
        ]);
    }

    /**
     * Backup Data - Transaksi (Manual)
     */
    public function backupTransaksi(Request $request)
    {
        try {
            $startDate = Carbon::parse($request->start_date ?? Carbon::now()->startOfMonth());
            $endDate = Carbon::parse($request->end_date ?? Carbon::now()->endOfMonth());

            // Generate backup
            $result = OrderBackupExport::generateAndSave($startDate, $endDate, 'manual');

            // Simpan ke database
            $backup = Backup::create([
                'nama_backup' => 'Backup_Transaksi_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d'),
                'lokasi_file' => $result['path'],
                'type' => 'manual',
                'keterangan_data' => "Backup transaksi periode " . $startDate->format('d-m-Y') . " s/d " . $endDate->format('d-m-Y'),
                'size' => $result['size'],
                'created_by' => Auth::id(),
                'backup_date' => now(),
                'periode_start' => $startDate,
                'periode_end' => $endDate,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Backup transaksi berhasil dibuat',
                'data' => $backup
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Data Backup - Tampilkan Semua
     */
    public function backups(Request $request)
    {
        $backups = Backup::with('createdBy')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Statistik
        $totalBackups = Backup::count();
        $totalManual = Backup::where('type', 'manual')->count();
        $totalOtomatis = Backup::where('type', 'otomatis')->count();
        $totalSize = Backup::sum('size');

        return view('admin.backup', compact('backups', 'totalBackups', 'totalManual', 'totalOtomatis', 'totalSize'));
    }
    /**
     * Active Sessions
     */
    public function activeSessions()
    {
        // Ambil user yang sedang login (session_id tidak null)
        $activeUsers = User::whereNotNull('session_id')
            ->where('status', 'Active')
            ->get();

        return view('dashboard.active-sessions', compact('activeUsers'));
    }

    /**
     * Force Logout User dari semua perangkat
     */
    public function forceLogoutUser($id)
    {
        $user = User::findOrFail($id);
        
        // Hapus session_id
        $user->update(['session_id' => null]);
        
        // Hapus session dari database
        DB::table('sessions')->where('user_id', $user->id)->delete();

        // Log aktivitas
        $this->logActivity('Force Logout', 'User Management', "Memaksa logout user: {$user->username} dari semua perangkat");

        return response()->json([
            'success' => true,
            'message' => "User {$user->username} berhasil di-logout dari semua perangkat"
        ]);
    }

    /**
     * Log aktivitas
     */
    private function logActivity($aksi, $module, $keterangan, $oldData = null, $newData = null)
    {
        try {
            LogActivity::create([
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
                'aksi' => $aksi,
                'module' => $module,
                'keterangan' => $keterangan,
                'old_data' => $oldData ? json_encode($oldData) : null,
                'new_data' => $newData ? json_encode($newData) : null,
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }

    /**
     * Dashboard Customer (Untuk Admin melihat data customer)
     */
    public function customerDashboard($id = null)
    {
        if ($id) {
            $customer = User::with(['orders', 'carts'])->findOrFail($id);
            return view('admin.customer_detail', compact('customer'));
        }

        $customers = User::where('role', 'customer')
            ->withCount(['orders', 'carts'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.customers', compact('customers'));
    }

    /**
     * Export Data (PDF/Excel)
     */
    public function exportData(Request $request)
    {
        $type = $request->type ?? 'pdf';
        $module = $request->module ?? 'orders';

        // Implementasi export sesuai kebutuhan
        // Bisa menggunakan library seperti DomPDF, Maatwebsite Excel, dll

        return response()->json([
            'success' => true,
            'message' => "Export {$module} sebagai {$type} berhasil"
        ]);
    }

    /**
     * Log Aktivitas
     */
    public function logActivities(Request $request)
    {
        $query = LogActivity::with('user');

        // Filter pencarian
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('aksi', 'LIKE', "%{$search}%")
                ->orWhere('module', 'LIKE', "%{$search}%")
                ->orWhere('keterangan', 'LIKE', "%{$search}%");
            });
        }

        // Filter module
        if ($request->has('module') && $request->module) {
            $query->where('module', $request->module);
        }

        // Filter tanggal
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get modules untuk filter
        $modules = LogActivity::select('module')->distinct()->pluck('module');

        return view('admin.log_activity', compact('logs', 'modules'));
    }

    /**
     * Get menu data for edit modal (AJAX)
     */
    public function editMenu($id)
    {
        try {
            $menu = Menu::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $menu
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Menu tidak ditemukan'
            ], 404);
        }
    }
}