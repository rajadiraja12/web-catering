{{-- resources/views/admin/manage_menu.blade.php --}}

@extends('app')
@include('layout.admin')
@section('content')
@yield('nav')
<br>
<br>
<br>
<br>
<br>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-100 border border-green-300 text-green-800 px-4 py-3">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 rounded-lg bg-red-100 border border-red-300 text-red-800 px-4 py-3">
        {{ session('error') }}
    </div>
@endif

{{-- Informasi --}}
<div class="mb-4 flex items-center gap-3 p-3 bg-yellow-50 border border-yellow-200 rounded-xl">
    <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    <div>
        <span class="text-sm font-medium text-yellow-700">Informasi:</span>
        <span class="text-sm text-yellow-600">Maksimal <strong>5 menu</strong> per tanggal untuk setiap tipe menu (Umum dan Kelas 1).</span>
    </div>
</div>

{{-- Tombol Tambah --}}
<div class="mb-4 flex-shrink-0">
    <button type="button" data-modal-target="crud-modal" data-modal-toggle="crud-modal" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-lg transition-all duration-200 shadow-sm hover:shadow-md focus:ring-4 focus:ring-blue-300 focus:outline-none">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Tambah Data
    </button>
</div>

{{-- Filter --}}
<div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Tipe Menu</label>
        <select name="tipe_menu" id="filter_tipe_menu" class="w-full px-4 py-2 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 transition-all"
                onchange="applyFilter()">
            <option value="">Semua Tipe</option>
            <option value="umum" {{ request('tipe_menu') == 'umum' ? 'selected' : '' }}>Menu Umum</option>
            <option value="kelas_1" {{ request('tipe_menu') == 'kelas_1' ? 'selected' : '' }}>Menu Khusus Kelas 1</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal</label>
        <input type="date" name="tanggal" id="filter_tanggal" value="{{ request('tanggal') }}" 
               class="w-full px-4 py-2 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 transition-all"
               onchange="applyFilter()">
    </div>
</div>

<script>
function applyFilter() {
    const tipe = document.getElementById('filter_tipe_menu').value;
    const tanggal = document.getElementById('filter_tanggal').value;
    
    let url = "{{ route('admin.menus') }}?";
    let params = [];
    if (tipe) params.push('tipe_menu=' + tipe);
    if (tanggal) params.push('tanggal=' + tanggal);
    
    window.location.href = url + params.join('&');
}
</script>

