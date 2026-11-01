<?php
// app/Http/Controllers/Auth/TwoFactorController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Tampilkan halaman verifikasi 2FA
     */
    public function showVerifyForm()
    {
        session()->save();
        Log::info('2FA Verify Form Accessed', [
            'session_id' => session()->getId(),
            'has_2fa_user_id' => session()->has('2fa:user_id'),
            '2fa_user_id' => session('2fa:user_id'),
            'all_session' => session()->all()
        ]);
        // Cek apakah ada data 2FA di session
        if (!session()->has('2fa:user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $userId = session('2fa:user_id');
        $user = User::find($userId);

        if (!$user) {
            return redirect('/login')->with('error', 'User tidak ditemukan.');
        }

        return view('auth.2fa_verify', compact('user'));
    }

    /**
     * Verifikasi kode 2FA - DIPERBAIKI UNTUK MIDDLEWARE
     */
    public function verify(Request $request)
    {
        Log::info('2FA Verify attempt', [
            'session_id' => session()->getId(),
            'has_session' => session()->has('2fa:user_id'),
            'ip' => $request->ip()
        ]);

        // Validasi input
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        // Cek apakah ada data 2FA di session
        if (!session()->has('2fa:user_id')) {
            Log::warning('2FA: No user_id in session', [
                'session_id' => session()->getId(),
            ]);
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $userId = session('2fa:user_id');
        $user = User::find($userId);

        if (!$user) {
            session()->forget('2fa:user_id');
            return redirect('/login')->with('error', 'User tidak ditemukan.');
        }

        // Verifikasi kode 2FA
        $valid = $this->google2fa->verifyKey(
            $user->google2fa_secret,
            $request->code,
            2
        );

        if (!$valid) {
            Log::warning('2FA: Invalid code', [
                'user' => $user->username,
                'code' => $request->code
            ]);
            return back()->withErrors(['code' => 'Kode verifikasi tidak valid. Silakan coba lagi.']);
        }

        // =============================================
        // 🔥 KODE VALID - PROSES LOGIN
        // =============================================

        // 1. Hapus data 2FA dari session
        session()->forget('2fa:user_id');
        session()->forget('2fa:ip');
        session()->forget('2fa:device');

        // 2. 🔥 REGENERATE SESSION UNTUK KEAMANAN
        // Tapi kita harus pastikan data session tetap ada
        $oldSessionId = session()->getId();
        $request->session()->regenerate();
        $currentSessionId = session()->getId();

        Log::info('Session regenerated for 2FA', [
            'user' => $user->username,
            'old_session' => $oldSessionId,
            'new_session' => $currentSessionId
        ]);

        // 3. 🔥 SET SESSION USER LENGKAP
        session([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'user_username' => $user->username,
            'user_email' => $user->email,
            'user_status' => $user->status,
            '2fa_verified' => true,
            'is_logged_in' => true,
            'session_created_at' => now()->toDateTimeString(),
        ]);

        // 4. 🔥 SAVE SESSION - PASTIKAN TERSIMPAN
        session()->save();

        // 5. 🔥 UPDATE USER DI DATABASE
        $currentIp = $request->ip();
        $currentDevice = $this->getDeviceInfo($request->userAgent());

        try {
            $user->update([
                'session_id' => $currentSessionId, // 🔥 Penting: Update session_id
                'session_ip' => $currentIp,
                'session_device' => $currentDevice,
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
                'last_login_device' => $this->getDeviceInfo($request->userAgent()),
                'last_activity' => now(),
            ]);

            Log::info('2FA verified and user logged in successfully', [
                'user' => $user->username,
                'session_id' => $currentSessionId,
                'user_id_session' => session('user_id'),
                'is_logged_in' => session('is_logged_in')
            ]);

        } catch (\Exception $e) {
            Log::error('2FA login update failed: ' . $e->getMessage());
        }

        // 6. 🔥 VERIFIKASI SESSION SEBELUM REDIRECT
        if (!session()->has('user_id')) {
            Log::error('CRITICAL: Session user_id not set after 2FA!');
            
            // Set ulang session
            session([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'user_username' => $user->username,
                '2fa_verified' => true,
                'is_logged_in' => true,
            ]);
            session()->save();

            // Update ulang user
            $user->update([
                'session_id' => session()->getId(),
                'session_ip' => $request->ip(),
                'session_device' => $this->getDeviceInfo($request->userAgent()),
                'last_activity' => now(),
            ]);
        }

        // 7. 🔥 PASTIKAN SESSION COOKIE TERKIRIM
        $request->session()->save();

        // 8. Redirect berdasarkan role
        return $this->redirectBasedOnRole($user->role, $user);
    }

    /**
     * Mendapatkan informasi perangkat
     */
    private function getDeviceInfo($userAgent)
    {
        $device = 'Unknown';
        
        if (strpos($userAgent, 'Windows') !== false) {
            $device = 'Windows PC';
        } elseif (strpos($userAgent, 'Macintosh') !== false) {
            $device = 'Mac';
        } elseif (strpos($userAgent, 'iPhone') !== false) {
            $device = 'iPhone';
        } elseif (strpos($userAgent, 'iPad') !== false) {
            $device = 'iPad';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $device = 'Android';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $device = 'Linux';
        }
        
        if (strpos($userAgent, 'Chrome') !== false) {
            $device .= ' - Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            $device .= ' - Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            $device .= ' - Safari';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            $device .= ' - Edge';
        }
        
        return $device;
    }

    /**
     * Setup 2FA untuk user
     */
    public function setup(Request $request)
    {
        // Cek login
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $userId = session('user_id');
        $user = User::find($userId);

        if (!$user) {
            return redirect('/login')->with('error', 'User tidak ditemukan.');
        }

        // Generate secret key
        $secret = $this->google2fa->generateSecretKey();

        // Generate QR Code
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name', 'Hanif Catering'),
            $user->email ?? $user->username,
            $secret
        );

        return view('auth.2fa_setup', compact('user', 'secret', 'qrCodeUrl'));
    }

    /**
     * Enable 2FA
     */
    public function enable(Request $request)
    {
        $request->validate([
            'secret' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        // Cek login
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $userId = session('user_id');
        $user = User::find($userId);

        if (!$user) {
            return redirect('/login')->with('error', 'User tidak ditemukan.');
        }

        // Verifikasi kode
        $valid = $this->google2fa->verifyKey(
            $request->secret,
            $request->code,
            2
        );

        if (!$valid) {
            return back()->withErrors(['code' => 'Kode verifikasi tidak valid. Silakan coba lagi.']);
        }

        // Simpan secret ke database
        $user->update([
            'google2fa_secret' => $request->secret,
            'google2fa_enabled' => true,
            'google2fa_verified_at' => now(),
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Google Authenticator berhasil diaktifkan!');
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request)
    {
        // Cek login
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $userId = session('user_id');
        $user = User::find($userId);

        if (!$user) {
            return redirect('/login')->with('error', 'User tidak ditemukan.');
        }

        $user->update([
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'google2fa_verified_at' => null,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Google Authenticator berhasil dinonaktifkan!');
    }

    private function redirectBasedOnRole($role)
    {
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($role === 'kasir') {
            return redirect()->route('kasir.dashboard');
        } else {
            return redirect()->route('customer.dashboard');
        }
    }
}