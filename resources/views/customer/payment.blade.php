{{-- resources/views/customer/payment.blade.php --}}

@extends('app')
@include('layout.customer')
@section('content')
@yield('nav')
<br>
<br>
<br>
<br>
@php
    $midtrans = $paymentGateways->where('gateway', 'midtrans')->where('is_active', true)->first();
@endphp

@if($midtrans)
    @php
        $clientKey = $midtrans->api_key;
        $isSandbox = $midtrans->is_sandbox;
        $snapUrl = $isSandbox 
            ? 'https://app.sandbox.midtrans.com/snap/snap.js' 
            : 'https://app.midtrans.com/snap/snap.js';
    @endphp
    
    <script src="{{ $snapUrl }}" data-client-key="{{ $clientKey }}"></script>
@endif

<div class="container mx-auto px-4 py-6">
    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
        <span class="text-sm text-gray-600">Status Snap:</span>
        <span id="snap-status" class="text-sm text-yellow-600">⏳ Memuat...</span>
    </div>
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-blue-100 rounded-xl">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Pembayaran</h1>
                    <p class="text-sm text-gray-500">Pilih metode pembayaran yang tersedia</p>
                </div>
            </div>

            {{-- Ringkasan Pesanan --}}
            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                <h3 class="font-semibold text-gray-700 mb-3">Ringkasan Pesanan</h3>
                @foreach($carts as $cart)
                <div class="flex justify-between text-sm py-1">
                    <span>{{ $cart->menu->nama_menu }} ({{ $cart->paket }}) x{{ $cart->quantity }}</span>
                    <span class="font-medium">Rp {{ number_format($cart->subtotal, 0, ',', '.') }}</span>
                </div>
                @endforeach
                <div class="border-t border-gray-200 mt-2 pt-2 flex justify-between font-bold text-lg">
                    <span>Total</span>
                    <span class="text-blue-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Pilih Payment Gateway --}}
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-3">Pilih Metode Pembayaran</label>
                
                @foreach($paymentGateways as $gateway)
                    @php
                        $methods = $gatewayMethods[$gateway->id]['methods'] ?? [];
                    @endphp
                    
                    @if(count($methods) > 0)
                        <div class="mb-4">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="font-medium text-gray-700">{{ $gateway->name }}</span>
                                <span class="text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full">
                                    {{ count($methods) }} metode
                                </span>
                            </div>
                            {{-- PERBAIKAN: Tambahkan id paymentMethods --}}
                            <div id="paymentMethods" class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                @foreach($methods as $method)
                                    <div class="border-2 rounded-xl p-3 cursor-pointer transition-all hover:border-blue-500 
                                        {{ $loop->parent->first && $loop->first ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}"
                                        data-gateway="{{ $gateway->id }}"
                                        data-method="{{ $method['id'] }}"
                                        onclick="selectPayment(this, {{ $gateway->id }}, '{{ $method['id'] }}')">
                                        
                                        <div class="flex items-center gap-2">
                                            <span class="text-xl">{{ $method['icon'] }}</span>
                                            <div>
                                                <p class="font-medium text-sm text-gray-800">{{ $method['name'] }}</p>
                                                <p class="text-xs text-gray-500">{{ $method['description'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Tombol Bayar --}}
            <button onclick="processPayment()" 
                class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-all hover:scale-[1.02]">
                <span id="payButtonText">Bayar Sekarang</span>
                <span id="payButtonLoading" class="hidden">
                    <svg class="inline w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Memproses...
                </span>
            </button>

            {{-- QRIS Display --}}
            <div id="qris-container" class="mt-6 hidden">
                <div class="p-4 bg-white rounded-xl border border-gray-200">
                    <h3 class="font-semibold text-gray-700 mb-3">📱 Scan QRIS untuk Pembayaran</h3>
                    <div class="flex flex-col items-center">
                        <div id="qris-image-container"></div>
                        <p class="text-sm text-gray-600 mt-3">Scan QRIS di atas menggunakan aplikasi pembayaran</p>
                        <p class="text-xs text-gray-400 mt-1">QRIS berlaku 15 menit</p>
                        <button onclick="checkQrisStatus()" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            Cek Status Pembayaran
                        </button>
                    </div>
                </div>
            </div>

            <p class="text-xs text-gray-400 text-center mt-4">
                Dengan melanjutkan, Anda menyetujui Syarat & Ketentuan yang berlaku
            </p>
        </div>
    </div>
</div>

<script>
    // ============================================
    // CEK SNAP MIDTRANS
    // ============================================
    (function() {
        let attempts = 0;
        const maxAttempts = 20;
        
        const checkSnap = setInterval(function() {
            attempts++;
            console.log('Checking Snap... attempt ' + attempts);
            
            if (typeof window.snap !== 'undefined') {
                console.log('✅ Snap Midtrans siap digunakan!');
                const statusEl = document.getElementById('snap-status');
                if (statusEl) {
                    statusEl.textContent = '✅ Snap siap digunakan';
                    statusEl.className = 'text-green-600 text-sm';
                }
                window.snapReady = true;
                clearInterval(checkSnap);
            } else if (attempts >= maxAttempts) {
                console.error('❌ Snap Midtrans gagal di-load!');
                const statusEl = document.getElementById('snap-status');
                if (statusEl) {
                    statusEl.textContent = '❌ Snap gagal dimuat, gunakan redirect';
                    statusEl.className = 'text-red-600 text-sm';
                }
                window.snapReady = false;
                clearInterval(checkSnap);
            }
        }, 500);
    })();

    // ============================================
    // VARIABEL GLOBAL
    // ============================================
    let selectedPayment = {{ $paymentGateways->first()->id ?? 0 }};
    let selectedMethod = '';

    // ============================================
    // SELECT PAYMENT METHOD
    // ============================================
    function selectPayment(element, gatewayId, methodId) {
        console.log('Selecting method:', gatewayId, methodId);
        
        // PERBAIKAN: Gunakan selector yang benar
        document.querySelectorAll('#paymentMethods > div').forEach(el => {
            el.classList.remove('border-blue-500', 'bg-blue-50');
            el.classList.add('border-gray-200');
        });
        
        element.classList.remove('border-gray-200');
        element.classList.add('border-blue-500', 'bg-blue-50');
        
        selectedPayment = gatewayId;
        selectedMethod = methodId;
        
        // Hide QRIS container jika bukan QRIS
        if (methodId !== 'qris') {
            document.getElementById('qris-container').classList.add('hidden');
        }
    }

    // ============================================
    // PROCESS PAYMENT
    // ============================================
    function processPayment() {
        console.log('Processing payment with ID:', selectedPayment);
        console.log('Selected method:', selectedMethod);
        
        if (!selectedPayment || selectedPayment === 0) {
            showNotification('⚠️ Silakan pilih metode pembayaran terlebih dahulu!', 'warning');
            return;
        }
        
        if (!selectedMethod || selectedMethod === '') {
            showNotification('⚠️ Silakan pilih jenis pembayaran (QRIS, Bank Transfer, dll)!', 'warning');
            return;
        }

        const btn = document.getElementById('payButtonText');
        const loading = document.getElementById('payButtonLoading');
        if (btn) btn.classList.add('hidden');
        if (loading) loading.classList.remove('hidden');

        const url = "{{ route('customer.payment.process') }}";
        
        const requestData = {
            payment_gateway: selectedPayment,
            payment_method: selectedMethod,
        };
        console.log('Request data:', requestData);
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            console.log('Response:', data);
            
            if (data.success) {
                // ===== KHUSUS CASH =====
                if (data.payment_type === 'cash') {
                    if (data.redirect_url) {
                        // Redirect ke halaman QR Code
                        window.location.href = data.redirect_url;
                    } else {
                        showNotification('✅ ' + data.message, 'success');
                        setTimeout(() => {
                            window.location.href = '{{ route("customer.orders") }}';
                        }, 1500);
                    }
                    return;
                }
                
                // ===== KHUSUS QRIS =====
                if (data.payment_type === 'qris' && data.qr_code) {
                    const container = document.getElementById('qris-container');
                    container.classList.remove('hidden');
                    
                    const imageContainer = document.getElementById('qris-image-container');
                    if (data.qr_image) {
                        imageContainer.innerHTML = `<img src="${data.qr_image}" alt="QRIS" class="w-48 h-48">`;
                    } else {
                        imageContainer.innerHTML = `
                            <div class="w-48 h-48 bg-gray-100 flex items-center justify-center">
                                <span class="text-sm text-gray-500">Scan QRIS: ${data.qr_code.substring(0, 20)}...</span>
                            </div>
                        `;
                    }
                    
                    showNotification('✅ Scan QRIS untuk membayar', 'success');
                    if (btn) btn.classList.remove('hidden');
                    if (loading) loading.classList.add('hidden');
                    
                    startQrisCheck(data.order_id);
                    return;
                }
                
                // ===== MIDTRANS SNAP =====
                if (data.payment_gateway === 'midtrans' && data.token) {
                    if (typeof window.snap !== 'undefined') {
                        console.log('Using Snap with token:', data.token);
                        
                        window.snap.pay(data.token, {
                            onSuccess: function(result) {
                                console.log('Payment success:', result);
                                showNotification('✅ Pembayaran berhasil!', 'success');
                                setTimeout(() => {
                                    window.location.href = data.finish_url || '{{ route("customer.payment.success") }}?order_id=' + data.order_id;
                                }, 1000);
                            },
                            onPending: function(result) {
                                console.log('Payment pending:', result);
                                showNotification('⏳ Pembayaran pending, silakan selesaikan.', 'warning');
                                if (btn) btn.classList.remove('hidden');
                                if (loading) loading.classList.add('hidden');
                                startPaymentCheck(data.order_id);
                            },
                            onError: function(result) {
                                console.log('Payment error:', result);
                                showNotification('❌ Pembayaran gagal!', 'error');
                                if (btn) btn.classList.remove('hidden');
                                if (loading) loading.classList.add('hidden');
                                setTimeout(() => {
                                    window.location.href = data.error_url || '{{ route("customer.payment.failed") }}';
                                }, 1500);
                            },
                            onClose: function() {
                                console.log('Payment closed');
                                showNotification('⚠️ Pembayaran ditutup!', 'warning');
                                if (btn) btn.classList.remove('hidden');
                                if (loading) loading.classList.add('hidden');
                            }
                        });
                    } else {
                        console.warn('Snap not available, using redirect fallback');
                        showNotification('⚠️ Menggunakan redirect pembayaran...', 'warning');
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            window.location.href = 'https://app.sandbox.midtrans.com/snap/v4/redirection/' + data.token;
                        }
                    }
                } 
                else if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                } 
                else {
                    showNotification('✅ ' + data.message, 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route("customer.orders") }}';
                    }, 1500);
                }
            } else {
                let errorMsg = data.message || 'Gagal memproses pembayaran!';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('\n');
                }
                showNotification('❌ ' + errorMsg, 'error');
                if (btn) btn.classList.remove('hidden');
                if (loading) loading.classList.add('hidden');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showNotification('❌ ' + error.message, 'error');
            if (btn) btn.classList.remove('hidden');
            if (loading) loading.classList.add('hidden');
        });
    }

    // ============================================
    // CEK STATUS QRIS
    // ============================================
    function startQrisCheck(orderId) {
        let checkCount = 0;
        const maxChecks = 30;
        
        const checkInterval = setInterval(function() {
            checkCount++;
            console.log('Checking QRIS status...', checkCount);
            
            fetch('/payment/status/' + orderId, {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'paid') {
                    clearInterval(checkInterval);
                    showNotification('✅ Pembayaran berhasil!', 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route("customer.orders") }}';
                    }, 1500);
                } else if (checkCount >= maxChecks) {
                    clearInterval(checkInterval);
                    showNotification('⏳ Waktu cek habis, cek status manual di Pesanan Saya', 'warning');
                }
            })
            .catch(error => {
                console.error('Error checking status:', error);
            });
        }, 5000);
    }

    function startPaymentCheck(orderId) {
        let checkCount = 0;
        const maxChecks = 60;
        
        const checkInterval = setInterval(function() {
            checkCount++;
            console.log('Checking payment status...', checkCount);
            
            fetch('/payment/status/' + orderId, {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'paid') {
                    clearInterval(checkInterval);
                    showNotification('✅ Pembayaran berhasil!', 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route("customer.orders") }}';
                    }, 1500);
                } else if (checkCount >= maxChecks) {
                    clearInterval(checkInterval);
                    showNotification('⏳ Waktu cek habis, cek status manual di Pesanan Saya', 'warning');
                }
            })
            .catch(error => {
                console.error('Error checking status:', error);
            });
        }, 5000);
    }

    function checkQrisStatus() {
        showNotification('⏳ Mengecek status pembayaran...', 'info');
        setTimeout(() => {
            window.location.reload();
        }, 2000);
    }

    // ============================================
    // SHOW NOTIFICATION
    // ============================================
    function showNotification(message, type = 'success') {
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
        notification.className = `fixed top-4 right-4 z-50 transform transition-all duration-500 translate-x-0 opacity-100 ${colors[type] || colors.success} text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-3 max-w-md`;
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

    // ============================================
    // DOM READY
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded - Payment page');
        
        const firstPayment = document.querySelector('#paymentMethods > div');
        if (firstPayment) {
            const gatewayId = parseInt(firstPayment.dataset.gateway);
            const methodId = firstPayment.dataset.method;
            
            if (gatewayId && gatewayId > 0 && methodId) {
                selectedPayment = gatewayId;
                selectedMethod = methodId;
                firstPayment.classList.add('border-blue-500', 'bg-blue-50');
                console.log('Default payment set to:', selectedPayment, selectedMethod);
            }
        }
    });
</script>

@endsection