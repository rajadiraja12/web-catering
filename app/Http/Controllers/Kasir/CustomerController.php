<?php
// app/Http/Controllers/Kasir/CustomerController.php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    /**
     * Halaman Manajemen Customer
     */
    public function index()
    {
        // Cek login kasir
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = User::find(session('user_id'));
        
        if (!$user || $user->role !== 'kasir') {
            return redirect('/login')->with('error', 'Akses ditolak. Hanya untuk kasir.');
        }

        // Ambil daftar customer
        $customers = User::where('role', 'customer')
            ->orderBy('name')
            ->paginate(15);

        return view('kasir.customers', compact('customers', 'user'));
    }

    /**
     * Search Customer (AJAX)
     */
    public function search(Request $request)
    {
        $search = $request->search;
        
        $customers = User::where('role', 'customer')
            ->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('username', 'LIKE', "%{$search}%")
                  ->orWhere('no_hp', 'LIKE', "%{$search}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'username', 'kelas', 'no_hp', 'status']);

        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
    }

    /**
     * Store Customer Baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'kelas' => 'required|string|max:2',
            'nama_kelas' => 'required|string|max:50',
            'no_hp' => 'required|string|max:15',
            'alergi' => 'nullable|string|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Gabungkan kelas + nama_kelas
            $kelasFull = $request->kelas . '-' . $request->nama_kelas;

            $customer = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'kelas' => $kelasFull,
                'no_hp' => $request->no_hp,
                'alergi' => $request->alergi ?? '-',
                'password' => Hash::make($request->password),
                'role' => 'customer',
                'status' => 'Active',
            ]);

            Log::info('Customer added by kasir', [
                'kasir_id' => session('user_id'),
                'customer_id' => $customer->id,
                'customer_name' => $customer->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer berhasil ditambahkan!',
                'data' => $customer
            ]);

        } catch (\Exception $e) {
            Log::error('Add customer error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Customer
     */
    public function update(Request $request, $id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'kelas' => 'required|string|max:2',
            'nama_kelas' => 'required|string|max:50',
            'no_hp' => 'required|string|max:15',
            'alergi' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->except(['password']);
            $data['kelas'] = $request->kelas . '-' . $request->nama_kelas;
            
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $customer->update($data);

            Log::info('Customer updated by kasir', [
                'kasir_id' => session('user_id'),
                'customer_id' => $customer->id,
                'customer_name' => $customer->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer berhasil diupdate!',
                'data' => $customer
            ]);

        } catch (\Exception $e) {
            Log::error('Update customer error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle Status Customer
     */
    public function toggleStatus($id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);
        
        $newStatus = $customer->status === 'Active' ? 'Non Aktif' : 'Active';
        $customer->update(['status' => $newStatus]);

        Log::info('Customer status toggled by kasir', [
            'kasir_id' => session('user_id'),
            'customer_id' => $customer->id,
            'new_status' => $newStatus
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status customer berhasil diubah!',
            'data' => $customer
        ]);
    }

    /**
     * Delete Customer (hanya jika tidak ada order)
     */
    public function destroy($id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);
        
        // Cek apakah customer memiliki order
        $orderCount = $customer->orders()->count();
        if ($orderCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Customer tidak dapat dihapus karena memiliki ' . $orderCount . ' order terkait!'
            ], 400);
        }

        $customerName = $customer->name;
        $customer->delete();

        Log::info('Customer deleted by kasir', [
            'kasir_id' => session('user_id'),
            'customer_id' => $id,
            'customer_name' => $customerName
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customer berhasil dihapus!'
        ]);
    }

    /**
     * Edit Customer (Get data for edit modal)
     */
    public function edit($id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $customer
        ]);
    }
}