{{-- resources/views/customer/dashboard.blade.php --}}

@extends('app')
@include('layout.customer')
@section('content')
@yield('nav')
<br>
<br>
<br>
<br>

{{-- Container Utama --}}
<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Menu Minggu Ini</h1>
            <p class="text-sm text-gray-500">
                @if($isKelas1 ?? false)
                    Menu khusus untuk Kelas 1
                @else
                    Menu untuk semua kelas
                @endif
            </p>
        </div>
        {{-- Tampilkan tipe menu yang diakses --}}
        @if($isKelas1 ?? false)
            <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">
                Khusus Kelas 1
            </span>
        @else
            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                Menu Umum
            </span>
        @endif
    </div>

    {{-- Filter Tanggal --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2 overflow-x-auto pb-2">
            <span class="text-sm text-gray-600 font-medium">Tanggal:</span>
            @if($menusByDate && $menusByDate->count() > 0)
                @foreach($menusByDate as $date => $menuGroup)
                    <button onclick="filterMenu('{{ $date }}')" 
                        class="date-filter-btn text-sm font-medium px-3 py-1 rounded-lg transition-all duration-200 
                            {{ $date == $today ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-blue-100 hover:text-blue-700' }}"
                        data-date="{{ $date }}">
                        {{ \Carbon\Carbon::parse($date)->translatedFormat('d/m/Y') }}
                    </button>
                @endforeach
            @else
                <span class="text-sm text-gray-400">Belum ada menu</span>
            @endif
        </div>
    </div>

    {{-- Loop Menu Berdasarkan Tanggal --}}
    @if($menusByDate && $menusByDate->count() > 0)
        @foreach($menusByDate as $date => $menuGroup)
            <div class="menu-group" id="menu-group-{{ str_replace('-', '', $date) }}" 
                style="{{ $date == $today ? '' : 'display: none;' }}">
                
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">
                            {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                        </h2>
                        <p class="text-sm text-gray-500">{{ $menuGroup->count() }} menu tersedia</p>
                    </div>
                </div>

                {{-- Scroll Horizontal Menu --}}
                <div class="relative mb-8">
                    {{-- Tombol Scroll Kiri --}}
                    <button onclick="scrollLeft(this)" 
                        class="absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-white/90 hover:bg-white shadow-lg rounded-full p-2.5 border border-gray-200 transition-all duration-200 hover:scale-110 ml-2 opacity-0 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    {{-- Tombol Scroll Kanan --}}
                    <button onclick="scrollRight(this)" 
                        class="absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-white/90 hover:bg-white shadow-lg rounded-full p-2.5 border border-gray-200 transition-all duration-200 hover:scale-110 mr-2 opacity-100">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    {{-- Container Scroll --}}
                    <div class="menu-scroll flex gap-6 overflow-x-auto pb-4 scroll-smooth hide-scrollbar"
                        style="scroll-behavior: smooth; -webkit-overflow-scrolling: touch;">
                        
                        @forelse($menuGroup as $menu)
                        <div class="min-w-[320px] max-w-[320px] bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden flex-shrink-0 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                            {{-- Gambar Menu --}}
                            <div class="relative h-48 bg-gray-200 flex items-center justify-center">
                                @if($menu->gambar)
                                    <img src="{{ asset($menu->gambar) }}" alt="{{ $menu->nama_menu }}" 
                                        class="w-full h-full object-cover">
                                @else
                                    <div class="w-32 h-32 bg-gray-300 rounded-2xl flex items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </div>
                                @endif
                                
                                {{-- Badge tipe menu --}}
                                @if($menu->tipe_menu == 'kelas_1')
                                    <span class="absolute top-2 right-2 px-2 py-1 bg-purple-600 text-white text-xs font-medium rounded-lg">
                                        Kelas 1
                                    </span>
                                @else
                                    <span class="absolute top-2 right-2 px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded-lg">
                                        Umum
                                    </span>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div class="p-5">
                                <h3 class="text-lg font-bold text-gray-800 mb-2">{{ $menu->nama_menu }}</h3>
                                
                                {{-- Pilihan Paket --}}
                                <div class="flex flex-wrap gap-2 mb-3">
                                    @php
                                        $kelasCustomer = explode('-', $user->kelas)[0] ?? '0';
                                        $isKelas1 = ($kelasCustomer == '1');
                                        
                                        // ===== TENTUKAN HARGA =====
                                        if ($isKelas1 && $menu->tipe_menu == 'kelas_1') {
                                            // Kelas 1 dengan menu kelas_1 -> pakai harga_kelas_1
                                            $hargaKecil = $menu->harga_kelas_1 ?? $menu->harga_paket_kecil;
                                            $hargaSedang = $menu->harga_kelas_1 ?? $menu->harga_paket_sedang;
                                            $hargaBesar = $menu->harga_kelas_1 ?? $menu->harga_paket_besar;
                                            $hargaKhusus = $menu->harga_kelas_1 ?? $menu->harga_paket_khusus;
                                        } else {
                                            // Kelas lain atau menu umum -> pakai harga normal
                                            $hargaKecil = $menu->harga_paket_kecil;
                                            $hargaSedang = $menu->harga_paket_sedang;
                                            $hargaBesar = $menu->harga_paket_besar;
                                            $hargaKhusus = $menu->harga_paket_khusus;
                                        }
                                    @endphp

                                    @if ($hargaKecil > 0)
                                        <button onclick="selectPackage(this, 'best', {{ $menu->id }})" 
                                            class="package-btn px-3 py-1.5 text-xs font-medium rounded-lg border-2 border-gray-200 text-gray-600 hover:border-blue-500 hover:text-blue-600 transition-all duration-200"
                                            data-price="{{ $hargaKecil }}">
                                            BEST<br>
                                            <span class="text-[10px]">Rp {{ number_format($hargaKecil, 0, ',', '.') }}</span>
                                        </button>
                                    @endif

                                    @if ($hargaSedang > 0)
                                        <button onclick="selectPackage(this, 'happy', {{ $menu->id }})" 
                                            class="package-btn px-3 py-1.5 text-xs font-medium rounded-lg border-2 border-gray-200 text-gray-600 hover:border-blue-500 hover:text-blue-600 transition-all duration-200"
                                            data-price="{{ $hargaSedang }}">
                                            HAPPY<br>
                                            <span class="text-[10px]">Rp {{ number_format($hargaSedang, 0, ',', '.') }}</span>
                                        </button>
                                    @endif

                                    @if ($hargaBesar > 0)
                                        <button onclick="selectPackage(this, 'jumbo', {{ $menu->id }})" 
                                            class="package-btn px-3 py-1.5 text-xs font-medium rounded-lg border-2 border-gray-200 text-gray-600 hover:border-blue-500 hover:text-blue-600 transition-all duration-200"
                                            data-price="{{ $hargaBesar }}">
                                            JUMBO<br>
                                            <span class="text-[10px]">Rp {{ number_format($hargaBesar, 0, ',', '.') }}</span>
                                        </button>
                                    @endif
                                    
                                    @if ($hargaKhusus > 0)
                                        <button onclick="selectPackage(this, 'smart', {{ $menu->id }})" 
                                            class="package-btn px-3 py-1.5 text-xs font-medium rounded-lg border-2 border-gray-200 text-gray-600 hover:border-blue-500 hover:text-blue-600 transition-all duration-200"
                                            data-price="{{ $hargaKhusus }}">
                                            SMART<br>
                                            <span class="text-[10px]">Rp {{ number_format($hargaKhusus, 0, ',', '.') }}</span>
                                        </button>
                                    @endif
                                </div>

                                {{-- Deskripsi --}}
                                <div class="mb-3">
                                    <p class="text-sm text-gray-600 truncate" id="desc-text-{{ $menu->id }}">
                                        {{ Str::limit($menu->deskripsi, 60) }}
                                    </p>
                                    @if(strlen($menu->deskripsi) > 60)
                                    <button onclick="toggleDescription('desc-text-{{ $menu->id }}', 'desc-full-{{ $menu->id }}', 'btn-desc-{{ $menu->id }}')" 
                                        id="btn-desc-{{ $menu->id }}"
                                        class="text-xs text-blue-600 hover:text-blue-800 font-medium hover:underline mt-0.5 transition-all duration-200">
                                        selengkapnya >>
                                    </button>
                                    <p id="desc-full-{{ $menu->id }}" class="text-sm text-gray-600 hidden mt-1">
                                        {{ $menu->deskripsi }}
                                    </p>
                                    @endif
                                </div>

                                {{-- Harga & Tombol --}}
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-xs text-gray-500">Mulai dari</span>
                                        <p class="text-lg font-bold text-blue-600" id="price-display-{{ $menu->id }}">
                                            Rp 0
                                        </p>
                                    </div>
                                    <button onclick="addToCart({{ $menu->id }})" 
                                        class="add-to-cart-btn px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-all duration-200 hover:scale-105 hover:shadow-lg hover:shadow-blue-500/30">
                                        Pesan
                                    </button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="w-full text-center py-12">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="text-gray-500">Tidak ada menu tersedia untuk tanggal ini</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    @else
        {{-- Jika tidak ada menu sama sekali --}}
        <div class="text-center py-16">
            <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <h3 class="text-xl font-semibold text-gray-600">
                @if($isKelas1 ?? false)
                    Belum Ada Menu Khusus Kelas 1
                @else
                    Belum Ada Menu
                @endif
            </h3>
            <p class="text-gray-400 mt-2">Menu akan segera tersedia. Silakan cek kembali nanti.</p>
        </div>
    @endif
</div>

{{-- JavaScript --}}
<script>
// ============================================
// VARIABEL GLOBAL
// ============================================
let selectedPackages = {};

// ============================================
// FILTER MENU BERDASARKAN TANGGAL
// ============================================
function filterMenu(date) {
    document.querySelectorAll('.menu-group').forEach(el => {
        el.style.display = 'none';
    });
    
    const groupId = 'menu-group-' + date.replace(/-/g, '');
    const target = document.getElementById(groupId);
    if (target) {
        target.style.display = 'block';
    }
    
    document.querySelectorAll('.date-filter-btn').forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white');
        btn.classList.add('bg-gray-100', 'text-gray-700');
        if (btn.dataset.date === date) {
            btn.classList.remove('bg-gray-100', 'text-gray-700');
            btn.classList.add('bg-blue-600', 'text-white');
        }
    });
}

// ============================================
// SCROLL FUNCTIONS
// ============================================
function scrollLeft(btn) {
    const container = btn.closest('.relative').querySelector('.menu-scroll');
    container.scrollBy({ left: -340, behavior: 'smooth' });
}

function scrollRight(btn) {
    const container = btn.closest('.relative').querySelector('.menu-scroll');
    container.scrollBy({ left: 340, behavior: 'smooth' });
}

// ============================================
// TOGGLE DESKRIPSI
// ============================================
function toggleDescription(textId, fullId, btnId) {
    const textEl = document.getElementById(textId);
    const fullEl = document.getElementById(fullId);
    const btnEl = document.getElementById(btnId);
    
    if (fullEl.classList.contains('hidden')) {
        textEl.classList.add('hidden');
        fullEl.classList.remove('hidden');
        btnEl.textContent = 'sembunyikan <<';
    } else {
        textEl.classList.remove('hidden');
        fullEl.classList.add('hidden');
        btnEl.textContent = 'selengkapnya >>';
    }
}

// ============================================
// SELECT PACKAGE
// ============================================
function selectPackage(button, packageSize, menuId) {
    const parent = button.parentElement;
    const buttons = parent.querySelectorAll('.package-btn');
    buttons.forEach(btn => {
        btn.removeAttribute('data-active');
    });
    
    button.setAttribute('data-active', 'true');
    
    const price = button.dataset.price;
    const priceDisplay = document.getElementById('price-display-' + menuId);
    if (priceDisplay) {
        priceDisplay.textContent = 'Rp ' + formatNumber(price);
    }
    
    selectedPackages[menuId] = {
        paket: packageSize,
        price: parseInt(price)
    };
}

// ============================================
// FORMAT NUMBER
// ============================================
function formatNumber(num) {
    let clean = num.toString().replace(/\.00$/, '');
    return clean.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// ============================================
// ADD TO CART
// ============================================
function addToCart(menuId) {
    if (!selectedPackages[menuId]) {
        showNotification('⚠️ Silakan pilih paket terlebih dahulu!', 'warning');
        return;
    }
    
    const data = selectedPackages[menuId];
    
    fetch('{{ route("customer.cart.add") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            menu_id: menuId,
            paket: data.paket,
            price: data.price,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✅ ' + data.message, 'success');
            updateCartCount();
        } else {
            let errorMsg = '❌ ' + (data.message || 'Gagal menambahkan!');
            if (data.errors) {
                errorMsg = Object.values(data.errors).flat().join('\n');
            }
            showNotification(errorMsg, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('❌ Terjadi kesalahan!', 'error');
    });
}

// ============================================
// UPDATE CART COUNT
// ============================================
function updateCartCount() {
    // Update badge keranjang
    const cartBadge = document.getElementById('cart-badge');
    if (cartBadge) {
        // Bisa diisi dengan AJAX request
    }
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
    }, 3000);
}

// ============================================
// AUTO SCROLL DENGAN MOUSE WHEEL
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.menu-scroll').forEach(container => {
        container.addEventListener('wheel', function(e) {
            if (e.deltaY !== 0) {
                e.preventDefault();
                this.scrollLeft += e.deltaY;
            }
        }, { passive: false });
    });
});

