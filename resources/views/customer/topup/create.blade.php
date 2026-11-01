{{-- resources/views/customer/topup/create.blade.php --}}

@extends('app')
@include('layout.customer')
@section('content')
@yield('nav')
<br><br><br><br>

<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-blue-100 rounded-xl">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">TopUp Saldo</h1>
                    <p class="text-sm text-gray-500">Isi saldo Anda melalui Midtrans</p>
                </div>
            </div>

            {{-- Saldo Saat Ini --}}
            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                <p class="text-sm text-gray-500">Saldo Saat Ini</p>
                <p class="text-2xl font-bold text-green-600">Rp {{ number_format($user->saldo, 0, ',', '.') }}</p>
            </div>

            <form id="topupForm">
                @csrf

                {{-- Nominal --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nominal TopUp</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-3">
                        <button type="button" onclick="setNominal(10000)" class="p-2 border-2 border-gray-200 rounded-lg hover:border-blue-500 transition-all">
                            Rp 10.000
                        </button>
                        <button type="button" onclick="setNominal(25000)" class="p-2 border-2 border-gray-200 rounded-lg hover:border-blue-500 transition-all">
                            Rp 25.000
                        </button>
                        <button type="button" onclick="setNominal(50000)" class="p-2 border-2 border-gray-200 rounded-lg hover:border-blue-500 transition-all">
                            Rp 50.000
                        </button>
                        <button type="button" onclick="setNominal(100000)" class="p-2 border-2 border-gray-200 rounded-lg hover:border-blue-500 transition-all">
                            Rp 100.000
                        </button>
                    </div>
                    <input type="number" 
                           id="nominal" 
                           name="nominal" 
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                           placeholder="Masukkan nominal topup (min Rp 10.000)"
                           min="10000"
                           max="10000000"
                           required>
                    <p class="text-xs text-gray-400 mt-1">Minimal Rp 10.000, Maksimal Rp 10.000.000</p>
                </div>

                {{-- Info Pembayaran --}}
                <div class="bg-blue-50 rounded-xl p-4 mb-6 border border-blue-200">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-blue-800">Pembayaran via Midtrans</p>
                            <p class="text-xs text-blue-600">Anda akan diarahkan ke halaman pembayaran Midtrans setelah mengklik tombol TopUp. Pembayaran dapat dilakukan melalui QRIS, Transfer Bank, E-Wallet, dan Kartu Kredit.</p>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Metode Pembayaran</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <div class="border-2 rounded-xl p-3 cursor-pointer hover:border-blue-500 transition-all border-gray-200"
                            onclick="selectMethod(this, 'qris')">
                            <div class="text-center">
                                <span class="text-2xl">📱</span>
                                <p class="text-sm font-medium">QRIS</p>
                                <p class="text-xs text-gray-500">Scan QR Code</p>
                            </div>
                        </div>
                        <div class="border-2 rounded-xl p-3 cursor-pointer hover:border-blue-500 transition-all border-gray-200"
                            onclick="selectMethod(this, 'credit_card')">
                            <div class="text-center">
                                <span class="text-2xl">💳</span>
                                <p class="text-sm font-medium">Kartu Kredit</p>
                                <p class="text-xs text-gray-500">Visa/Mastercard</p>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="payment_method" name="payment_method" value="qris">
                </div>

                {{-- Submit --}}
                <button type="submit" 
                        class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-all hover:scale-[1.02]">
                    <span id="btnText">TopUp Sekarang</span>
                    <span id="btnLoading" class="hidden">
                        <svg class="inline w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Memproses...
                    </span>
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Midtrans Snap Script --}}
@if(isset($midtrans) && $midtrans)
    @php
        $clientKey = $midtrans->api_key;
        $isSandbox = $midtrans->is_sandbox;
        $snapUrl = $isSandbox 
            ? 'https://app.sandbox.midtrans.com/snap/snap.js' 
            : 'https://app.midtrans.com/snap/snap.js';
    @endphp
    <script src="{{ $snapUrl }}" data-client-key="{{ $clientKey }}"></script>
@endif

<script>

let selectedMethod = 'qris';

function selectMethod(element, method) {
    document.querySelectorAll('.grid-cols-2 > div, .grid-cols-4 > div').forEach(el => {
        el.classList.remove('border-blue-500', 'bg-blue-50');
        el.classList.add('border-gray-200');
    });
    element.classList.remove('border-gray-200');
    element.classList.add('border-blue-500', 'bg-blue-50');
    selectedMethod = method;
    document.getElementById('payment_method').value = method;
}

function setNominal(value) {
    document.getElementById('nominal').value = value;
}

document.getElementById('topupForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    const submitBtn = this.querySelector('button[type="submit"]');
    
    btnText.classList.add('hidden');
    btnLoading.classList.remove('hidden');
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    
    fetch('{{ route("customer.topup.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // ===== REDIRECT KE MIDTRANS =====
            if (data.data && data.data.payment_url) {
                // Buka Snap Midtrans
                if (window.snap && data.data.payment_token) {
                    window.snap.pay(data.data.payment_token, {
                        onSuccess: function(result) {
                            console.log('Payment success:', result);
                            window.location.href = '{{ route("customer.topup.callback") }}?order_id=' + result.order_id;
                        },
                        onPending: function(result) {
                            console.log('Payment pending:', result);
                            showNotification('⏳ Pembayaran pending, silakan selesaikan.', 'warning');
                            btnText.classList.remove('hidden');
                            btnLoading.classList.add('hidden');
                            submitBtn.disabled = false;
                        },
                        onError: function(result) {
                            console.log('Payment error:', result);
                            showNotification('❌ Pembayaran gagal!', 'error');
                            btnText.classList.remove('hidden');
                            btnLoading.classList.add('hidden');
                            submitBtn.disabled = false;
                            window.location.href = '{{ route("customer.topup.failed") }}';
                        },
                        onClose: function() {
                            console.log('Payment closed');
                            showNotification('⚠️ Pembayaran ditutup!', 'warning');
                            btnText.classList.remove('hidden');
                            btnLoading.classList.add('hidden');
                            submitBtn.disabled = false;
                        }
                    });
                } else {
                    // Redirect langsung ke URL
                    window.location.href = data.data.payment_url;
                }
            } else {
                showNotification('✅ ' + data.message, 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("customer.topup.index") }}';
                }, 2000);
            }
        } else {
            let errorMsg = data.message || 'Gagal topup!';
            if (data.errors) {
                errorMsg = Object.values(data.errors).flat().join('\n');
            }
            showNotification('❌ ' + errorMsg, 'error');
            btnText.classList.remove('hidden');
            btnLoading.classList.add('hidden');
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        showNotification('❌ ' + error.message, 'error');
        btnText.classList.remove('hidden');
        btnLoading.classList.add('hidden');
        submitBtn.disabled = false;
    });
});

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