{{-- Tabel --}}
<div class="relative overflow-x-auto bg-white shadow-sm rounded-2xl border border-gray-200">
    <table class="w-full text-sm text-left rtl:text-right text-gray-700">
        <thead class="text-xs text-gray-800 uppercase bg-blue-200 from-blue-600 to-blue-800 border-b border-blue-900">
            <tr>
                <th scope="col" class="px-6 py-4 text-center">No</th>
                <th scope="col" class="px-6 py-4">Tanggal</th>
                <th scope="col" class="px-6 py-4">Nama Menu</th>
                <th scope="col" class="px-6 py-4">Gambar</th>
                <th scope="col" class="px-6 py-4">Harga</th>
                <th scope="col" class="px-6 py-4 text-center">Tipe Menu</th>
                <th scope="col" class="px-6 py-4">Deskripsi</th>
                <th scope="col" class="px-6 py-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @if ($menus->count())
            @foreach($menus as $index => $menu)
            <tr class="{{ $loop->iteration % 2 == 0 ? 'bg-gray-50' : 'bg-white' }} border-b border-gray-200 hover:bg-blue-50 transition-colors duration-200">
                <td class="px-6 py-4 text-center font-medium text-gray-900">
                    {{ $loop->iteration + (($menus->currentPage() - 1) * $menus->perPage()) }}
                </td>
                <td class="px-6 py-4">
                    {{ $menu->tanggal ? date('d-m-Y', strtotime($menu->tanggal)) : '-' }}
                </td>
                <td class="px-6 py-4 font-medium">{{ $menu->nama_menu }}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        @if($menu->gambar)
                            <img src="{{ asset($menu->gambar) }}" alt="Menu" class="w-12 h-12 rounded-lg object-cover">
                        @else
                            <div class="w-12 h-12 rounded-lg bg-gray-200 flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4 font-semibold text-blue-600 text-xs">
                    <span class="text-gray-500">BEST:</span> Rp {{ number_format($menu->harga_paket_kecil, 0, ',', '.') }}<br>
                    <span class="text-gray-500">HAPPY:</span> Rp {{ number_format($menu->harga_paket_sedang, 0, ',', '.') }}<br>
                    <span class="text-gray-500">JUMBO:</span> Rp {{ number_format($menu->harga_paket_besar, 0, ',', '.') }}<br>
                    <span class="text-gray-500">SMART:</span> Rp {{ number_format($menu->harga_paket_khusus, 0, ',', '.') }}
                </td>
                <td class="px-6 py-4 text-center">
                    @if($menu->tipe_menu == 'kelas_1')
                        <span class="px-2.5 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">
                            Kelas 1
                        </span>
                    @else
                        <span class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                            Umum
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 text-gray-600">
                    <p class="line-clamp-2">{{ Str::limit($menu->deskripsi, 50) }}</p>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-center gap-2">
                        <button type="button" data-modal-target="crud-modal-edit-{{ $menu->id }}" data-modal-toggle="crud-modal-edit-{{ $menu->id }}"
                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-100 hover:bg-blue-600 text-blue-700 hover:text-white font-medium text-xs rounded-lg transition-all duration-200 border border-blue-200 hover:border-blue-700 focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>
                        <form action="{{ route('admin.menus.delete', $menu->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus menu ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-100 hover:bg-red-600 text-red-700 hover:text-white font-medium text-xs rounded-lg transition-all duration-200 border border-red-200 hover:border-red-700 focus:ring-2 focus:ring-red-300 focus:outline-none">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Hapus
                            </button>
                        </form>
                    </div>
                </td>
            </tr>

            {{-- MODAL EDIT --}}
            <div id="crud-modal-edit-{{ $menu->id }}" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black/50 backdrop-blur-sm">
                <div class="relative p-4 w-full max-w-2xl max-h-full">
                    <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 md:p-8 animate-fadeInDown">
                        
                        <div class="flex items-center justify-between pb-5 mb-5 border-b-2 border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg shadow-blue-500/30">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">Update Menu</h3>
                                    <p class="text-sm text-gray-500 mt-0.5">Isi form di bawah untuk mengupdate menu</p>
                                </div>
                            </div>
                            <button type="button" class="text-gray-400 bg-gray-50 hover:bg-gray-200 hover:text-gray-900 rounded-xl text-sm w-10 h-10 inline-flex justify-center items-center transition-all duration-200 hover:rotate-90" data-modal-hide="crud-modal-edit-{{ $menu->id }}">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                                </svg>
                                <span class="sr-only">Close modal</span>
                            </button>
                        </div>
                        
                        <form action="{{ route('admin.menus.update', $menu->id) }}" method="POST" class="space-y-6" enctype="multipart/form-data" id="edit-form-{{ $menu->id }}">
                            @csrf
                            @method('PUT')
                            {{-- HAPUS input hidden status --}}
                            <div class="grid gap-4 grid-cols-1">
                                <div class="col-span-1">
                                    <label for="nama_menu" class="block mb-2 text-sm font-semibold text-gray-700">Nama Menu <span class="text-red-500">*</span></label>
                                    <input type="text" name="nama_menu" id="nama_menu" value="{{ $menu->nama_menu }}"
                                        class="bg-gray-50 border-2 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full px-4 py-3 transition-all duration-200 hover:border-blue-400" 
                                        required>
                                </div>
                                
                                <div class="col-span-1">
                                    <label for="tanggal" class="block mb-2 text-sm font-semibold text-gray-700">Tanggal Menu <span class="text-red-500">*</span></label>
                                    <input type="date" name="tanggal" id="tanggal" value="{{ $menu->tanggal }}"
                                        class="bg-gray-50 border-2 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full px-4 py-3 transition-all duration-200 hover:border-blue-400" 
                                        required>
                                </div>

                                <div class="grid gap-3 grid-cols-1 md:grid-cols-4">
                                    <div>
                                        <label class="block mb-2 text-sm font-semibold text-gray-700">BEST <span class="text-red-500">*</span></label>
                                        <input type="number" name="harga_paket_kecil" value="{{ $menu->harga_paket_kecil }}"
                                            class="bg-gray-50 border-2 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full px-4 py-3 transition-all duration-200" 
                                            placeholder="0" required min="0">
                                    </div>
                                    <div>
                                        <label class="block mb-2 text-sm font-semibold text-gray-700">HAPPY <span class="text-red-500">*</span></label>
                                        <input type="number" name="harga_paket_sedang" value="{{ $menu->harga_paket_sedang }}"
                                            class="bg-gray-50 border-2 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full px-4 py-3 transition-all duration-200" 
                                            placeholder="0" required min="0">
                                    </div>
                                    <div>
                                        <label class="block mb-2 text-sm font-semibold text-gray-700">JUMBO <span class="text-red-500">*</span></label>
                                        <input type="number" name="harga_paket_besar" value="{{ $menu->harga_paket_besar }}"
                                            class="bg-gray-50 border-2 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full px-4 py-3 transition-all duration-200" 
                                            placeholder="0" required min="0">
                                    </div>
                                    <div>
                                        <label class="block mb-2 text-sm font-semibold text-gray-700">SMART <span class="text-red-500">*</span></label>
                                        <input type="number" name="harga_paket_khusus" value="{{ $menu->harga_paket_khusus }}"
                                            class="bg-gray-50 border-2 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full px-4 py-3 transition-all duration-200" 
                                            placeholder="0" required min="0">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tipe Menu <span class="text-red-500">*</span></label>
                                    <select name="tipe_menu" id="edit_tipe_menu_{{ $menu->id }}" required 
                                            class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                        <option value="umum" {{ $menu->tipe_menu == 'umum' ? 'selected' : '' }}>Menu Umum</option>
                                        <option value="kelas_1" {{ $menu->tipe_menu == 'kelas_1' ? 'selected' : '' }}>Menu Khusus Kelas 1</option>
                                    </select>
                                    <p class="text-xs text-gray-400 mt-1">Menu Umum: untuk kelas 2-12. Menu Khusus Kelas 1: hanya untuk kelas 1.</p>
                                </div>

                                <div>
                                    <label for="deskripsi" class="block mb-2 text-sm font-semibold text-gray-700">Deskripsi Menu</label>
                                    <textarea name="deskripsi" id="deskripsi" rows="3"
                                        class="bg-gray-50 border-2 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full px-4 py-3 transition-all duration-200 resize-none">{{ $menu->deskripsi }}</textarea>
                                </div>

                                <div>
                                    <label class="block mb-2 text-sm font-semibold text-gray-700">Gambar Menu</label>
                                    <div class="flex flex-col sm:flex-row gap-4">
                                        <div class="flex-1">
                                            <div id="dropzone-edit-{{ $menu->id }}" 
                                                class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition-all duration-200 hover:border-blue-400"
                                                onclick="triggerEditFileInput({{ $menu->id }})">
                                                <div id="upload-placeholder-edit-{{ $menu->id }}" class="flex flex-col items-center justify-center pt-5 pb-6">
                                                    <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                                    </svg>
                                                    <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Klik untuk upload</span> atau drag and drop</p>
                                                    <p class="text-xs text-gray-400">PNG, JPG, JPEG (Max. 2MB)</p>
                                                </div>
                                                <input id="image-input-edit-{{ $menu->id }}" type="file" class="hidden" accept="image/*" name="gambar" onchange="handleEditImageUpload(event, {{ $menu->id }})">
                                            </div>
                                        </div>
                                        <div id="preview-container-edit-{{ $menu->id }}" class="{{ $menu->gambar ? '' : 'hidden' }} flex-shrink-0 w-full sm:w-48 h-48">
                                            <div class="relative w-full h-full rounded-xl overflow-hidden border-2 border-gray-200 bg-gray-50">
                                                <img id="image-preview-edit-{{ $menu->id }}" 
                                                     src="{{ $menu->gambar ? asset($menu->gambar) : '' }}" 
                                                     class="w-full h-full object-cover" alt="Preview">
                                                <button type="button" onclick="removeEditImage({{ $menu->id }})" 
                                                    class="absolute top-2 right-2 p-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg shadow-lg transition-all duration-200 hover:scale-110 z-10">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-5 border-t-2 border-gray-100">
                                <button data-modal-hide="crud-modal-edit-{{ $menu->id }}" type="button" 
                                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all duration-200">
                                    Batal
                                </button>
                                <button type="submit" 
                                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-all duration-200 hover:scale-105 hover:shadow-lg hover:shadow-blue-500/30">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Update Menu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
            @else
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm font-medium">Tidak ada data menu</p>
                    </div>
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Pagination --}}
    <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between p-4 bg-gray-50 border-t border-gray-200 rounded-b-2xl">
        <span class="text-sm font-normal text-gray-700 mb-4 md:mb-0 block w-full md:inline md:w-auto">
            Menampilkan <span class="font-semibold text-gray-900">{{ $menus->firstItem() ?? 0 }}</span>-<span class="font-semibold text-gray-900">{{ $menus->lastItem() ?? 0 }}</span> 
            dari <span class="font-semibold text-gray-900">{{ $menus->total() }}</span> data
        </span>
        <div>
            {{ $menus->links() }}
        </div>
    </nav>
