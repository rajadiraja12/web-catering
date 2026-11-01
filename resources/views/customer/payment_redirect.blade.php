@extends('app')
@include('layout.customer')
@section('content')
@yield('nav')
<br>
<br>
<br>
<br>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-md mx-auto bg-white rounded-2xl shadow-lg p-8 text-center">
        <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-blue-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Mengarahkan ke Pembayaran...</h1>
        <p class="text-gray-600 mb-6">Silakan tunggu, Anda akan dialihkan ke halaman pembayaran.</p>
        <p class="text-sm text-gray-400">Order ID: <span class="font-mono">{{ $order_id ?? '-' }}</span></p>
        
        <div class="mt-6">
            <a href="{{ route('customer.pending-payments') }}" 
                class="text-sm text-blue-600 hover:text-blue-800">
                ← Kembali ke Pembayaran Tertunda
            </a>
        </div>
    </div>
</div>

@if(isset($token) && isset($client_key))
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $client_key }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tunggu sebentar agar Snap.js siap
            setTimeout(function() {
                if (typeof window.snap !== 'undefined') {
                    window.snap.pay('{{ $token }}', {
                        onSuccess: function(result) {
                            window.location.href = '{{ route("customer.payment.success") }}?order_id={{ $order_id }}';
                        },
                        onPending: function(result) {
                            window.location.href = '{{ route("customer.payment.failed") }}';
                        },
                        onError: function(result) {
                            window.location.href = '{{ route("customer.payment.failed") }}';
                        },
                        onClose: function() {
                            window.location.href = '{{ route("customer.pending-payments") }}';
                        }
                    });
                } else {
                    // Fallback: redirect ke URL Midtrans
                    @if(isset($redirect_url))
                        window.location.href = '{{ $redirect_url }}';
                    @else
                        alert('Snap Midtrans tidak tersedia. Silakan coba lagi.');
                        window.location.href = '{{ route("customer.pending-payments") }}';
                    @endif
                }
            }, 1000);
        });
    </script>
@endif

@endsection