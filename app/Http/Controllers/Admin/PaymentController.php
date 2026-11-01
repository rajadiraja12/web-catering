<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    /**
     * Halaman setting payment
     */
    public function index()
    {
        $payments = Payment::all();
        return view('admin.payment', compact('payments'));
    }

    /**
     * Update payment gateway (API Settings)
     */
    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'merchant_code' => 'nullable|string',
            'client_key' => 'nullable|string',
            'is_sandbox' => 'boolean',
            // TAMBAHKAN untuk saldo
            'min_saldo' => 'nullable|numeric|min:0',
            'max_saldo' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $settings = $payment->settings ?? [];
        
        // Update client_key jika ada
        if ($request->client_key) {
            $settings['client_key'] = $request->client_key;
        }
        
        // Update settings untuk saldo
        if ($request->has('min_saldo')) {
            $settings['min_saldo'] = $request->min_saldo;
        }
        if ($request->has('max_saldo')) {
            $settings['max_saldo'] = $request->max_saldo;
        }

        $payment->update([
            'api_key' => $request->api_key,
            'api_secret' => $request->api_secret,
            'merchant_code' => $request->merchant_code,
            'is_sandbox' => $request->is_sandbox ?? true,
            'settings' => $settings,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment gateway berhasil diupdate',
            'data' => $payment
        ]);
    }

    /**
     * Toggle active payment gateway
     */
    public function toggleActive($id)
    {
        $payment = Payment::findOrFail($id);
        
        // Jika mengaktifkan, nonaktifkan yang lain
        if (!$payment->is_active) {
            Payment::where('is_active', true)->update(['is_active' => false]);
        }
        
        $payment->is_active = !$payment->is_active;
        $payment->save();

        return response()->json([
            'success' => true,
            'message' => 'Status payment gateway berhasil diubah',
            'data' => $payment
        ]);
    }

    /**
     * Toggle payment method
     * UPDATE: Tambahkan cash dan saldo
     */
    public function toggleMethod(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'method' => 'required|in:qris,bank_transfer,credit_card,ewallet,retail,cash,saldo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $field = $request->method . '_enabled';
        $payment->$field = !$payment->$field;
        $payment->save();

        $status = $payment->$field ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json([
            'success' => true,
            'message' => "Metode pembayaran {$payment->name} - " . strtoupper($request->method) . " berhasil {$status}",
            'data' => $payment
        ]);
    }

    /**
     * Update Bank List
     */
    public function updateBanks(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'banks' => 'required|array',
            'banks.*' => 'string|in:bca,bni,bri,mandiri,cimb,danamon,btpn,maybank,permata',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $payment->bank_list = $request->banks;
        $payment->save();

        return response()->json([
            'success' => true,
            'message' => 'Daftar bank berhasil diupdate',
            'data' => $payment
        ]);
    }

    /**
     * Upload QRIS Image
     */
    public function uploadQris(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'qris_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Hapus gambar lama
        if ($payment->qris_image && file_exists(public_path($payment->qris_image))) {
            unlink(public_path($payment->qris_image));
        }

        $file = $request->file('qris_image');
        $filename = 'qris_' . time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('qris'), $filename);
        
        $payment->qris_image = 'qris/' . $filename;
        $payment->save();

        return response()->json([
            'success' => true,
            'message' => 'QRIS berhasil diupload',
            'data' => $payment
        ]);
    }

    /**
     * TAMBAHKAN: Create new payment gateway
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'gateway' => 'required|string|in:midtrans,tripay,xendit,cash,saldo',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $payment = Payment::create([
            'name' => $request->name,
            'gateway' => $request->gateway,
            'is_active' => $request->is_active ?? false,
            'is_sandbox' => true,
            'settings' => [],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment gateway berhasil ditambahkan',
            'data' => $payment
        ]);
    }

    /**
     * TAMBAHKAN: Delete payment gateway
     */
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment gateway berhasil dihapus'
        ]);
    }
}