</div>

{{-- ============================================ --}}
{{-- MODAL TAMBAH --}}
{{-- ============================================ --}}
<div id="crud-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black/50 backdrop-blur-sm">
    <div class="relative p-4 w-full max-w-2xl max-h-full">
        <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 md:p-8 animate-fadeInDown">
            
            <div class="flex items-center justify-between pb-5 mb-5 border-b-2 border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg shadow-blue-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Tambah Menu Baru</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Isi form di bawah untuk menambahkan menu</p>
                    </div>
                </div>
                <button type="button" class="text-gray-400 bg-gray-50 hover:bg-gray-200 hover:text-gray-900 rounded-xl text-sm w-10 h-10 inline-flex justify-center items-center transition-all duration-200 hover:rotate-90" data-modal-hide="crud-modal">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>

            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-xs text-blue-700">
                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Maksimal <strong>5 menu</strong> per tanggal untuk setiap tipe.
                </p>
            </div>

            <form action="{{ route('admin.menus.store') }}" method="POST" class="space-y-6" enctype="multipart/form-data" id="create-form">
                @csrf
                {{-- HAPUS input hidden status --}}
                <div class="grid gap-4 grid-cols-1">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Nama Menu <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_menu" id="nama_menu"
                            class="bg-gray-50 border-2 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full px-4 py-3 transition-all duration-200" 
                            placeholder="Tulis Menu..." required>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Tanggal Menu <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal" id="add_tanggal"
                            class="bg-gray-50 border-2 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full px-4 py-3 transition-all duration-200" 
                            required>
                    </div>

                    <div class="grid gap-3 grid-cols-1 md:grid-cols-4">
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-700">BEST <span class="text-red-500">*</span></label>
                            <input type="number" name="harga_paket_kecil" id="price1"
                                class="bg-gray-50 border-2 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full px-4 py-3" 
                                placeholder="0" value="19000" required min="0">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-700">HAPPY <span class="text-red-500">*</span></label>
                            <input type="number" name="harga_paket_sedang" id="price2"
                                class="bg-gray-50 border-2 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full px-4 py-3" 
                                placeholder="0" value="22000" required min="0">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-700">JUMBO <span class="text-red-500">*</span></label>
                            <input type="number" name="harga_paket_besar" id="price3"
                                class="bg-gray-50 border-2 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full px-4 py-3" 
                                placeholder="0" value="22000" required min="0">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-700">SMART <span class="text-red-500">*</span></label>
                            <input type="number" name="harga_paket_khusus" id="price4"
                                class="bg-gray-50 border-2 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full px-4 py-3" 
                                placeholder="0" value="17000" required min="0">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tipe Menu <span class="text-red-500">*</span></label>
                        <select name="tipe_menu" required 
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="umum">Menu Umum</option>
                            <option value="kelas_1">Menu Khusus Kelas 1</option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Menu Umum: untuk kelas 2-12. Menu Khusus Kelas 1: hanya untuk kelas 1.</p>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Deskripsi Menu</label>
                        <textarea name="deskripsi" id="deskripsi" rows="3"
                            class="bg-gray-50 border-2 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full px-4 py-3 transition-all duration-200 resize-none"
                            placeholder="Tulis deskripsi produk di sini..."></textarea>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Gambar Menu</label>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="flex-1">
                                <div id="dropzone-area" 
                                    class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition-all duration-200 hover:border-blue-400"
                                    onclick="triggerFileInput()">
                                    <div id="upload-placeholder" class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                        </svg>
                                        <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Klik untuk upload</span> atau drag and drop</p>
                                        <p class="text-xs text-gray-400">PNG, JPG, JPEG (Max. 2MB)</p>
                                    </div>
                                    <input id="image-input" type="file" class="hidden" accept="image/*" name="gambar" onchange="handleImageUpload(event)">
                                </div>
                            </div>
                            <div id="preview-container" class="hidden flex-shrink-0 w-full sm:w-48 h-48">
                                <div class="relative w-full h-full rounded-xl overflow-hidden border-2 border-gray-200 bg-gray-50">
                                    <img id="image-preview" class="w-full h-full object-cover" alt="Preview">
                                    <button type="button" onclick="removeImage()" 
                                        class="absolute top-2 right-2 p-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg shadow-lg transition-all duration-200 hover:scale-110 z-10">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-5 border-t-2 border-gray-100">
                    <button data-modal-hide="crud-modal" type="button" 
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all duration-200">
                        Batal
                    </button>
                    <button type="submit" 
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-all duration-200 hover:scale-105 hover:shadow-lg hover:shadow-blue-500/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Simpan Menu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ============================================
// TRIGGER FILE INPUT
// ============================================
function triggerFileInput() {
    document.getElementById('image-input').click();
}

