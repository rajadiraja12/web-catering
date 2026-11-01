{{-- resources/views/customer/cart.blade.php --}}
@extends('app')
@include('layout.customer')
@section('content')
@yield('nav')
<br>
<br>
<br>
<br>
<br>

{{-- Info Peringatan --}}
@if($carts->isNotEmpty())
    @php
        $tanggalMenu = $carts->first()->menu->tanggal;
        $existingOrder = \App\Models\Order::where('id_customer', Auth::id())
            ->whereDate('tanggal_menu', $tanggalMenu)
            ->where('payment_status', '!=', 'cancelled')
            ->exists();
    @endphp
    
    @if($existingOrder)
    <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg flex items-center gap-3">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <strong>Perhatian!</strong> Anda sudah memiliki pesanan untuk tanggal <strong>{{ date('d-m-Y', strtotime($tanggalMenu)) }}</strong>. 
            Hanya boleh 1 pesanan per hari!
        </div>
    </div>
    @endif
@endif

{{-- Header --}}
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">🛒 Keranjang Belanja</h1>
        <p class="text-sm text-gray-500">Daftar menu yang akan Anda pesan</p>
    </div>
    <a href="{{ route('customer.dashboard') }}" 
        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-sm rounded-lg transition-all duration-200">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Lanjut Belanja
    </a>
</div>

{{-- Tabel Keranjang --}}
<div class="relative overflow-x-auto bg-white shadow-sm rounded-2xl border border-gray-200">
    <table class="w-full text-sm text-left rtl:text-right text-gray-700">
        <thead class="text-xs text-gray-800 uppercase bg-blue-200 from-blue-600 to-blue-800 border-b border-blue-900">
            <tr>
                <th scope="col" class="px-6 py-4 text-center w-16">No</th>
                <th scope="col" class="px-6 py-4">Menu</th>
                <th scope="col" class="px-6 py-4 text-center">Paket</th>
                <th scope="col" class="px-6 py-4 text-center">Tanggal</th>
                <th scope="col" class="px-6 py-4 text-center">Jumlah</th>
                <th scope="col" class="px-6 py-4 text-right">Harga</th>
                <th scope="col" class="px-6 py-4 text-right">Subtotal</th>
                <th scope="col" class="px-6 py-4 text-center w-28">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($carts as $index => $cart)
            <tr class="{{ $loop->iteration % 2 == 0 ? 'bg-gray-50' : 'bg-white' }} border-b border-gray-200 hover:bg-blue-50 transition-colors duration-200">
                <td class="px-6 py-4 text-center font-medium text-gray-900">
                    {{ $loop->iteration }}
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        @if($cart->menu->gambar)
                            <img src="{{ asset($cart->menu->gambar) }}" alt="{{ $cart->menu->nama_menu }}" 
                                class="w-10 h-10 rounded-lg object-cover border border-gray-200">
                        @else
                            <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                        @endif
                        <div>
                            <p class="font-medium text-gray-800">{{ $cart->menu->nama_menu }}</p>
                            <p class="text-xs text-gray-500">{{ Str::limit($cart->menu->deskripsi, 30) }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-center">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                        <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                        {{ ucfirst($cart->paket ?? 'Standar') }}
                    </span>
                </td>
                <td class="px-6 py-4 text-center">
                    {{ $cart->menu->tanggal ? date('d-m-Y', strtotime($cart->menu->tanggal)) : '-' }}
                </td>
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <button onclick="updateQuantity({{ $cart->id }}, 'minus')" 
                            class="w-7 h-7 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-lg transition-all duration-200 text-gray-600 hover:text-gray-800">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                            </svg>
                        </button>
                        <span class="text-sm font-semibold text-gray-800 w-6 text-center" id="qty-{{ $cart->id }}">
                            {{ $cart->quantity }}
                        </span>
                        <button onclick="updateQuantity({{ $cart->id }}, 'plus')" 
                            class="w-7 h-7 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-lg transition-all duration-200 text-gray-600 hover:text-gray-800">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                    </div>
                </td>
                <td class="px-6 py-4 text-right font-medium text-gray-700">
                    Rp {{ number_format($cart->price, 0, ',', '.') }}
                </td>
                <td class="px-6 py-4 text-right font-semibold text-blue-600" id="subtotal-{{ $cart->id }}">
                    Rp {{ number_format($cart->subtotal, 0, ',', '.') }}
                </td>
                <td class="px-6 py-4 text-center">
                    <button onclick="removeFromCart({{ $cart->id }})" 
                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-100 hover:bg-red-600 text-red-700 hover:text-white font-medium text-xs rounded-lg transition-all duration-200 border border-red-200 hover:border-red-700 focus:ring-2 focus:ring-red-300 focus:outline-none">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Hapus
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="w-20 h-20 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="text-lg font-medium text-gray-600">Keranjang Kosong</p>
                        <p class="text-sm text-gray-400 mt-1">Silakan pilih menu terlebih dahulu</p>
                        <a href="{{ route('customer.dashboard') }}" 
                            class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-lg transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Pesan Sekarang
                        </a>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer dengan Total dan Tombol Bayar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 bg-gray-50 border-t border-gray-200 rounded-b-2xl">
        <div class="flex items-center gap-4 mb-4 sm:mb-0">
            <span class="text-sm font-normal text-gray-700">
                Total Item: <span class="font-semibold text-gray-900">{{ $carts->count() }}</span>
            </span>
            <span class="text-sm font-normal text-gray-700">
                Total Pesanan: 
                <span class="text-xl font-bold text-blue-600" id="total-display">
                    Rp {{ number_format($total, 0, ',', '.') }}
                </span>
            </span>
        </div>
        
        @if($carts->count() > 0)
            @php
                $tanggalMenu = $carts->first()->menu->tanggal;
                $existingOrder = \App\Models\Order::where('id_customer', Auth::id())
                    ->whereDate('tanggal_menu', $tanggalMenu)
                    ->where('payment_status', '!=', 'cancelled')
                    ->exists();
            @endphp
            
            @if($existingOrder)
                <button disabled 
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gray-400 cursor-not-allowed text-white font-medium text-sm rounded-xl">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Sudah Pesan Hari Ini
                </button>
            @else
                <button onclick="checkout()" 
                    class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium text-sm rounded-xl transition-all duration-200 shadow-sm hover:shadow-md hover:scale-105 focus:ring-4 focus:ring-green-300 focus:outline-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Bayar Sekarang
                </button>
            @endif
        @endif
    </div>
</div>

{{-- ============================================ --}}
{{-- JAVASCRIPT --}}
{{-- ============================================ --}}
<script>
// ============================================
// UPDATE QUANTITY
// ============================================
function updateQuantity(cartId, action) {
    const qtyElement = document.getElementById('qty-' + cartId);
    let currentQty = parseInt(qtyElement.textContent);
    
    if (action === 'plus') {
        currentQty += 1;
    } else if (action === 'minus') {
        if (currentQty <= 1) return;
        currentQty -= 1;
    }
    
    const url = "{{ route('customer.cart.update', ['id' => ':id']) }}".replace(':id', cartId);
    
    fetch(url, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ quantity: currentQty })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            qtyElement.textContent = currentQty;
            
            const subtotalElement = document.getElementById('subtotal-' + cartId);
            const price = parseInt(data.data.price);
            const newSubtotal = price * currentQty;
            subtotalElement.textContent = 'Rp ' + formatNumber(newSubtotal);
            
            updateTotal();
            showNotification('✅ ' + data.message, 'success');
        } else {
            showNotification('❌ Gagal mengupdate!', 'error');
        }
    })
    .catch(error => {
        showNotification('❌ Terjadi kesalahan!', 'error');
        console.error('Error:', error);
    });
}

