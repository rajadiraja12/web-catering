<?php
// app/Http/Middleware/CheckActiveSession.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckActiveSession
{
    public function handle(Request $request, Closure $next)
    {
        // Skip untuk route public
        $excludeRoutes = ['login', '2fa*', 'welcome', 'register', 'logout'];
        foreach ($excludeRoutes as $route) {
            if ($request->is($route)) {
                return $next($request);
            }
        }

        if (!session()->has('user_id') || !session()->has('is_logged_in')) {
            return redirect('/welcome')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = User::find(session('user_id'));

        if (!$user) {
            $this->clearSession($request);
            return redirect('/login')->with('error', 'User tidak ditemukan.');
        }

        // ============================================================
        // CEK ROLE AKSES
        // ============================================================
        $currentRole = session('user_role');
        $requestedPath = $request->path();

        $roleAccess = [
            'admin' => ['admin/*', 'dashboard', 'users', 'settings', 'logs'],
            'kasir' => ['kasir/*', 'transaksi', 'penjualan', 'order*'],
            'customer' => ['customer/*', 'produk', 'keranjang', 'checkout', 'order*']
        ];

        $allowed = false;
        foreach ($roleAccess[$currentRole] ?? [] as $pattern) {
            if ($request->is($pattern)) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            Log::warning('Role access denied', [
                'user' => $user->username,
                'role' => $currentRole,
                'path' => $requestedPath
            ]);

            return redirect($this->getDashboardByRole($currentRole))
                ->with('error', ' Akses ditolak! Anda tidak memiliki izin untuk halaman ini.');
        }

        // ============================================================
        // CEK SINGLE SESSION
        // ============================================================
        $currentSessionId = session()->getId();
        $storedSessionId = $user->session_id;

        Log::info('Session check', [
            'user' => $user->username,
            'stored_session' => $storedSessionId,
            'current_session' => $currentSessionId,
            'ip' => $request->ip(),
            'session_driver' => config('session.driver')
        ]);

        // ===== KONDISI 1: Session ID NULL =====
        if (!$storedSessionId) {
            // ✅ Update dengan session baru
            $user->update([
                'session_id' => $currentSessionId,
                'session_ip' => $request->ip(),
                'session_device' => $this->getDeviceInfo($request->userAgent()),
                'last_activity' => now(),
            ]);

            Log::info('Session synced (was null)', [
                'user' => $user->username,
                'session_id' => $currentSessionId
            ]);

            return $next($request);
        }

        // ===== KONDISI 2: Session ID SAMA =====
        if ($storedSessionId === $currentSessionId) {
            // ✅ Session valid, update aktivitas
            $user->update([
                'last_activity' => now(),
                'session_ip' => $request->ip(),
            ]);

            Log::info('Session match, activity updated', [
                'user' => $user->username,
                'session_id' => $currentSessionId
            ]);

            return $next($request);
        }

        // ============================================================
        // KONDISI 3: Session ID BERBEDA
        // ============================================================
        
        // ✅ CEK APAKAH SESSION LAMA MASIH ADA DI TABEL SESSIONS
        $sessionExists = DB::table('sessions')
            ->where('id', $storedSessionId)
            ->exists();

        // ✅ CEK APAKAH SESSION BARU ADA DI TABEL SESSIONS
        $newSessionExists = DB::table('sessions')
            ->where('id', $currentSessionId)
            ->exists();

        Log::info('Session existence check', [
            'user' => $user->username,
            'old_session_exists' => $sessionExists,
            'new_session_exists' => $newSessionExists,
            'old_session' => $storedSessionId,
            'new_session' => $currentSessionId
        ]);

        // ===== CASE A: Session lama TIDAK ADA di tabel =====
        if (!$sessionExists) {
            // ⚠️ HATI-HATI: Ini bisa terjadi karena:
            // 1. Session expired (sudah dihapus)
            // 2. Session dihapus manual
            // 3. Session driver tidak menyimpan dengan benar
            
            // ✅ PERBAIKAN: Cek apakah ini session yang baru dibuat
            if ($newSessionExists) {
                // Session baru ada di tabel, berarti ini sesi yang valid
                // Tapi user di database masih punya session lama
                // Update user dengan session baru
                $user->update([
                    'session_id' => $currentSessionId,
                    'session_ip' => $request->ip(),
                    'session_device' => $this->getDeviceInfo($request->userAgent()),
                    'last_activity' => now(),
                ]);

                Log::info('Session replaced (old session not found, new session exists)', [
                    'user' => $user->username,
                    'old_session' => $storedSessionId,
                    'new_session' => $currentSessionId
                ]);

                return $next($request);
            } else {
                // ⚠️ KRITIS: Session baru juga tidak ada di tabel!
                // Ini berarti session driver bermasalah
                Log::error('CRITICAL: New session not found in DB!', [
                    'user' => $user->username,
                    'current_session' => $currentSessionId,
                    'session_driver' => config('session.driver')
                ]);

                // Regenerate session untuk memastikan
                $request->session()->regenerate();
                $newId = session()->getId();

                $user->update([
                    'session_id' => $newId,
                    'session_ip' => $request->ip(),
                    'session_device' => $this->getDeviceInfo($request->userAgent()),
                    'last_activity' => now(),
                ]);

                Log::info('Session regenerated due to critical error', [
                    'user' => $user->username,
                    'new_session' => $newId
                ]);

                return $next($request);
            }
        }

        // ===== CASE B: Session lama MASIH ADA =====
        // Cek apakah session masih aktif (belum expired)
        $sessionData = DB::table('sessions')
            ->where('id', $storedSessionId)
            ->first();

        if ($sessionData) {
            $lifetime = config('session.lifetime') * 60;
            $isExpired = (time() - $sessionData->last_activity) > $lifetime;

            Log::info('Session status', [
                'user' => $user->username,
                'is_expired' => $isExpired,
                'last_activity' => $sessionData->last_activity,
                'lifetime' => $lifetime,
                'time_now' => time()
            ]);

            if ($isExpired) {
                // ✅ Session expired, hapus dan ganti dengan yang baru
                DB::table('sessions')->where('id', $storedSessionId)->delete();
                $user->update([
                    'session_id' => $currentSessionId,
                    'session_ip' => $request->ip(),
                    'session_device' => $this->getDeviceInfo($request->userAgent()),
                    'last_activity' => now(),
                ]);

                Log::info('Session replaced (old session expired)', [
                    'user' => $user->username,
                    'old_session' => $storedSessionId,
                    'new_session' => $currentSessionId
                ]);

                return $next($request);
            }

            // ===== SESSION MASIH AKTIF DI PERANGKAT LAIN =====
            // TOLAK AKSES!
            Log::warning('❌ SESSION MISMATCH - Active session on another device', [
                'user' => $user->username,
                'stored_session' => $storedSessionId,
                'current_session' => $currentSessionId,
                'active_device' => $user->session_device,
                'active_ip' => $user->session_ip,
                'attempt_ip' => $request->ip()
            ]);

            $this->clearSession($request);
            return redirect('/login')->with('error',
                ' Akun ini sedang aktif di perangkat lain (' .
                ($user->session_device ?? 'Unknown') .
                '). Harap logout terlebih dahulu dari perangkat tersebut.'
            );
        }

        // ===== FALLBACK: Jika tidak ada kondisi yang terpenuhi =====
        Log::warning('Session check fell through to fallback', [
            'user' => $user->username,
            'stored_session' => $storedSessionId,
            'current_session' => $currentSessionId
        ]);

        return $next($request);
    }

    private function getDashboardByRole($role)
    {
        return match ($role) {
            'admin' => '/admin/dashboard',
            'kasir' => '/kasir/dashboard',
            'customer' => '/customer/dashboard',
            default => '/login'
        };
    }

    private function clearSession($request)
    {
        session()->forget([
            'user_id',
            'user_name',
            'user_role',
            'user_username',
            '2fa_verified',
            'is_logged_in',
            'session_created_at'
        ]);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
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
}