function triggerEditFileInput(id) {
    document.getElementById('image-input-edit-' + id).click();
}

// ============================================
// HANDLE IMAGE UPLOAD
// ============================================
function handleImageUpload(event) {
    const input = event.target;
    const file = input.files[0];
    
    if (!file) return;
    
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!validTypes.includes(file.type)) {
        alert('❌ Format file tidak didukung! Gunakan PNG, JPG, JPEG, GIF, atau WEBP.');
        input.value = '';
        return;
    }
    
    if (file.size > 2 * 1024 * 1024) {
        alert('❌ Ukuran file terlalu besar! Maksimal 2MB.');
        input.value = '';
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('image-preview').src = e.target.result;
        document.getElementById('preview-container').classList.remove('hidden');
        document.getElementById('upload-placeholder').classList.add('hidden');
        showNotification('✅ Gambar berhasil diupload!', 'success');
    };
    reader.readAsDataURL(file);
}

function handleEditImageUpload(event, id) {
    const input = event.target;
    const file = input.files[0];
    
    if (!file) return;
    
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!validTypes.includes(file.type)) {
        alert('❌ Format file tidak didukung!');
        input.value = '';
        return;
    }
    
    if (file.size > 2 * 1024 * 1024) {
        alert('❌ Ukuran file terlalu besar! Maksimal 2MB.');
        input.value = '';
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('image-preview-edit-' + id).src = e.target.result;
        document.getElementById('preview-container-edit-' + id).classList.remove('hidden');
        document.getElementById('upload-placeholder-edit-' + id).classList.add('hidden');
        showNotification('✅ Gambar berhasil diupload!', 'success');
    };
    reader.readAsDataURL(file);
}

