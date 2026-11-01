<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * ============================================
     * 1. SHOW LOGIN & WELCOME PAGE
     * ============================================
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

     /**
     * Halaman Syarat & Ketentuan
     */
    public function sk()
    {
        return view('terms.index');
    }
    
    public function welcome()
    {
        return view('auth.welcome');
    }

    /**
     * ============================================
     * 2. PROSES LOGIN
     * ============================================
     */
    public function login(Request $request)
    {
        Log::info('Login attempt', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => session()->getId(),
        ]);

        if ($request->has('force') && $request->force == 'true') {
            Log::info('Force login attempt', ['username' => $request->username]);
            return $this->forceLogin($request);
        }
        
        // Rate limiting (5 attempts per minute)
        $key = 'login_attempts_' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return redirect()->back()
                ->withErrors(['username' => 'Terlalu banyak percobaan login. Coba lagi dalam ' . 
                    RateLimiter::availableIn($key) . ' detik.']);
        }

        // Validasi
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        // Cari user
        $user = User::where('username', $request->username)->first();

        if (!$user) {
            RateLimiter::hit($key, 60);
            return redirect()->back()
                ->withErrors(['username' => 'Username atau password salah.'])
                ->withInput($request->except('password'));
        }

        // Cek status user
        if ($user->status !== 'Active') {
            return redirect()->back()
                ->withErrors(['username' => 'Akun Anda tidak aktif. Silakan hubungi admin.'])
                ->withInput($request->except('password'));
        }

        // Cek password
        if (!Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 60);
            $this->logActivity('Login Failed', 'Authentication', 'Invalid password', null, [
                'username' => $user->username,
                'ip' => $request->ip(),
            ]);
            return redirect()->back()
                ->withErrors(['username' => 'Username atau password salah.'])
                ->withInput($request->except('password'));
        }

        // Di login() method, bagian cek session aktif

        // ============================================================
        // CEK SESSION AKTIF - TOLAK LOGIN JIKA ADA SESSION
        // ============================================================
        $storedSessionId = $user->session_id;
        $hasActiveSession = false;
        $deviceInfo = '';
        $ipInfo = '';
        $loginTime = '';
    
        if ($storedSessionId) {
            // CEK APAKAH SESSION MASIH AKTIF DI TABEL SESSIONS
            $sessionData = DB::table('sessions')
                ->where('id', $storedSessionId)
                ->first();
    
            if ($sessionData) {
                // Cek apakah session sudah expired
                $lifetime = config('session.lifetime') * 60;
                $isExpired = (time() - $sessionData->last_activity) > $lifetime;
    
                if (!$isExpired) {
                    // ✅ SESSION MASIH AKTIF! TOLAK LOGIN
                    $hasActiveSession = true;
                    $deviceInfo = $user->session_device ?? 'Perangkat lain';
                    $ipInfo = $user->session_ip ?? 'IP tidak diketahui';
                    $loginTime = $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i:s') : 'Tidak diketahui';
    
                    Log::warning('LOGIN DITOLAK - Session masih aktif', [
                        'user' => $user->username,
                        'session_id' => $storedSessionId,
                        'device' => $deviceInfo,
                        'ip' => $ipInfo,
                        'attempt_ip' => $request->ip()
                    ]);
    
                    RateLimiter::hit($key, 60);
    
                    return redirect()->back()
                    ->with('session_alert', true)
                    ->with('session_data', [
                        'device' => $deviceInfo,
                        'ip' => $ipInfo,
                        'login_time' => $loginTime,
                        'username' => $request->username,
                        'password' => $request->password,
                    ])
                    ->withInput($request->except('password'));
            } else {
                    // ✅ Session expired, hapus dari tabel dan reset user
                    DB::table('sessions')->where('id', $storedSessionId)->delete();
                    $user->update(['session_id' => null]);
                    Log::info('Session expired, cleared for user: ' . $user->username);
                }
            } else {
                // ✅ Session tidak ditemukan di tabel, reset user
                $user->update(['session_id' => null]);
                Log::info('Session not found in DB, reset for user: ' . $user->username);
            }
        }
    
        // ✅ CEK LAGI: Pastikan session benar-benar sudah direset
        if ($hasActiveSession) {
            RateLimiter::hit($key, 60);
            return redirect()->back()
            ->with('session_alert', true)
            ->with('session_data', [
                'device' => $deviceInfo,
                'ip' => $ipInfo,
                'login_time' => $loginTime,
                'username' => $request->username,
                'password' => $request->password,
            ])
            ->withInput($request->except('password'));
        }
    
        // ✅ DOUBLE CHECK: Ambil ulang data user untuk memastikan session_id = null
        $user = User::find($user->id);
        if ($user->session_id !== null) {
            Log::error('CRITICAL: Session ID masih ada setelah check!', [
                'user' => $user->username,
                'session_id' => $user->session_id
            ]);
            $user->update(['session_id' => null]);
        }

        // ============================================================
        // CEK 2FA UNTUK ADMIN
        // ============================================================
        if ($user->role === 'admin' && $user->google2fa_enabled) {
            // Simpan data sementara untuk 2FA
            session([
                '2fa:user_id' => $user->id,
                '2fa:ip' => $request->ip(),
                '2fa:device' => $this->getDeviceInfo($request->userAgent()),
            ]);

            RateLimiter::clear($key);
            return redirect()->route('2fa.verify');
        }

        // ============================================================
        // PROSES LOGIN (TANPA 2FA)
        // ============================================================
        RateLimiter::clear($key);
        return $this->completeLogin($request, $user);
    }

    /**
     * 🔥 FORCE LOGIN - HARUS LEWAT 2FA JUGA!
     */
    public function forceLogin(Request $request)
    {
        Log::info('Force login executed', ['username' => $request->username]);

        // Validasi
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        // Cari user
        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return redirect()->back()
                ->withErrors(['username' => 'User tidak ditemukan.'])
                ->withInput($request->except('password'));
        }

        // Cek status user
        if ($user->status !== 'Active') {
            return redirect()->back()
                ->withErrors(['username' => 'Akun Anda tidak aktif. Silakan hubungi admin.'])
                ->withInput($request->except('password'));
        }

        // Cek password
        if (!Hash::check($request->password, $user->password)) {
            return redirect()->back()
                ->withErrors(['password' => 'Password salah.'])
                ->withInput($request->except('password'));
        }

        // 🔥 HAPUS SESSION LAMA
        if ($user->session_id) {
            DB::table('sessions')->where('id', $user->session_id)->delete();
            $user->update(['session_id' => null]);
            Log::info('Old session deleted for force login', [
                'user' => $user->username,
                'old_session_id' => $user->session_id
            ]);
        }

        // ============================================================
        // 🔥 CEK 2FA - TETAP DIPERIKSA UNTUK FORCE LOGIN!
        // ============================================================
        if ($user->google2fa_enabled) {
            Log::info('Force login - 2FA required', [
                'user' => $user->username,
                '2fa_enabled' => $user->google2fa_enabled
            ]);

            // Simpan data sementara untuk 2FA
            session([
                '2fa:user_id' => $user->id,
                '2fa:ip' => $request->ip(),
                '2fa:device' => $this->getDeviceInfo($request->userAgent()),
                '2fa:force_login' => true, // 🔥 Flag untuk tahu ini force login
            ]);

            // Hapus rate limit
            $key = 'login_attempts_' . $request->ip();
            RateLimiter::clear($key);

            return redirect()->route('2fa.verify');
        }

        // ============================================================
        // TANPA 2FA - LANGSUNG LOGIN
        // ============================================================
        Log::info('Force login - No 2FA required', [
            'user' => $user->username
        ]);

        return $this->completeLogin($request, $user);
    }
    /**
     * ============================================
     * 3. COMPLETE LOGIN (AFTER 2FA OR DIRECT)
     * ============================================
     */
    public function completeLogin(Request $request, User $user)
    {
        // ✅ SAVE SESSION SEBELUM REGENERATE
        // Pastikan session driver menyimpan dengan benar
        $request->session()->regenerate();
        $sessionId = session()->getId();
    
        // ✅ CEK APAKAH SESSION TERSIMPAN DI DATABASE
        if (config('session.driver') === 'database') {
            $sessionExists = DB::table('sessions')
                ->where('id', $sessionId)
                ->exists();
    
            if (!$sessionExists) {
                Log::error('Session not saved in database after regenerate!', [
                    'session_id' => $sessionId,
                    'user' => $user->username
                ]);
                
                // ✅ FORCE SAVE SESSION
                session()->save();
                
                // Cek lagi
                $sessionExists = DB::table('sessions')
                    ->where('id', $sessionId)
                    ->exists();
                    
                if (!$sessionExists) {
                    Log::critical('CRITICAL: Session still not saved!', [
                        'session_id' => $sessionId,
                        'user' => $user->username
                    ]);
                }
            }
        }
    
        // Set session
        session([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'user_username' => $user->username,
            '2fa_verified' => true,
            'is_logged_in' => true,
            'session_created_at' => time()
        ]);
    
        // ✅ SAVE SESSION SETELAH SET DATA
        session()->save();
    
        // Update user dengan session baru
        $deviceInfo = $this->getDeviceInfo($request->userAgent());
        $user->update([
            'session_id' => $sessionId,
            'session_ip' => $request->ip(),
            'session_device' => $deviceInfo,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'last_login_device' => $deviceInfo,
            'last_activity' => now(),
        ]);
    
        // ✅ VERIFIKASI: Cek apakah session_id di user sama dengan di tabel sessions
        $verifySession = DB::table('sessions')
            ->where('id', $sessionId)
            ->exists();
    
        Log::info('Login verification', [
            'user' => $user->username,
            'session_id' => $sessionId,
            'session_in_db' => $verifySession,
            'user_session_id' => $user->fresh()->session_id
        ]);

        // Redirect berdasarkan role
        return $this->redirectByRole($user->role)
            ->with('success', 'Selamat datang, ' . $user->name . '!');
    }

    /**
     * ============================================
     * 4. PROSES LOGOUT
     * ============================================
     */
    public function logout(Request $request)
    {
        if (session()->has('user_id')) {
            $user = User::find(session('user_id'));

            if ($user) {
                // Hapus session dari tabel sessions
                if ($user->session_id) {
                    DB::table('sessions')->where('id', $user->session_id)->delete();
                }

                // Update user
                $user->update([
                    'session_id' => null,
                    'session_ip' => null,
                    'session_device' => null,
                ]);

                $this->logActivity('Logout', 'Authentication', 'User logout', null, [
                    'username' => $user->username
                ]);

                Log::info('User logged out', [
                    'user' => $user->username,
                    'session_cleared' => true
                ]);
            }

            // Hapus semua session data
            session()->forget([
                'user_id',
                'user_name',
                'user_role',
                'user_username',
                '2fa_verified',
                'is_logged_in',
                'session_created_at'
            ]);
        }

        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/welcome')->with('success', 'Anda berhasil logout.');
    }

    /**
     * ============================================
     * 5. REGISTRASI USER (CUSTOMER)
     * ============================================
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'no_hp' => 'required|string|max:15',
            'kelas' => 'required|string|max:50',
            'nama_kelas' => 'required|string|max:50',
            'alergi' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'required|accepted',
        ], [
            'username.unique' => 'Username sudah digunakan. Silakan pilih username lain.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Password dan konfirmasi password tidak cocok.',
            'terms.required' => 'Anda harus menyetujui Syarat & Ketentuan.',
            'terms.accepted' => 'Anda harus menyetujui Syarat & Ketentuan.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        $kelas = $request->kelas . '-' . $request->nama_kelas;

        try {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'no_hp' => $request->no_hp,
                'kelas' => $kelas,
                'alergi' => $request->alergi,
                'password' => Hash::make($request->password),
                'role' => 'customer',
                'status' => 'Active',
            ]);

            $this->logActivity('Register', 'Authentication', 'New user registered', null, [
                'user_id' => $user->id,
                'username' => $user->username,
            ]);

            // Auto login setelah register
            $request->session()->regenerate();
            $sessionId = session()->getId();

            session([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'user_username' => $user->username,
                'is_logged_in' => true,
                'session_created_at' => time()
            ]);

            $user->update([
                'session_id' => $sessionId,
                'session_ip' => $request->ip(),
                'session_device' => $this->getDeviceInfo($request->userAgent()),
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
                'last_login_device' => $this->getDeviceInfo($request->userAgent()),
            ]);

            Log::info('New user registered', [
                'user_id' => $user->id,
                'username' => $user->username,
                'session_id' => $sessionId
            ]);

            return redirect()->route('customer.dashboard')
                ->with('success', 'Selamat datang, ' . $user->name . '! Akun Anda berhasil dibuat.');
        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat registrasi. Silakan coba lagi.')
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }

    /**
     * ============================================
     * 6. DASHBOARD BERDASARKAN ROLE
     * ============================================
     */
    public function adminDashboard()
    {
        if (!session()->has('user_id') || !session()->has('is_logged_in')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (session('user_role') !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $totalUsers = User::count();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalActiveUsers = User::where('status', 'Active')->count();
        $onlineUsers = User::whereNotNull('session_id')->count();

        $recentActivities = LogActivity::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalCustomers',
            'totalActiveUsers',
            'onlineUsers',
            'recentActivities'
        ));
    }

    public function customerDashboard()
    {
        if (!session()->has('user_id') || !session()->has('is_logged_in')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (session('user_role') !== 'customer') {
            abort(403, 'Unauthorized access.');
        }

        $user = User::find(session('user_id'));
        $orders = \App\Models\Order::where('id_customer', $user->id)
            ->with('menu')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('customer.dashboard', compact('user', 'orders'));
    }

    public function kasirDashboard()
    {
        if (!session()->has('user_id') || !session()->has('is_logged_in')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (session('user_role') !== 'kasir') {
            abort(403, 'Unauthorized access.');
        }

        return view('kasir.dashboard');
    }

    /**
     * ============================================
     * 7. HELPER FUNCTIONS
     * ============================================
     */
    private function redirectByRole($role)
    {
        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'kasir' => redirect()->route('kasir.dashboard'),
            'customer' => redirect()->route('customer.dashboard'),
            default => redirect('/login')->with('error', 'Role tidak valid.')
        };
    }

    private function getDeviceInfo($userAgent)
    {
        $device = 'Unknown';

        if (strpos($userAgent, 'Windows') !== false) {
            $device = ' Windows PC';
        } elseif (strpos($userAgent, 'Macintosh') !== false) {
            $device = ' Mac';
        } elseif (strpos($userAgent, 'iPhone') !== false) {
            $device = ' iPhone';
        } elseif (strpos($userAgent, 'iPad') !== false) {
            $device = ' iPad';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $device = ' Android';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $device = ' Linux';
        } else {
            $device = ' Perangkat lain';
        }

        // Tambahkan browser info
        if (strpos($userAgent, 'Chrome') !== false) {
            $device .= ' (Chrome)';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            $device .= ' (Firefox)';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            $device .= ' (Safari)';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            $device .= ' (Edge)';
        }

        return $device;
    }

    private function logActivity($aksi, $module, $keterangan, $oldData = null, $newData = null)
    {
        try {
            LogActivity::create([
                'user_id' => session('user_id'),
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
}