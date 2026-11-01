{{-- resources/views/kasir/customers.blade.php --}}

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
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Data Customer</h1>
            <p class="text-sm text-gray-500">Kelola data customer</p>
        </div>
        <button type="button" data-modal-target="add-customer-modal" data-modal-toggle="add-customer-modal"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium text-sm rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Customer
        </button>
    </div>

    {{-- Search --}}
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" id="searchCustomer" 
                   class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-sm"
                   placeholder="Cari customer...">
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">username</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No HP</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        {{-- <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th> --}}
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="customerTableBody">
                    @forelse($customers as $index => $customer)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-sm">{{ $loop->iteration + (($customers->currentPage() - 1) * $customers->perPage()) }}</td>
                        <td class="px-4 py-3 font-medium">{{ $customer->name }}</td>
                        <td class="px-4 py-3 font-mono text-sm">{{ $customer->username }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                                {{ $customer->kelas }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $customer->no_hp }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($customer->status == 'Active')
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Active</span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">Non Aktif</span>
                            @endif
                        </td>
                        {{-- <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1 flex-wrap">
                                <button onclick="editCustomer({{ $customer->id }})" 
                                        class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-medium rounded-lg transition-all">
                                    Edit
                                </button>
                                <button onclick="toggleStatus({{ $customer->id }})" 
                                        class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium rounded-lg transition-all">
                                    Status
                                </button>
                                <button onclick="deleteCustomer({{ $customer->id }})" 
                                        class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-lg transition-all">
                                    Hapus
                                </button>
                            </div>
                        </td> --}}
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            Belum ada customer
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200">
            {{ $customers->links() }}
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- MODAL TAMBAH CUSTOMER --}}
{{-- ============================================ --}}
<div id="add-customer-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black/50 backdrop-blur-sm">
    <div class="relative p-4 w-full max-w-2xl max-h-full">
        <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 md:p-8">
            
            <div class="flex items-center justify-between pb-5 mb-5 border-b-2 border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg shadow-green-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Tambah Customer</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Isi form di bawah untuk menambahkan customer</p>
                    </div>
                </div>
                <button type="button" data-modal-hide="add-customer-modal" class="text-gray-400 bg-gray-50 hover:bg-gray-200 hover:text-gray-900 rounded-xl text-sm w-10 h-10 inline-flex justify-center items-center transition-all duration-200 hover:rotate-90">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="form-add-customer" method="POST">
                @csrf
                <div class="grid gap-4 grid-cols-1 md:grid-cols-2">
                    {{-- Nama --}}
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="add_name" required
                               class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    
                    {{-- NISN --}}
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" id="add_username" required
                               class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>

                    {{-- Kelas --}}
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Kelas <span class="text-red-500">*</span></label>
                        <select name="kelas" id="add_kelas" required
                                class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="" disabled selected>Pilih Kelas</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                        </select>
                    </div>

                    {{-- Nama Kelas --}}
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Nama Kelas <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_kelas" id="add_nama_kelas" required
                               class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                               placeholder="Contoh: Alpukat, IPA 1">
                    </div>

                    {{-- No HP --}}
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">No HP <span class="text-red-500">*</span></label>
                        <input type="text" name="no_hp" id="add_no_hp" required
                               class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>

                    {{-- Alergi --}}
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Alergi</label>
                        <input type="text" name="alergi" id="add_alergi"
                               class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                               placeholder="Contoh: Kacang, Udang">
                    </div>

                    {{-- Password --}}
                    <div>
                        {{-- <label class="block mb-2 text-sm font-semibold text-gray-700">Password <span class="text-red-500">*</span></label> --}}
                        <input type="hidden" name="password" id="add_password" required minlength="8" value="acesgt4rghcrexgfhjcrcjy"
                               class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                </div>

                {{-- Preview Gabungan Kelas --}}
                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-xs text-blue-700">
                        <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Kelas akan digabung menjadi: <strong id="kelas_preview">1-Alpukat</strong>
                        <span class="text-xs text-gray-500">(Kelas-Nama Kelas)</span>
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-5 mt-5 border-t-2 border-gray-100">
                    <button data-modal-hide="add-customer-modal" type="button" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all">Batal</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-xl transition-all hover:scale-105 hover:shadow-lg hover:shadow-green-500/30">
                        Simpan Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- MODAL EDIT CUSTOMER --}}
{{-- ============================================ --}}
<div id="edit-customer-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black/50 backdrop-blur-sm">
    <div class="relative p-4 w-full max-w-2xl max-h-full">
        <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 md:p-8">
            
            <div class="flex items-center justify-between pb-5 mb-5 border-b-2 border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl shadow-lg shadow-yellow-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Edit Customer</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Edit data customer yang dipilih</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditModal()" class="text-gray-400 bg-gray-50 hover:bg-gray-200 hover:text-gray-900 rounded-xl text-sm w-10 h-10 inline-flex justify-center items-center transition-all duration-200 hover:rotate-90">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="form-edit-customer" method="POST">
                @csrf
                @method('PUT')
                <div class="grid gap-4 grid-cols-1 md:grid-cols-2">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="edit_name" required
                               class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">NISN <span class="text-red-500">*</span></label>
                        <input type="text" name="username" id="edit_username" required
                               class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Kelas <span class="text-red-500">*</span></label>
                        <select name="kelas" id="edit_kelas" required
                                class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="" disabled>Pilih Kelas</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Nama Kelas <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_kelas" id="edit_nama_kelas" required
                               class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">No HP <span class="text-red-500">*</span></label>
                        <input type="text" name="no_hp" id="edit_no_hp" required
                               class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Alergi</label>
                        <input type="text" name="alergi" id="edit_alergi"
                               class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Password <span class="text-gray-400 text-xs">(Kosongkan jika tidak diubah)</span></label>
                        <input type="password" name="password" id="edit_password" minlength="8"
                               class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-5 mt-5 border-t-2 border-gray-100">
                    <button type="button" onclick="closeEditModal()" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all">Batal</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-yellow-500 hover:bg-yellow-600 rounded-xl transition-all hover:scale-105 hover:shadow-lg hover:shadow-yellow-500/30">
                        Update Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ============================================