// ============================================
// REMOVE IMAGE
// ============================================
function removeImage() {
    if (!confirm('Yakin ingin menghapus gambar ini?')) return;
    
    document.getElementById('image-input').value = '';
    document.getElementById('preview-container').classList.add('hidden');
    document.getElementById('image-preview').src = '';
    document.getElementById('upload-placeholder').classList.remove('hidden');
    showNotification('🗑️ Gambar telah dihapus', 'info');
}

function removeEditImage(id) {
    if (!confirm('Yakin ingin menghapus gambar ini?')) return;
    
    document.getElementById('image-input-edit-' + id).value = '';
    document.getElementById('preview-container-edit-' + id).classList.add('hidden');
    document.getElementById('image-preview-edit-' + id).src = '';
    document.getElementById('upload-placeholder-edit-' + id).classList.remove('hidden');
    showNotification('🗑️ Gambar telah dihapus', 'info');
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
// FORM SUBMIT HANDLER
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const createForm = document.getElementById('create-form');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Log untuk debug
            console.log('Form submitted');
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Tampilkan loading
            submitBtn.innerHTML = `
                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Menyimpan...
            `;
            submitBtn.disabled = true;
            
            // Log data yang akan dikirim
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                // Cek apakah response OK
                if (!response.ok) {
                    return response.json().then(err => {
                        console.error('Response error:', err);
                        throw err;
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    showNotification('✅ ' + data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    let errorMsg = '❌ Gagal menyimpan!';
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
                console.error('Fetch error:', error);
                // Tampilkan error detail
                let errorMsg = '❌ Terjadi kesalahan!';
                if (error.message) {
                    errorMsg += ' ' + error.message;
                }
                if (error.errors) {
                    errorMsg += ' ' + Object.values(error.errors).flat().join(' ');
                }
                showNotification(errorMsg, 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
            
            return false;
        });
    }
});

// ============================================
// DRAG AND DROP SUPPORT
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const dropzone = document.getElementById('dropzone-area');
    if (!dropzone) return;
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, preventDefaults, false);
    });
    
    dropzone.addEventListener('dragover', function() {
        this.classList.add('border-blue-500', 'bg-blue-50');
    });
    
    dropzone.addEventListener('dragleave', function() {
        this.classList.remove('border-blue-500', 'bg-blue-50');
    });
    
    dropzone.addEventListener('drop', function(e) {
        this.classList.remove('border-blue-500', 'bg-blue-50');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            const input = document.getElementById('image-input');
            input.files = files;
            const event = new Event('change', { bubbles: true });
            input.dispatchEvent(event);
            handleImageUpload({ target: input });
        }
    });
});

