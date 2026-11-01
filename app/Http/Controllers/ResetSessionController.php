<?php
// app/Http/Controllers/ResetSessionController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResetSessionController extends Controller
{
    /**
     * Reset session untuk user tertentu
     */
    public function resetUserSession($username)
    {
        try {
            $user = User::where('username', $username)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => "User dengan username '{$username}' tidak ditemukan"
                ], 404);
            }
            
            // Reset session_id
            $user->update(['session_id' => null]);
            
            // Hapus session dari database
            DB::table('sessions')->where('user_id', $user->id)->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Session berhasil direset untuk user: {$username}",
                'data' => [
                    'user_id' => $user->id,
                    'username' => $user->username,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset semua session
     */
    public function resetAllSessions()
    {
        try {
            // Reset semua session_id di users
            User::query()->update(['session_id' => null]);
            
            // Hapus semua session
            DB::table('sessions')->truncate();
            
            return response()->json([
                'success' => true,
                'message' => 'Semua session berhasil direset!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cek status session user
     */
    public function checkSession($username)
    {
        try {
            $user = User::where('username', $username)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => "User '{$username}' tidak ditemukan"
                ], 404);
            }
            
            $sessionExists = false;
            $sessionData = null;
            
            if ($user->session_id) {
                $sessionExists = DB::table('sessions')
                    ->where('id', $user->session_id)
                    ->exists();
                
                if ($sessionExists) {
                    $sessionData = DB::table('sessions')
                        ->where('id', $user->session_id)
                        ->first();
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'name' => $user->name,
                        'session_id' => $user->session_id,
                        'status' => $user->status,
                    ],
                    'session_exists_in_db' => $sessionExists,
                    'session_data' => $sessionData,
                    'current_session_id' => session()->getId(),
                    'is_current_user' => $user->session_id === session()->getId(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi error: ' . $e->getMessage()
            ], 500);
        }
    }
}