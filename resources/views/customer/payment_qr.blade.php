@extends('app')
@include('layout.customer')
@section('content')
@yield('nav')
<br>
<br>
<br>
<br>

<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Pembayaran QRIS</h1>
            <p class="text-sm text-gray-500">Scan QR Code untuk menyelesaikan pembayaran</p>
        </div>
        <a href="{{ route('customer.pending-payments') }}" 
            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    {{-- Info Order --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-sm text-gray-500">Order ID</p>
                <p class="font-semibold text-gray-800">{{ $order_id }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total</p>
                <p class="font-semibold text-blue-600">Rp {{ number_format($total, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Metode</p>
                <p class="font-semibold text-gray-800">QRIS</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Status</p>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                    ⏳ Menunggu Pembayaran
                </span>
            </div>
        </div>
    </div>

    {{-- QR Code --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 text-center max-w-lg mx-auto">
        <div class="mb-4">
            <img src="{{ $qr_image }}" alt="QR Code" class="mx-auto w-64 h-64 object-contain">
        </div>
        
        <p class="text-sm text-gray-500 mb-2">Scan QR Code di atas menggunakan aplikasi pembayaran (GoPay, OVO, DANA, dll)</p>
        
        {{-- Timer --}}
        <div class="mt-4 p-4 bg-gray-50 rounded-xl">
            <p class="text-sm text-gray-600">Sisa waktu:</p>
            <p class="text-2xl font-bold text-red-600" id="countdown">
                {{ $expires_at->diff(now())->format('%H:%I:%S') }}
            </p>
        </div>

        {{-- Status Check --}}
        <div class="mt-4">
            <button onclick="checkPaymentStatus()" 
                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all">
                🔄 Cek Status Pembayaran
            </button>
        </div>

        <div id="status-message" class="mt-4 text-sm"></div>
    </div>

    {{-- Info --}}
    <div class="mt-6 max-w-lg mx-auto">
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm text-blue-700">
                        <strong>Catatan:</strong> QR Code akan otomatis kadaluarsa dalam 
                        <strong>{{ $expires_at->diff(now())->format('%H jam %I menit %S detik') }}</strong>.
                        Jika sudah kadaluarsa, silakan buat pesanan baru.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Countdown Timer
    let expiresAt = new Date('{{ $expires_at->toDateTimeString() }}').getTime();
    
    function updateCountdown() {
        let now = new Date().getTime();
        let distance = expiresAt - now;
        
        if (distance < 0) {
            document.getElementById('countdown').innerHTML = '⏰ Kadaluarsa';
            document.getElementById('countdown').className = 'text-2xl font-bold text-red-600';
            return;
        }
        
        let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        let seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        document.getElementById('countdown').innerHTML = 
            String(hours).padStart(2, '0') + ':' + 
            String(minutes).padStart(2, '0') + ':' + 
            String(seconds).padStart(2, '0');
    }
    
    // Update setiap detik
    setInterval(updateCountdown, 1000);
    updateCountdown();
    
    // Cek Status
    let checking = false;
    
    function checkPaymentStatus() {
        if (checking) return;
        checking = true;
        
        let btn = document.querySelector('button[onclick="checkPaymentStatus()"]');
        let msg = document.getElementById('status-message');
        
        btn.disabled = true;
        btn.innerHTML = '⏳ Mengecek...';
        msg.innerHTML = '';
        
        fetch('{{ route("customer.payment.check-status", ["order_id" => $order_id]) }}')
            .then(response => response.json())
            .then(data => {
                checking = false;
                btn.disabled = false;
                btn.innerHTML = '🔄 Cek Status Pembayaran';
                
                if (data.status === 'paid') {
                    msg.innerHTML = `
                        <div class="p-4 bg-green-100 border border-green-200 rounded-xl text-green-700">
                            ✅ Pembayaran berhasil! 
                            <a href="{{ route('customer.orders') }}" class="underline font-medium">
                                Lihat Pesanan Saya
                            </a>
                        </div>
                    `;
                    // Redirect setelah 3 detik
                    setTimeout(() => {
                        window.location.href = '{{ route('customer.orders') }}';
                    }, 3000);
                } else if (data.status === 'pending') {
                    msg.innerHTML = `
                        <div class="p-4 bg-yellow-100 border border-yellow-200 rounded-xl text-yellow-700">
                            ⏳ Masih menunggu pembayaran. Silakan scan QR Code.
                        </div>
                    `;
                } else if (data.status === 'expired') {
                    msg.innerHTML = `
                        <div class="p-4 bg-red-100 border border-red-200 rounded-xl text-red-700">
                            ❌ QR Code sudah kadaluarsa. Silakan buat pesanan baru.
                            <a href="{{ route('customer.pending-payments') }}" class="underline font-medium">
                                Kembali
                            </a>
                        </div>
                    `;
                } else {
                    msg.innerHTML = `
                        <div class="p-4 bg-gray-100 border border-gray-200 rounded-xl text-gray-700">
                            ℹ️ Status: ${data.status || 'Tidak diketahui'}
                        </div>
                    `;
                }
            })
            .catch(error => {
                checking = false;
                btn.disabled = false;
                btn.innerHTML = '🔄 Cek Status Pembayaran';
                msg.innerHTML = `
                    <div class="p-4 bg-red-100 border border-red-200 rounded-xl text-red-700">
                        ❌ Gagal mengecek status. Silakan coba lagi.
                    </div>
                `;
            });
    }
    
    // Auto check setiap 10 detik
    setInterval(checkPaymentStatus, 10000);
    
    // Check saat pertama kali load
    setTimeout(checkPaymentStatus, 3000);
</script>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .bg-white.rounded-2xl {
        animation: fadeIn 0.3s ease-out;
    }
</style>

@endsection