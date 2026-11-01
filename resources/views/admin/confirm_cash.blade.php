{{-- resources/views/admin/confirm_cash.blade.php --}}
@extends('app')
@include('layout.admin')
@section('content')
@yield('nav')

<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h1 class="text-2xl font-bold mb-4">Konfirmasi Pembayaran Tunai</h1>
            
            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Nomor Pesanan</span>
                    <span class="font-bold">{{ $orderId }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Customer</span>
                    <span class="font-bold">{{ $customerName }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">Total</span>
                    <span class="font-bold text-green-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-gray-600">Status</span>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full">Menunggu Konfirmasi</span>
                </div>
            </div>
            
            <div class="flex gap-3">
                <button onclick="confirmPayment()" 
                        class="flex-1 py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition-all">
                    Konfirmasi Pembayaran
                </button>
                <button onclick="cancelPayment()" 
                        class="flex-1 py-3 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition-all">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmPayment() {
    if (!confirm('Yakin konfirmasi pembayaran ini?')) return;
    
    fetch('{{ route("admin.process.confirm.cash", $orderId) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Pembayaran berhasil dikonfirmasi!');
            window.location.href = '{{ route("admin.orders") }}';
        } else {
            alert('❌ ' + data.message);
        }
    });
}

function cancelPayment() {
    if (!confirm('Yakin batalkan transaksi ini?')) return;
    window.location.href = '{{ route("admin.orders") }}';
}
</script>
@endsection