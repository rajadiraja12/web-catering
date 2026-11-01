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
            <h1 class="text-2xl font-bold text-gray-800">Pembayaran Tertunda</h1>
            <p class="text-sm text-gray-500">Daftar pesanan yang belum Anda selesaikan pembayarannya</p>
        </div>
        <a href="{{ route('customer.orders') }}" 
            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Lihat Pesanan Saya
        </a>
    </div>

    {{-- Info --}}
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm text-blue-700">
                    <strong>Perhatian:</strong> Pembayaran yang tertunda akan otomatis kedaluwarsa dalam <strong>24 jam</strong>.
                    Silakan selesaikan pembayaran sebelum kedaluwarsa.
                </p>
            </div>
        </div>
    </div>

    {{-- Pending Payments Aktif --}}
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Perlu Diselesaikan</h2>
    
    @if($pendingPayments->count() > 0)
        <div class="grid grid-cols-1 gap-4 mb-8">
            @foreach($pendingPayments as $pending)
            <div class="bg-white rounded-2xl shadow-sm border border-yellow-200 hover:shadow-md transition-all duration-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        {{-- Left --}}
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-sm font-mono font-bold text-gray-800 bg-gray-100 px-3 py-1 rounded-lg">
                                    {{ $pending->order_id }}
                                </span>
                                <span class="text-xs px-3 py-1 rounded-full bg-yellow-100 text-yellow-700">
                                    {{ ucfirst($pending->status) }}
                                </span>
                                @if($pending->expires_at)
                                <span class="text-xs text-gray-500">
                                    {{ $pending->expires_at->diffForHumans() }}
                                </span>
                                @endif
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="text-gray-500">Metode:</span>
                                    <span class="font-medium text-gray-700">{{ ucfirst($pending->payment_method) }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Gateway:</span>
                                    <span class="font-medium text-gray-700">{{ $pending->payment_gateway ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Total Item:</span>
                                    <span class="font-medium text-gray-700">{{ count($pending->cart_data) }} item</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Dibuat:</span>
                                    <span class="font-medium text-gray-700">{{ $pending->created_at->format('d-m-Y H:i') }}</span>
                                </div>
                            </div>

                            {{-- Detail Item --}}
                            <div class="mt-3">
                                <button onclick="toggleDetail(this)" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                    Lihat Detail Item ▼
                                </button>
                                <div class="hidden mt-2 space-y-1 text-sm border-t border-gray-100 pt-2">
                                    @foreach($pending->cart_data as $item)
                                    <div class="flex justify-between py-1">
                                        <span class="text-gray-600">
                                            {{ $item['menu']['nama_menu'] ?? 'Menu' }} 
                                            ({{ $item['paket'] ?? 'Standar' }}) 
                                            x{{ $item['quantity'] ?? 1 }}
                                        </span>
                                        <span class="font-medium">
                                            Rp {{ number_format($item['subtotal'] ?? 0, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    @endforeach
                                    <div class="border-t border-gray-200 pt-2 flex justify-between font-bold">
                                        <span>Total</span>
                                        <span class="text-blue-600">Rp {{ number_format($pending->total, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right - Actions --}}
                        <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                            <a href="{{ route('customer.pending-payment.continue', $pending->id) }}" 
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all hover:scale-105">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                                Lanjutkan Bayar
                            </a>
                            <button onclick="cancelPending({{ $pending->id }})" 
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-red-100 hover:bg-red-600 text-red-700 hover:text-white text-sm font-medium rounded-lg transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Batalkan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 text-center mb-8">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-gray-500">Tidak ada pembayaran tertunda.</p>
            <p class="text-sm text-gray-400 mt-1">Semua pesanan Anda sudah selesai dibayar.</p>
        </div>
    @endif

    {{-- Expired Payments --}}
    @if($expiredPayments->count() > 0)
    <div class="mt-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Kadaluwarsa</h2>
        <div class="grid grid-cols-1 gap-3">
            @foreach($expiredPayments as $expired)
            <div class="bg-gray-50 rounded-2xl border border-red-200 p-4 opacity-75">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-mono font-bold text-gray-600">
                                {{ $expired->order_id }}
                            </span>
                            <span class="text-xs px-3 py-1 rounded-full bg-red-100 text-red-700">
                                Kadaluwarsa
                            </span>
                            @if($expired->expires_at)
                            <span class="text-xs text-gray-400">
                                {{ $expired->expires_at->diffForHumans() }}
                            </span>
                            @endif
                        </div>
                        <div class="text-sm text-gray-500 mt-1">
                            Total: Rp {{ number_format($expired->total, 0, ',', '.') }}
                        </div>
                    </div>
                    <button onclick="deleteExpired({{ $expired->id }})" 
                        class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-medium rounded-lg transition-all">
                        Hapus
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<script>
function toggleDetail(btn) {
    const parent = btn.closest('div');
    const detail = parent.querySelector('.hidden.mt-2');
    if (detail) {
        detail.classList.toggle('hidden');
        btn.textContent = detail.classList.contains('hidden') ? 'Lihat Detail Item ▼' : 'Sembunyikan Detail ▲';
    }
}

function cancelPending(id) {
    if (!confirm('Yakin ingin membatalkan pesanan ini?')) return;
    
    const url = "{{ route('customer.pending-payment.cancel', ['id' => ':id']) }}".replace(':id', id);
    
    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✅ ' + data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('❌ ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('❌ Terjadi kesalahan!', 'error');
    });
}

function deleteExpired(id) {
    if (!confirm('Hapus riwayat pembayaran yang kadaluwarsa ini?')) return;
    
    const url = "{{ route('customer.pending-payment.cancel', ['id' => ':id']) }}".replace(':id', id);
    
    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✅ ' + data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        showNotification('❌ Terjadi kesalahan!', 'error');
    });
}

function showNotification(message, type = 'success') {
    const old = document.getElementById('custom-notification');
    if (old) old.remove();
    
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500',
        warning: 'bg-yellow-500'
    };
    
    const notification = document.createElement('div');
    notification.id = 'custom-notification';
    notification.className = `fixed top-4 right-4 z-50 transform transition-all duration-500 translate-x-0 opacity-100 ${colors[type] || colors.success} text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-3`;
    notification.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" class="text-white hover:text-gray-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;
    
    document.body.appendChild(notification);
    setTimeout(() => { if (notification.parentNode) notification.remove(); }, 3000);
}
</script>

<style>
/* Animasi */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.bg-white.rounded-2xl {
    animation: fadeIn 0.3s ease-out;
}
</style>

@endsection