// ============================================
// SHOW/HIDE SCROLL BUTTONS
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.menu-scroll').forEach(container => {
        const wrapper = container.closest('.relative');
        const leftBtn = wrapper.querySelector('button[onclick^="scrollLeft"]');
        const rightBtn = wrapper.querySelector('button[onclick^="scrollRight"]');
        
        function checkScroll() {
            if (container.scrollLeft === 0) {
                leftBtn.classList.add('opacity-0', 'pointer-events-none');
                leftBtn.classList.remove('opacity-100');
            } else {
                leftBtn.classList.remove('opacity-0', 'pointer-events-none');
                leftBtn.classList.add('opacity-100');
            }
            
            if (container.scrollLeft + container.clientWidth >= container.scrollWidth - 10) {
                rightBtn.classList.add('opacity-0', 'pointer-events-none');
                rightBtn.classList.remove('opacity-100');
            } else {
                rightBtn.classList.remove('opacity-0', 'pointer-events-none');
                rightBtn.classList.add('opacity-100');
            }
        }
        
        container.addEventListener('scroll', checkScroll);
        setTimeout(checkScroll, 100);
    });
});
</script>

<style>
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.hide-scrollbar::-webkit-scrollbar {
    display: none;
}

.package-btn[data-active="true"] {
    border-color: #2563eb;
    background-color: #eff6ff;
    color: #1d4ed8;
}

.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}

#desc-full-1, #desc-full-2, #desc-full-3, #desc-full-4 {
    animation: fadeIn 0.3s ease-out;
}

@media (max-width: 640px) {
    .min-w-\[320px\] {
        min-width: 280px;
        max-width: 280px;
    }
}

/* Date filter button scroll */
.overflow-x-auto {
    scrollbar-width: thin;
}
</style>

@endsection