// PREVIEW GABUNGAN KELAS
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const kelasSelect = document.getElementById('add_kelas');
    const namaKelasInput = document.getElementById('add_nama_kelas');
    const kelasPreview = document.getElementById('kelas_preview');
    
    if (kelasSelect && namaKelasInput && kelasPreview) {
        function updateKelasPreview() {
            const kelas = kelasSelect.value;
            const namaKelas = namaKelasInput.value || '...';
            
            if (kelas && namaKelas) {
                kelasPreview.textContent = kelas + '-' + namaKelas;
            } else if (kelas) {
                kelasPreview.textContent = kelas + '-...';
            } else if (namaKelas) {
                kelasPreview.textContent = '?-?' + namaKelas;
            } else {
                kelasPreview.textContent = '1-Alpukat';
            }
        }
        
        kelasSelect.addEventListener('change', updateKelasPreview);
        namaKelasInput.addEventListener('input', updateKelasPreview);
    }
});

// ============================================
// EDIT CUSTOMER
// ============================================
function editCustomer(id) {
    const url = "{{ route('kasir.customers.edit', ['id' => ':id']) }}".replace(':id', id);
    
    showNotification('⏳ Memuat data customer...', 'info');
    
    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const customer = data.data;
            
            document.getElementById('edit_name').value = customer.name;
            document.getElementById('edit_username').value = customer.username;
            
            if (customer.kelas && customer.kelas.includes('-')) {
                const parts = customer.kelas.split('-');
                document.getElementById('edit_kelas').value = parts[0] || '';
                document.getElementById('edit_nama_kelas').value = parts.slice(1).join('-') || '';
            }
            
            document.getElementById('edit_no_hp').value = customer.no_hp;
            document.getElementById('edit_alergi').value = customer.alergi || '';
            document.getElementById('edit_password').value = '';
            
            const form = document.getElementById('form-edit-customer');
            const actionUrl = "{{ route('kasir.customers.update', ['id' => ':id']) }}".replace(':id', id);
            form.action = actionUrl;
            
            document.getElementById('edit-customer-modal').classList.remove('hidden');
            document.getElementById('edit-customer-modal').style.display = 'flex';
            
            const notif = document.getElementById('custom-notification');
            if (notif) notif.remove();
        } else {
            showNotification('❌ Gagal memuat data', 'error');
        }
    })
    .catch(error => {
        showNotification('❌ ' + error.message, 'error');
        console.error('Error:', error);
    });
}