// ============================================
// UPDATE TOTAL
// ============================================
function updateTotal() {
    const subtotals = document.querySelectorAll('[id^="subtotal-"]');
    let total = 0;
    subtotals.forEach(el => {
        const text = el.textContent.replace('Rp ', '').replace(/\./g, '');
        total += parseInt(text);
    });
    document.getElementById('total-display').textContent = 'Rp ' + formatNumber(total);
}

// ============================================
// REMOVE FROM CART
// ============================================
function removeFromCart(cartId) {
    if (!confirm('Yakin ingin menghapus item ini dari keranjang?')) {
        return;
    }
    
    const url = "{{ route('customer.cart.remove', ['id' => ':id']) }}".replace(':id', cartId);
    
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
        console.error('Error:', error);
    });
}

// ============================================
// CHECKOUT / BAYAR
// ============================================
function checkout() {
    if (!confirm('Yakin ingin melakukan pembayaran?')) {
        return;
    }
    
    const url = "{{ route('customer.checkout') }}";
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✅ ' + data.message, 'success');
            setTimeout(() => {
                window.location.href = "{{ route('customer.orders') }}";
            }, 1500);
        } else {
            showNotification('❌ ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('❌ Terjadi kesalahan!', 'error');
        console.error('Error:', error);
    });
}

// ============================================
// FORMAT NUMBER
// ============================================
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// ============================================
// NOTIFICATION
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
    
    setTimeout(() => {
        if (notification.parentNode) notification.remove();
    }, 3000);
}
</script>

<style>
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

tbody tr {
    animation: fadeIn 0.3s ease-out;
}
</style>

@endsection