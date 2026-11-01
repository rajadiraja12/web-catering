{{-- resources/views/kasir/order_manual.blade.php --}}

@extends('app')
@include('layout.cashier')
@section('content')
@yield('nav')
<br>
<br>
<br>
<br>
<br>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Order Manual</h1>
            <p class="text-sm text-gray-500">Input pesanan customer secara manual</p>
        </div>
        <div class="flex gap-3">
            <span class="text-sm text-gray-500">{{ date('d-m-Y H:i') }}</span>
            {{-- Tombol buka modal --}}
            <button type="button" onclick="openOrderModal()" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-lg transition-all shadow-sm hover:shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Order Baru
            </button>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MODAL ORDER --}}
    {{-- ============================================ --}}
    <div id="order-modal" tabindex="-1" aria-hidden="true" 
         class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black/50 backdrop-blur-sm">
        <div class="relative p-4 w-full max-w-4xl max-h-full">
            <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 md:p-8">
                
                {{-- Header Modal --}}
                <div class="flex items-center justify-between pb-5 mb-5 border-b-2 border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg shadow-blue-500/30">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Buat Order Manual</h3>
                            <p class="text-sm text-gray-500 mt-0.5">Isi form di bawah untuk membuat order baru</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeOrderModal()" 
                            class="text-gray-400 bg-gray-50 hover:bg-gray-200 hover:text-gray-900 rounded-xl text-sm w-10 h-10 inline-flex justify-center items-center transition-all duration-200 hover:rotate-90">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                {{-- Form --}}
                <form id="form-order-manual" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- ===== TANGGAL MENU ===== --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Tanggal Menu <span class="text-red-500">*</span></label>
                            <div class="flex gap-3">
                                <select id="tanggal_menu" name="tanggal_menu" required 
                                        class="flex-1 px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                        onchange="loadMenuByDate()">
                                    <option value="">Pilih Tanggal Menu</option>
                                    @foreach($tanggalMenu as $tgl)
                                        <option value="{{ $tgl['value'] }}" {{ $tgl['value'] == $tanggalDefault ? 'selected' : '' }}>
                                            {{ $tgl['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" onclick="loadMenuByDate()" 
                                        class="px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-xl transition-all">
                                     Muat Menu
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Pilih tanggal menu yang tersedia</p>
                        </div>

                        {{-- ===== MENU ===== --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Menu <span class="text-red-500">*</span></label>
                            <select name="menu_id" id="menu_id" required 
                                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    onchange="updatePrice()">
                                <option value="">-- Pilih Menu --</option>
                                @foreach($menus as $menu)
                                    <option value="{{ $menu->id }}" 
                                            data-best="{{ $menu->harga_paket_kecil }}"
                                            data-happy="{{ $menu->harga_paket_sedang }}"
                                            data-jumbo="{{ $menu->harga_paket_besar }}"
                                            data-smart="{{ $menu->harga_paket_khusus }}">
                                        {{ $menu->nama_menu }}-{{ $menu->tipe_menu }}kamu kemana
                                    </option>
                                @endforeach
                            </select>
                            <div id="menu_loading" class="hidden text-xs text-blue-600 mt-1">Memuat menu...</div>
                            <div id="menu_empty" class="hidden text-xs text-yellow-600 mt-1"> Tidak ada menu untuk tanggal ini</div>
                        </div>

                        {{-- ===== PAKET ===== --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Paket <span class="text-red-500">*</span></label>
                            <select name="paket" id="paket" required 
                                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    onchange="updatePrice()">
                                <option value="">Pilih Paket</option>
                                <option value="best">BEST</option>
                                <option value="happy">HAPPY</option>
                                <option value="jumbo">JUMBO</option>
                                <option value="smart">SMART</option>
                            </select>
                        </div>

                        {{-- ===== CUSTOMER ===== --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Cari Customer <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <input type="text" id="search_customer" 
                                       class="w-full pl-10 pr-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                       placeholder="Cari nama / NISN / no HP..."
                                       autocomplete="off">
                            </div>
                            <div id="customer_list" class="absolute z-20 mt-2 max-h-40 overflow-y-auto hidden border border-gray-200 rounded-xl"></div>
                            <input type="hidden" name="customer_id" id="customer_id" required>
                            <div id="selected_customer" class="mt-2 hidden">
                                <div class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded-xl p-3">
                                    <div>
                                        <p class="font-semibold" id="selected_customer_name">-</p>
                                        <p class="text-xs text-gray-500" id="selected_customer_info">-</p>
                                    </div>
                                    <button type="button" onclick="clearCustomer()" class="text-red-500 hover:text-red-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- ===== JUMLAH ===== --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah <span class="text-red-500">*</span></label>
                            <input type="number" name="quantity" id="quantity" value="1" min="1" max="10" required
                                   class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                   oninput="updatePrice()">
                        </div>

                        {{-- ===== HARGA ===== --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Harga Satuan</label>
                            <input type="text" id="harga_display" readonly
                                   class="w-full px-4 py-3 bg-gray-100 border-2 border-gray-200 rounded-xl cursor-not-allowed"
                                   value="Rp 0">
                            <input type="hidden" name="harga" id="harga" value="0">
                        </div>

                        {{-- ===== TOTAL ===== --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Total</label>
                            <input type="text" id="total_display" readonly
                                   class="w-full px-4 py-3 bg-gray-100 border-2 border-gray-200 rounded-xl cursor-not-allowed font-bold text-blue-600"
                                   value="Rp 0">
                            <input type="hidden" name="total" id="total" value="0">
                        </div>

                        {{-- ===== STATUS PEMBAYARAN ===== --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status Pembayaran <span class="text-red-500">*</span></label>
                            <select name="payment_status" id="payment_status" required 
                                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                <option value="paid">Lunas</option>
                                <option value="unpaid">Belum Bayar</option>
                            </select>
                        </div>

                        {{-- ===== CATATAN ===== --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan</label>
                            <textarea name="catatan" id="catatan" rows="2"
                                      class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                      placeholder="Tambahkan catatan jika diperlukan"></textarea>
                        </div>
                    </div>

                    {{-- Tombol --}}
                    <div class="flex gap-3 mt-4 pt-4 border-t border-gray-200">
                        <button type="submit" 
                                class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-all hover:scale-[1.02]">
                            <span id="btnText">Simpan Order</span>
                            <span id="btnLoading" class="hidden">
                                <svg class="inline w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Menyimpan...
                            </span>
                        </button>
                        <button type="button" onclick="resetForm()" 
                                class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-xl transition-all">
                            Reset
                        </button>
                        <button type="button" onclick="closeOrderModal()" 
                                class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-xl transition-all">
                            Tutup
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Daftar Order Hari Ini --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Order Hari Ini</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Order</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Menu</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Paket</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($orders as $index => $order)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-sm">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 font-mono text-sm">{{ $order->kode_order }}</td>
                        <td class="px-4 py-3">{{ $order->customer->name ?? 'Unknown' }}</td>
                        <td class="px-4 py-3">{{ $order->menu->nama_menu ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium uppercase">
                                {{ $order->paket }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-blue-600">
                            Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($order->payment_status == 'paid')
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Lunas</span>
                            @else
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Belum Bayar</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1 flex-wrap">
                                <button onclick="printStruk({{ $order->id }})" 
                                        class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium rounded-lg transition-all">
                                     Struk
                                </button>
                                
                                @if($order->payment_status == 'unpaid')
                                    <button onclick="updatePayment({{ $order->id }}, 'paid')" 
                                            class="px-3 py-1 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg transition-all">
                                        Bayar
                                    </button>
                                @else
                                    <button onclick="updatePayment({{ $order->id }}, 'unpaid')" 
                                            class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-medium rounded-lg transition-all">
                                        Batal Bayar
                                    </button>
                                @endif
                                
                                @if($order->payment_status == 'unpaid')
                                    <button onclick="deleteOrder({{ $order->id }})" 
                                            class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-lg transition-all">
                                        Hapus
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                            Belum ada order hari ini
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// ============================================
// OPEN & CLOSE MODAL
// ============================================
function openOrderModal() {
    const modal = document.getElementById('order-modal');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Reset form
    resetForm();
}

function closeOrderModal() {
    const modal = document.getElementById('order-modal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function resetForm() {
    document.getElementById('form-order-manual').reset();
    document.getElementById('customer_id').value = '';
    document.getElementById('selected_customer').classList.add('hidden');
    document.getElementById('search_customer').value = '';
    document.getElementById('harga_display').value = 'Rp 0';
    document.getElementById('total_display').value = 'Rp 0';
    document.getElementById('harga').value = 0;
    document.getElementById('total').value = 0;
    document.getElementById('menu_empty').classList.add('hidden');
    document.getElementById('menu_loading').classList.add('hidden');
    
    // Load menu otomatis jika ada tanggal default
    const tanggalSelect = document.getElementById('tanggal_menu');
    if (tanggalSelect.value) {
        loadMenuByDate();
    }
}

// ============================================
// LOAD MENU BY DATE
// ============================================
function loadMenuByDate() {
    const tanggal = document.getElementById('tanggal_menu').value;
    
    if (!tanggal) {
        document.getElementById('menu_id').innerHTML = '<option value="">-- Pilih Menu --</option>';
        document.getElementById('menu_empty').classList.add('hidden');
        return;
    }
    
    const loading = document.getElementById('menu_loading');
    const empty = document.getElementById('menu_empty');
    const menuSelect = document.getElementById('menu_id');
    
    loading.classList.remove('hidden');
    empty.classList.add('hidden');
    menuSelect.innerHTML = '<option value="">⏳ Memuat...</option>';
    menuSelect.disabled = true;
    
    fetch('{{ route("kasir.order.get-menu") }}?tanggal=' + encodeURIComponent(tanggal), {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        loading.classList.add('hidden');
        menuSelect.disabled = false;
        
        if (data.success && data.data.length > 0) {
            let html = '<option value="">-- Pilih Menu --</option>';
            data.data.forEach(menu => {
                html += `
                    <option value="${menu.id}" 
                            data-best="${menu.harga_paket_kecil}"
                            data-happy="${menu.harga_paket_sedang}"
                            data-jumbo="${menu.harga_paket_besar}"
                            data-smart="${menu.harga_paket_khusus}">
                        ${menu.nama_menu} - ${menu.tipe_menu}
                    </option>
                `;
            });
            menuSelect.innerHTML = html;
            updatePrice();
        } else {
            menuSelect.innerHTML = '<option value="">-- Tidak ada menu --</option>';
            empty.classList.remove('hidden');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        loading.classList.add('hidden');
        menuSelect.disabled = false;
        menuSelect.innerHTML = '<option value="">-- Gagal memuat menu --</option>';
        showNotification('❌ Gagal memuat menu', 'error');
    });
}

// ============================================
// CETAK STRUK
// ============================================
function printStruk(orderId) {
    const url = '{{ route("kasir.order.print-struk", ['id' => ':id']) }}'.replace(':id', orderId);
    window.open(url, '_blank', 'width=400,height=700');
}

// ============================================
// SEARCH CUSTOMER
// ============================================
let searchTimeout;

document.getElementById('search_customer').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const search = this.value.trim();
    
    if (search.length < 2) {
        document.getElementById('customer_list').classList.add('hidden');
        return;
    }
    
    searchTimeout = setTimeout(() => {
        fetch('{{ route("kasir.order.search-customer") }}?search=' + encodeURIComponent(search), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            const customerList = document.getElementById('customer_list');
            
            if (data.success && data.data.length > 0) {
                let html = '';
                data.data.forEach(customer => {
                    html += `
                        <div onclick="selectCustomer(${customer.id}, '${customer.name}', '${customer.username} - ${customer.kelas}')" 
                             class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0">
                            <p class="font-medium">${customer.name}</p>
                            <p class="text-xs text-gray-500">${customer.username} - ${customer.kelas}</p>
                        </div>
                    `;
                });
                customerList.innerHTML = html;
                customerList.classList.remove('hidden');
            } else {
                customerList.innerHTML = '<div class="px-4 py-2 text-gray-500">Tidak ada customer ditemukan</div>';
                customerList.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }, 300);
});

// ============================================
// SELECT CUSTOMER
// ============================================
function selectCustomer(id, name, info) {
    document.getElementById('customer_id').value = id;
    document.getElementById('selected_customer_name').textContent = name;
    document.getElementById('selected_customer_info').textContent = info;
    document.getElementById('selected_customer').classList.remove('hidden');
    document.getElementById('customer_list').classList.add('hidden');
    document.getElementById('search_customer').value = name;
}

function clearCustomer() {
    document.getElementById('customer_id').value = '';
    document.getElementById('selected_customer').classList.add('hidden');
    document.getElementById('search_customer').value = '';
    document.getElementById('search_customer').focus();
}

// ============================================
// UPDATE PRICE
// ============================================
function updatePrice() {
    const menuSelect = document.getElementById('menu_id');
    const paketSelect = document.getElementById('paket');
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    
    if (!menuSelect.value || !paketSelect.value) {
        document.getElementById('harga_display').value = 'Rp 0';
        document.getElementById('total_display').value = 'Rp 0';
        document.getElementById('harga').value = 0;
        document.getElementById('total').value = 0;
        return;
    }
    
    const selectedOption = menuSelect.options[menuSelect.selectedIndex];
    const harga = parseInt(selectedOption.dataset[paketSelect.value]) || 0;
    
    document.getElementById('harga_display').value = 'Rp ' + formatNumber(harga);
    document.getElementById('total_display').value = 'Rp ' + formatNumber(harga * quantity);
    document.getElementById('harga').value = harga;
    document.getElementById('total').value = harga * quantity;
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// ============================================
// SUBMIT ORDER
// ============================================
document.getElementById('form-order-manual').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const customerId = document.getElementById('customer_id').value;
    if (!customerId) {
        showNotification('⚠️ Silakan pilih customer terlebih dahulu!', 'warning');
        return;
    }
    
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    const submitBtn = this.querySelector('button[type="submit"]');
    
    btnText.classList.add('hidden');
    btnLoading.classList.remove('hidden');
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    
    fetch('{{ route("kasir.order.store") }}', {
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
            showNotification('✅ ' + data.message, 'success');
            setTimeout(() => {
                closeOrderModal();
                location.reload();
            }, 1000);
        } else {
            let errorMsg = '❌ Gagal!';
            if (data.errors) {
                errorMsg = Object.values(data.errors).flat().join('\n');
            } else if (data.message) {
                errorMsg = data.message;
            }
            showNotification(errorMsg, 'error');
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

// ============================================
// UPDATE PAYMENT
// ============================================
function updatePayment(orderId, status) {
    if (!confirm('Yakin ingin mengubah status pembayaran?')) return;
    
    fetch('{{ route("kasir.order.update-payment", ['id' => ':id']) }}'.replace(':id', orderId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ payment_status: status })
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
        showNotification('❌ ' + error.message, 'error');
    });
}

// ============================================
// DELETE ORDER
// ============================================
function deleteOrder(orderId) {
    if (!confirm('Yakin ingin menghapus order ini?')) return;
    
    fetch('{{ route("kasir.order.destroy", ['id' => ':id']) }}'.replace(':id', orderId), {
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
        showNotification('❌ ' + error.message, 'error');
    });
}

// ============================================
// NOTIFICATION
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

// ============================================
// CLICK OUTSIDE MODAL TO CLOSE
// ============================================
document.addEventListener('click', function(event) {
    // Close customer list
    const customerList = document.getElementById('customer_list');
    const searchInput = document.getElementById('search_customer');
    
    if (customerList && !customerList.classList.contains('hidden')) {
        if (!customerList.contains(event.target) && event.target !== searchInput) {
            customerList.classList.add('hidden');
        }
    }
    
    // Close modal when clicking outside
    const modal = document.getElementById('order-modal');
    if (modal && !modal.classList.contains('hidden')) {
        if (event.target === modal) {
            closeOrderModal();
        }
    }
});

// ============================================
// CLOSE MODAL WITH ESCAPE
// ============================================
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeOrderModal();
    }
});

// ============================================
// LOAD MENU OTOMATIS SAAT HALAMAN LOAD
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const tanggalSelect = document.getElementById('tanggal_menu');
    if (tanggalSelect.value) {
        loadMenuByDate();
    }
});
</script>

<style>
#customer_list {
    position: absolute;
    width: 100%;
    background: white;
    z-index: 10;
    max-height: 200px;
    overflow-y: auto;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

#customer_list::-webkit-scrollbar {
    width: 6px;
}

#customer_list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#customer_list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

#customer_list::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Modal transition */
#order-modal {
    transition: opacity 0.3s ease;
}

#order-modal .relative {
    transition: transform 0.3s ease, opacity 0.3s ease;
    transform: scale(0.95);
    opacity: 0;
}

#order-modal:not(.hidden) .relative {
    transform: scale(1);
    opacity: 1;
}
</style>

@endsection