// ============================================
// CEK KUOTA MENU PER TANGGAL
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const tanggalInput = document.getElementById('add_tanggal');
    
    if (tanggalInput) {
        tanggalInput.addEventListener('change', function() {
            const tanggal = this.value;
            if (tanggal) {
                checkMenuQuota(tanggal);
            }
        });
    }
});

function checkMenuQuota(tanggal) {
    const url = "{{ route('admin.menus.check-quota') }}?tanggal=" + tanggal + "&tipe_menu=" + document.querySelector('select[name="tipe_menu"]').value;
    
    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        const quotaInfo = document.getElementById('menu-count-info');
        if (quotaInfo) {
            const sisa = 5 - data.count;
            quotaInfo.innerHTML = 'Sisa kuota tanggal ini: <strong>' + sisa + '</strong> menu';
            
            const submitBtn = document.querySelector('#crud-modal button[type="submit"]');
            if (submitBtn) {
                if (sisa <= 0) {
                    quotaInfo.innerHTML = '<span class="text-red-600">⚠️ Kuota penuh! Maksimal 5 menu per tipe.</span>';
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }
        }
    })
    .catch(error => console.error('Error checking quota:', error));
}
</script>

<style>
/* Animasi fadeInDown */
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.animate-fadeInDown {
    animation: fadeInDown 0.3s ease-out;
}

/* Modal backdrop */
.fixed.bg-black\/50 {
    background-color: rgba(0, 0, 0, 0.5);
}

/* Preview animation */
#preview-container,
[id^="preview-container-edit-"] {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Hover effect preview */
#preview-container img,
[id^="preview-container-edit-"] img {
    transition: transform 0.3s ease;
}

#preview-container:hover img,
[id^="preview-container-edit-"]:hover img {
    transform: scale(1.05);
}

/* Scrollbar modal */
#crud-modal::-webkit-scrollbar,
[id^="crud-modal-edit-"]::-webkit-scrollbar {
    width: 8px;
}

#crud-modal::-webkit-scrollbar-track,
[id^="crud-modal-edit-"]::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#crud-modal::-webkit-scrollbar-thumb,
[id^="crud-modal-edit-"]::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

#crud-modal::-webkit-scrollbar-thumb:hover,
[id^="crud-modal-edit-"]::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Dropzone drag style */
.dropzone-drag {
    border-color: #3b82f6 !important;
    background-color: #eff6ff !important;
}
</style>

@endsection