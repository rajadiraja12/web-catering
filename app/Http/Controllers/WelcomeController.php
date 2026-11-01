<?php
// app/Http/Controllers/WelcomeController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        // Jika sudah login, redirect ke dashboard sesuai role
        if (session()->has('user_id')) {
            $role = session('user_role');
            
            if ($role == 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($role == 'kasir') {
                return redirect()->route('kasir.dashboard');
            } elseif ($role == 'customer') {
                return redirect()->route('customer.dashboard');
            }
        }
        
        // Jika belum login, tampilkan welcome page
        return view('auth.welcome');
    }
}