function closeEditModal() {
    document.getElementById('edit-customer-modal').classList.add('hidden');
    document.getElementById('edit-customer-modal').style.display = 'none';
}

// ============================================
// TOGGLE STATUS
// ============================================
function toggleStatus(id) {
    if (!confirm('Yakin ingin mengubah status customer ini?')) return;
    
    fetch('{{ route("kasir.customers.toggle-status", ['id' => ':id']) }}'.replace(':id', id), {
        method: 'PATCH',
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
// DELETE CUSTOMER
// ============================================
function deleteCustomer(id) {
    if (!confirm('Yakin ingin menghapus customer ini?')) return;
    
    fetch('{{ route("kasir.customers.delete", ['id' => ':id']) }}'.replace(':id', id), {
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
// SEARCH CUSTOMER - PERBAIKAN
// ============================================
let searchTimeout;
document.getElementById('searchCustomer').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const search = this.value.trim();
    
    // Jika search kosong, reload untuk menampilkan semua data
    if (search === '') {
        location.reload();
        return;
    }
    
    // Jika search kurang dari 2 karakter, tampilkan pesan atau biarkan saja
    if (search.length < 2) {
        // Tampilkan pesan di tabel
        const tbody = document.getElementById('customerTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                    🔍 Minimal 2 karakter untuk mencari
                </td>
            </tr>
        `;
        return;
    }
    
    // Lakukan pencarian
    searchTimeout = setTimeout(() => {
        // Tampilkan loading
        const tbody = document.getElementById('customerTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                    <svg class="w-6 h-6 animate-spin mx-auto text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Mencari data...
                </td>
            </tr>
        `;
        
        fetch('{{ route("kasir.customers.search") }}?search=' + encodeURIComponent(search), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '';
                if (data.data.length === 0) {
                    html = `
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                😕 Tidak ada customer yang ditemukan
                            </td>
                        </tr>
                    `;
                } else {
                    data.data.forEach((customer, index) => {
                        html += `
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-sm">${index + 1}</td>
                                <td class="px-4 py-3 font-medium">${customer.name}</td>
                                <td class="px-4 py-3 font-mono text-sm">${customer.username}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                                        ${customer.kelas}
                                    </span>
                                </td>
                                <td class="px-4 py-3">${customer.no_hp}</td>
                                <td class="px-4 py-3 text-center">
                                    ${customer.status == 'Active' 
                                        ? '<span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">✅ Active</span>'
                                        : '<span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">❌ Non Aktif</span>'}
                                </td>
                            </tr>
                        `;
                    });
                }
                document.getElementById('customerTableBody').innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('customerTableBody').innerHTML = `
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-red-500">
                        ❌ Terjadi kesalahan saat mencari data
                    </td>
                </tr>
            `;
        });
    }, 300);
});

// ============================================
// FORM SUBMIT - TAMBAH CUSTOMER
// ============================================
document.getElementById('form-add-customer').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = `
        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Menyimpan...
    `;
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    
    fetch('{{ route("kasir.customers.store") }}', {
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
            setTimeout(() => location.reload(), 1000);
        } else {
            let errorMsg = '❌ Gagal!';
            if (data.errors) {
                errorMsg = Object.values(data.errors).flat().join('\n');
            } else if (data.message) {
                errorMsg = data.message;
            }
            showNotification(errorMsg, 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        showNotification('❌ ' + error.message, 'error');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// ============================================
// FORM SUBMIT - EDIT CUSTOMER
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-edit-customer');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = `
                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Mengupdate...
            `;
            submitBtn.disabled = true;
            
            const formData = new FormData(this);
            
            fetch(this.action, {
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
                        closeEditModal();
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
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                showNotification('❌ ' + error.message, 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});

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
// CLOSE MODAL WITH ESCAPE
// ============================================
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeEditModal();
    }
});

// ============================================
// CLICK OUTSIDE MODAL TO CLOSE
// ============================================
document.addEventListener('click', function(event) {
    const editModal = document.getElementById('edit-customer-modal');
    if (editModal && !editModal.classList.contains('hidden')) {
        if (event.target === editModal) {
            closeEditModal();
        }
    }
});
</script>
@endsection