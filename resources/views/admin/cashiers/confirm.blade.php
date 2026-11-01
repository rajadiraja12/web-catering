{{-- resources/views/admin/cashier/confirm.blade.php --}}

@extends('app')
@include('layout.admin')
@section('content')
@yield('nav')
<br><br><br><br>
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            {{-- Header --}}
            <div class="text-center mb-6">
                <div class="text-5xl mb-2"></div>
                <h1 class="text-2xl font-bold text-gray-800">Konfirmasi Pembayaran Tunai</h1>
                <p class="text-sm text-gray-500">Verifikasi dan konfirmasi pembayaran cash</p>
            </div>

            {{-- Detail Pesanan --}}
            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                <div class="grid grid-cols-2 gap-2">
                    <div class="col-span-2 border-b pb-2">
                        <p class="text-xs text-gray-500">Nomor Pesanan</p>
                        <p class="font-mono font-bold text-lg text-blue-600">{{ $orderId }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Customer</p>
                        <p class="font-semibold">{{ $customer->name ?? 'N/A' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500">Total</p>
                        <p class="font-bold text-xl text-green-600">Rp {{ number_format($total, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Metode</p>
                        <p class="font-semibold">Tunai (Cash)</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500">Status</p>
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium">
                            Menunggu Konfirmasi
                        </span>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs text-gray-500">Berlaku hingga</p>
                        <p class="font-semibold text-red-500">{{ $pending->expires_at ? date('d-m-Y H:i', strtotime($pending->expires_at)) : '24 jam' }}</p>
                    </div>
                </div>
            </div>

            {{-- Detail Menu --}}
            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                <h3 class="font-semibold text-gray-700 mb-3">Detail Pesanan</h3>
                @foreach($pending->cart_data as $item)
                <div class="flex justify-between py-2 border-b border-gray-200 last:border-0">
                    <div>
                        <p class="font-medium">{{ $item['menu']['nama_menu'] ?? 'Menu' }}</p>
                        <p class="text-xs text-gray-500">{{ ucfirst($item['paket']) }} × {{ $item['quantity'] }}</p>
                    </div>
                    <p class="font-semibold">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</p>
                </div>
                @endforeach
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex gap-3">
                <button onclick="confirmPayment()" 
                        class="flex-1 py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Konfirmasi Pembayaran
                </button>
                
                <button onclick="window.location.href='{{ route('admin.cashier.scan') }}'" 
                        class="flex-1 py-3 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali
                </button>
            </div>

            {{-- Tombol Batal --}}
            <button onclick="cancelPayment()" 
                    class="w-full mt-3 py-2 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition-all">
                Batalkan Transaksi
            </button>
        </div>
    </div>
</div>

<script>
    // ============================================
    // CONFIRM PAYMENT
    // ============================================
    function confirmPayment() {
        if (!confirm('Yakin ingin mengkonfirmasi pembayaran ini?')) return;
        
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = 'memproses...';
        btn.disabled = true;
        
        fetch('{{ route("cashier.process.confirm", $orderId) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('✅ ' + data.message, 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("admin.cashier.dashboard") }}';
                }, 1500);
            } else {
                showNotification('❌ ' + data.message, 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            showNotification('❌ ' + error.message, 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    // ============================================
    // CANCEL PAYMENT
    // ============================================
    function cancelPayment() {
        if (!confirm('Yakin ingin membatalkan transaksi ini?')) return;
        
        fetch('/cashier/status/{{ $orderId }}', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'pending') {
                // Hapus pending payment
                fetch('/cashier/cancel/{{ $orderId }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                })
                .then(() => {
                    showNotification('❌ Transaksi dibatalkan', 'error');
                    setTimeout(() => {
                        window.location.href = '{{ route("admin.cashier.dashboard") }}';
                    }, 1000);
                });
            } else {
                window.location.href = '{{ route("admin.cashier.dashboard") }}';
            }
        })
        .catch(() => {
            window.location.href = '{{ route("admin.cashier.dashboard") }}';
        });
    }

    // ============================================
    // SHOW NOTIFICATION
    // ============================================
    function showNotification(message, type = 'info') {
        const oldNotification = document.getElementById('custom-notification');
        if (oldNotification) oldNotification.remove();
        
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            info: 'bg-blue-500',
            warning: 'bg-yellow-500'
        };
        
        const notification = document.createElement('div');
        notification.id = 'custom-notification';
        notification.className = `fixed top-4 right-4 z-50 transform transition-all duration-500 translate-x-0 opacity-100 ${colors[type] || colors.info} text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-3 max-w-md`;
        notification.innerHTML = `
            <span>${message}</span>
            <button onclick="this.parentElement.remove()" class="text-white hover:text-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) notification.remove();
        }, 5000);
    }
</script>

@endsection