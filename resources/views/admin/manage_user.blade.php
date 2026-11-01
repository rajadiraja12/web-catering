{{-- resources/views/admin/manage_user.blade.php --}}

@extends('app')
@include('layout.admin')
@section('content')
@yield('nav')
<br>
<br>
<br>
<br>
<br>

{{-- Header dan Search --}}
<div class="mb-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data User</h1>
        </div>
        {{-- Di bagian tombol, ganti dengan ini --}}
        <div class="flex items-center gap-3 mt-3 sm:mt-0">
            {{-- Tombol Tambah User --}}
            <button type="button" data-modal-target="add-user-modal" data-modal-toggle="add-user-modal" 
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah User
            </button>
            
            {{-- Tombol Import --}}
            <a href="{{ route('admin.users.import') }}" 
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Import
            </a>
            
            {{-- Tombol Export --}}
            <button type="button" onclick="exportUsers()" 
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export
            </button>
        </div>
    </div>

    {{-- Search --}}
    <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" id="searchUser" 
                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-sm text-gray-900 placeholder-gray-400"
                placeholder="Cari berdasarkan username, nama, kelas, atau no HP...">
        </div>
        <div class="flex items-center justify-between mt-3">
            <span class="text-xs text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>
                Hasil pencarian akan langsung muncul saat mengetik
            </span>
            <button onclick="resetSearch()" 
                class="text-sm text-blue-600 hover:text-blue-800 font-medium hover:underline">
                <i class="fas fa-undo mr-1"></i> Reset
            </button>
        </div>
    </div>
</div>

{{-- Tabel --}}
<div class="relative overflow-x-auto bg-blue-800 shadow-sm rounded-2xl border border-gray-200">
    <table class="w-full text-sm text-left rtl:text-right text-gray-700" id="userTable">
        <thead class="text-xs text-white uppercase bg-gradient-to-r from-blue-600 to-blue-800 border-b border-blue-900">
            <tr>
                <th scope="col" class="px-6 py-4 text-center w-16">No</th>
                <th scope="col" class="px-6 py-4 whitespace-nowrap">Username</th>
                <th scope="col" class="px-6 py-4 whitespace-nowrap">Nama</th>
                <th scope="col" class="px-6 py-4 whitespace-nowrap">Kelas</th>
                <th scope="col" class="px-6 py-4 whitespace-nowrap">No HP</th>
                <th scope="col" class="px-6 py-4">Alergi</th>
                <th scope="col" class="px-6 py-4 text-center">Role</th>
                <th scope="col" class="px-6 py-4 text-center">Status</th>
                <th scope="col" class="px-6 py-4 text-center whitespace-nowrap">Hutang</th>
                <th scope="col" class="px-6 py-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody id="userTableBody">
            @forelse($users as $index => $user)
            <tr class="{{ $loop->iteration % 2 == 0 ? 'bg-gray-50' : 'bg-white' }} border-b border-gray-200 hover:bg-blue-50 transition-colors duration-200">
                <td class="px-6 py-4 text-center font-medium text-gray-900">
                    {{ $loop->iteration + (($users->currentPage() - 1) * $users->perPage()) }}
                </td>
                <td class="px-6 py-4 font-mono text-sm font-medium text-gray-800">{{ $user->username }}</td>
                <td class="px-6 py-4 font-medium text-gray-800">{{ $user->name }}</td>
                <td class="px-6 py-4">
                    @if($user->role == 'customer')
                    <span class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                        {{ $user->kelas }}
                    </span>
                    @else
                    <span class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                        --
                    </span>
                    @endif
                </td>
                <td class="px-6 py-4 font-mono text-sm">{{ $user->no_hp }}</td>
                <td class="px-6 py-4">
                    @if($user->alergi && $user->alergi != '-')
                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">{{ $user->alergi }}</span>
                    @else
                        <span class="text-gray-400 text-sm">-</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-center">
                    <span class="px-2.5 py-1 {{ $user->role == 'admin' ? 'bg-red-100 text-red-700' : ($user->role == 'kasir' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700') }} rounded-full text-xs font-medium capitalize">
                        {{ $user->role }}
                    </span>
                </td>
                <td class="px-6 py-4 text-center">
                    @if($user->status == 'Active')
                        <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-700 px-2.5 py-0.5 rounded-full text-xs font-medium">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                            Active
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-700 px-2.5 py-0.5 rounded-full text-xs font-medium">
                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                            Non Aktif
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 text-center">
                    <div class="text-sm">
                        @if($user->role == 'customer')
                        <span class="font-medium text-gray-700">
                            Rp {{ number_format($user->hutang_limit ?? 0, 0, ',', '.') }}
                        </span>
                        <br>
                        <span class="text-xs text-gray-500">
                            Terpakai: Rp {{ number_format($user->hutang_used ?? 0, 0, ',', '.') }}
                        </span>
                        <br>
                        <span class="text-xs {{ ($user->hutang_limit - $user->hutang_used) > 0 ? 'text-green-600' : 'text-red-600' }}">
                            Sisa: Rp {{ number_format(($user->hutang_limit ?? 0) - ($user->hutang_used ?? 0), 0, ',', '.') }}
                        </span>
                        @else
                        <span class="font-medium text-gray-700">
                            --
                        </span>
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-2 flex-wrap">
                        {{-- Tampilkan aksi hanya untuk role customer --}}
                        @if($user->role == 'customer')
                            {{-- Tombol Edit --}}
                            <button onclick="editUser({{ $user->id }})" 
                                class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium text-sm rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </button>

                            <button onclick="editHutang({{ $user->id }})" 
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white font-medium text-sm rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                Hutang
                            </button>
                            
                            @if($user->status == 'Active')
                                <button onclick="toggleStatus({{ $user->id }})" 
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium text-sm rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"/>
                                    </svg>
                                    Nonaktifkan
                                </button>
                            @else
                                <button onclick="toggleStatus({{ $user->id }})" 
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Aktifkan
                                </button>
                            @endif
                            
                            <button onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')" 
                                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium text-sm rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Hapus
                            </button>
                        @elseif($user->role == 'kasir')
                            <button onclick="editUserAdmin({{ $user->id }})" 
                                class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium text-sm rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </button>
                            @if($user->status == 'Active')
                                <button onclick="toggleStatus({{ $user->id }})" 
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium text-sm rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"/>
                                    </svg>
                                    Nonaktifkan
                                </button>
                            @else
                                <button onclick="toggleStatus({{ $user->id }})" 
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Aktifkan
                                </button>
                            @endif
                        @else
                            {{-- Untuk admin dan kasir, hanya tombol edit --}}
                            <button onclick="editUserAdmin({{ $user->id }})" 
                                class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium text-sm rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm font-medium">Tidak ada data user</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between p-4 bg-gray-50 border-t border-gray-200 rounded-b-2xl">
        <span class="text-sm font-normal text-gray-700 mb-4 md:mb-0 block w-full md:inline md:w-auto">
            Menampilkan <span class="font-semibold text-gray-900">{{ $users->firstItem() ?? 0 }}</span>-<span class="font-semibold text-gray-900">{{ $users->lastItem() ?? 0 }}</span> 
            dari <span class="font-semibold text-gray-900">{{ $users->total() }}</span> user
        </span>
        <div>
            {{ $users->links() }}
        </div>
    </nav>
</div>

{{-- ============================================ --}}
{{-- MODAL TAMBAH USER --}}
{{-- ============================================ --}}
<div id="add-user-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black/50 backdrop-blur-sm">
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
                        <h3 class="text-xl font-bold text-gray-900">Tambah User Baru</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Isi form di bawah untuk menambahkan user</p>
                    </div>
                </div>
                <button type="button" data-modal-hide="add-user-modal" class="text-gray-400 bg-gray-50 hover:bg-gray-200 hover:text-gray-900 rounded-xl text-sm w-10 h-10 inline-flex justify-center items-center transition-all duration-200 hover:rotate-90">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form action="{{ route('admin.users.store') }}" method="POST" id="form-add-user">
                @csrf
                <div class="grid gap-4 grid-cols-1 md:grid-cols-2">
                    {{-- Nama Lengkap --}}
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="Masukkan nama lengkap">
                    </div>
                    
                    {{-- NISN / Username --}}
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" required class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="Masukkan NISN">
                    </div>

                    {{-- Kelas - Select Dropdown (sama seperti register) --}}
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Kelas <span class="text-red-500">*</span></label>
                        <select name="kelas" id="add_kelas" required class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
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
                        <input type="text" name="nama_kelas" id="add_nama_kelas" required class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="Contoh: Alpukat, IPA 1, IPS 2">
                    </div>

                    {{-- No HP --}}
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">No HP <span class="text-red-500">*</span></label>
                        <input type="text" name="no_hp" required class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="Contoh: 08123456789">
                    </div>

                    {{-- Alergi --}}
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Alergi</label>
                        <input type="text" name="alergi" class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="Contoh: Kacang, Udang">
                    </div>

                    {{-- Password --}}
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required minlength="8" class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="Min. 8 karakter">
                    </div>

                    {{-- Role --}}
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Role <span class="text-red-500">*</span></label>
                        <select name="role" required class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="customer">Customer</option>
                            <option value="kasir">Kasir</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Status <span class="text-red-500">*</span></label>
                        <select name="status" required class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="Active">Active</option>
                            <option value="Non Aktif">Non Aktif</option>
                        </select>
                    </div>
                </div>

                {{-- Informasi Gabungan Kelas (Preview) --}}
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
                    <button data-modal-hide="add-user-modal" type="button" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all">Batal</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-xl transition-all hover:scale-105 hover:shadow-lg hover:shadow-green-500/30">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- MODAL EDIT CUSTOMER --}}
{{-- ============================================ --}}
<div id="edit-user-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black/50 backdrop-blur-sm">
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
            
            <form id="form-edit-user" method="POST">
                @csrf
                @method('PUT')
                <div class="grid gap-4 grid-cols-1 md:grid-cols-2">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="edit_name" required class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" id="edit_username" required class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Kelas <span class="text-red-500">*</span></label>
                        <select name="kelas" id="edit_kelas" required class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
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
                        <input type="text" name="nama_kelas" id="edit_nama_kelas" required class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="Contoh: IPA 1, IPS 2">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">No HP <span class="text-red-500">*</span></label>
                        <input type="text" name="no_hp" id="edit_no_hp" required class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Alergi</label>
                        <input type="text" name="alergi" id="edit_alergi" class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="Contoh: Kacang, Udang">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Password <span class="text-gray-400 text-xs">(Kosongkan jika tidak diubah)</span></label>
                        <input type="password" name="password" id="edit_password" minlength="8" class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Role <span class="text-red-500">*</span></label>
                        <select name="role" id="edit_role" required class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="customer">Customer</option>
                            <option value="kasir">Kasir</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="edit_status" required class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="Active">Active</option>
                            <option value="Non Aktif">Non Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-5 mt-5 border-t-2 border-gray-100">
                    <button type="button" onclick="closeEditModal()" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all">Batal</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-yellow-500 hover:bg-yellow-600 rounded-xl transition-all hover:scale-105 hover:shadow-lg hover:shadow-yellow-500/30">Update Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- MODAL EDIT ADMIN / KASIR --}}
{{-- ============================================ --}}
<div id="edit-admin-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black/50 backdrop-blur-sm">
    <div class="relative p-4 w-full max-w-2xl max-h-full">
        <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 md:p-8">
            
            <div class="flex items-center justify-between pb-5 mb-5 border-b-2 border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg shadow-blue-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Edit Admin / Kasir</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Edit data admin/kasir yang dipilih</p>
                    </div>
                </div>
                <button type="button" onclick="closeAdminModal()" class="text-gray-400 bg-gray-50 hover:bg-gray-200 hover:text-gray-900 rounded-xl text-sm w-10 h-10 inline-flex justify-center items-center transition-all duration-200 hover:rotate-90">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="form-edit-admin" method="POST">
                @csrf
                @method('PUT')
                <div class="grid gap-4 grid-cols-1 md:grid-cols-2">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="admin_edit_name" required class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">NIP / Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" id="admin_edit_username" required class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">No HP <span class="text-red-500">*</span></label>
                        <input type="text" name="no_hp" id="admin_edit_no_hp" required class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <input type="hidden" name="kelas" value="1">
                    <input type="hidden" name="nama_kelas" value="admin">
                    <div>
                        <input type="hidden" name="email" id="admin_edit_email" class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="email@example.com">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Password <span class="text-gray-400 text-xs">(Kosongkan jika tidak diubah)</span></label>
                        <input type="password" name="password" id="admin_edit_password" minlength="8" class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Role <span class="text-red-500">*</span></label>
                        <select name="role" id="admin_edit_role" required class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="kasir">Kasir</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="admin_edit_status" required class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="Active">Active</option>
                            <option value="Non Aktif">Non Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-5 mt-5 border-t-2 border-gray-100">
                    <button type="button" onclick="closeAdminModal()" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all">Batal</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-all hover:scale-105 hover:shadow-lg hover:shadow-blue-500/30">Update Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- MODAL EDIT HUTANG --}}
{{-- ============================================ --}}
<div id="edit-hutang-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black/50 backdrop-blur-sm">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 md:p-8">
            
            <div class="flex items-center justify-between pb-5 mb-5 border-b-2 border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg shadow-purple-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Edit Limit Hutang</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Kelola limit hutang user</p>
                    </div>
                </div>
                <button type="button" onclick="closeHutangModal()" class="text-gray-400 bg-gray-50 hover:bg-gray-200 hover:text-gray-900 rounded-xl text-sm w-10 h-10 inline-flex justify-center items-center transition-all duration-200 hover:rotate-90">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="form-edit-hutang" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">User</label>
                    <p class="text-gray-900 font-medium" id="hutang_user_name">-</p>
                    <p class="text-xs text-gray-500" id="hutang_user_username">-</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Hutang Saat Ini</label>
                    <div class="grid grid-cols-3 gap-2 text-sm bg-gray-50 rounded-xl p-3">
                        <div>
                            <span class="text-gray-500">Limit</span>
                            <p class="font-semibold" id="current_limit">Rp 0</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Terpakai</span>
                            <p class="font-semibold text-orange-600" id="current_used">Rp 0</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Sisa</span>
                            <p class="font-semibold text-green-600" id="current_available">Rp 0</p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Limit Hutang Baru <span class="text-red-500">*</span></label>
                    <input type="number" name="hutang_limit" id="hutang_limit_input" 
                           class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                           min="0"
                           step="50000"
                           required>
                    <p class="text-xs text-gray-400 mt-1">Minimal Rp 0, kelipatan Rp 50.000</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-5 mt-5 border-t-2 border-gray-100">
                    <button type="button" onclick="closeHutangModal()" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all">Batal</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-purple-600 hover:bg-purple-700 rounded-xl transition-all hover:scale-105 hover:shadow-lg hover:shadow-purple-500/30">
                        Update Hutang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- JAVASCRIPT --}}
{{-- ============================================ --}}
<script>
// ============================================
// TOGGLE CUSTOMER FIELDS (Add User)
// ============================================
function toggleCustomerFields() {
    const role = document.getElementById('add_role').value;
    const customerFields = document.querySelector('.customer-fields');
    
    if (role === 'customer') {
        customerFields.style.display = 'grid';
        customerFields.style.gridTemplateColumns = '1fr 1fr';
        customerFields.style.gap = '1rem';
        // Set required
        customerFields.querySelectorAll('select, input').forEach(el => el.required = true);
    } else {
        customerFields.style.display = 'none';
        // Remove required
        customerFields.querySelectorAll('select, input').forEach(el => el.required = false);
    }
}

// ============================================
// EDIT CUSTOMER
// ============================================
function editUser(userId) {
    const url = "{{ route('admin.users.edit', ['id' => ':id']) }}".replace(':id', userId);
    
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
            const user = data.data;
            
            document.getElementById('edit_name').value = user.name;
            document.getElementById('edit_username').value = user.username;
            
            if (user.kelas && user.kelas.includes('-')) {
                const kelasParts = user.kelas.split('-');
                document.getElementById('edit_kelas').value = kelasParts[0] || '';
                document.getElementById('edit_nama_kelas').value = kelasParts.slice(1).join('-') || '';
            } else {
                document.getElementById('edit_kelas').value = user.kelas || '';
                document.getElementById('edit_nama_kelas').value = '';
            }
            
            document.getElementById('edit_no_hp').value = user.no_hp;
            document.getElementById('edit_alergi').value = user.alergi || '';
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_status').value = user.status;
            document.getElementById('edit_password').value = '';
            
            const form = document.getElementById('form-edit-user');
            const actionUrl = "{{ route('admin.users.update', ['id' => ':id']) }}".replace(':id', userId);
            form.action = actionUrl;
            
            document.getElementById('edit-user-modal').classList.remove('hidden');
            document.getElementById('edit-user-modal').style.display = 'flex';
            
            const notif = document.getElementById('custom-notification');
            if (notif) notif.remove();
        } else {
            showNotification('❌ Gagal memuat data customer', 'error');
        }
    })
    .catch(error => {
        showNotification('❌ Terjadi kesalahan: ' + error.message, 'error');
        console.error('Error:', error);
    });
}

// ============================================
// EDIT ADMIN / KASIR
// ============================================
function editUserAdmin(userId) {
    const url = "{{ route('admin.users.edit', ['id' => ':id']) }}".replace(':id', userId);
    
    showNotification('⏳ Memuat data...', 'info');
    
    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const user = data.data;
            
            document.getElementById('admin_edit_name').value = user.name;
            document.getElementById('admin_edit_username').value = user.username;
            document.getElementById('admin_edit_no_hp').value = user.no_hp;
            document.getElementById('admin_edit_email').value = user.email || '';
            document.getElementById('admin_edit_role').value = user.role;
            document.getElementById('admin_edit_status').value = user.status;
            document.getElementById('admin_edit_password').value = '';
            
            const form = document.getElementById('form-edit-admin');
            const actionUrl = "{{ route('admin.users.update', ['id' => ':id']) }}".replace(':id', userId);
            form.action = actionUrl;
            
            document.getElementById('edit-admin-modal').classList.remove('hidden');
            document.getElementById('edit-admin-modal').style.display = 'flex';
            
            const notif = document.getElementById('custom-notification');
            if (notif) notif.remove();
        } else {
            showNotification('❌ Gagal memuat data', 'error');
        }
    })
    .catch(error => {
        showNotification('❌ Terjadi kesalahan: ' + error.message, 'error');
        console.error('Error:', error);
    });
}

// ============================================
// CLOSE EDIT MODAL
// ============================================
function closeEditModal() {
    document.getElementById('edit-user-modal').classList.add('hidden');
    document.getElementById('edit-user-modal').style.display = 'none';
}

function closeAdminModal() {
    document.getElementById('edit-admin-modal').classList.add('hidden');
    document.getElementById('edit-admin-modal').style.display = 'none';
}

// ============================================
// EDIT FORM SUBMIT (CUSTOMER)
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-edit-user');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = `
                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Mengupdate...
            `;
            submitBtn.disabled = true;
            
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
                    let errorMsg = '❌ Gagal mengupdate!';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('\n');
                    }
                    showNotification(errorMsg, 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                showNotification('❌ Terjadi kesalahan: ' + error.message, 'error');
                console.error('Error:', error);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});

// ============================================
// EDIT FORM SUBMIT (ADMIN/KASIR)
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-edit-admin');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = `
                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Mengupdate...
            `;
            submitBtn.disabled = true;
            
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
                        closeAdminModal();
                        location.reload();
                    }, 1000);
                } else {
                    let errorMsg = '❌ Gagal mengupdate!';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('\n');
                    }
                    showNotification(errorMsg, 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                showNotification('❌ Terjadi kesalahan: ' + error.message, 'error');
                console.error('Error:', error);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});

// ============================================
// TOGGLE STATUS
// ============================================
function toggleStatus(userId) {
    if (!confirm('Yakin ingin mengubah status user ini?')) {
        return;
    }
    
    const url = "{{ route('admin.users.toggle-status', ['id' => ':id']) }}".replace(':id', userId);
    
    fetch(url, {
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
// DELETE USER
// ============================================
function deleteUser(userId, userName) {
    if (!confirm('Yakin ingin menghapus user "' + userName + '"?')) {
        return;
    }
    
    const url = "{{ route('admin.users.delete', ['id' => ':id']) }}".replace(':id', userId);
    
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
// EDIT HUTANG
// ============================================
let hutangUserId = null;

function editHutang(userId) {
    hutangUserId = userId;
    
    const url = "{{ route('admin.users.edit', ['id' => ':id']) }}".replace(':id', userId);
    
    showNotification('⏳ Memuat data hutang...', 'info');
    
    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const user = data.data;
            
            document.getElementById('hutang_user_name').textContent = user.name;
            document.getElementById('hutang_user_username').textContent = '@' + user.username;
            
            document.getElementById('current_limit').textContent = 'Rp ' + numberFormat(user.hutang_limit || 0);
            document.getElementById('current_used').textContent = 'Rp ' + numberFormat(user.hutang_used || 0);
            document.getElementById('current_available').textContent = 'Rp ' + numberFormat((user.hutang_limit || 0) - (user.hutang_used || 0));
            
            document.getElementById('hutang_limit_input').value = user.hutang_limit || 0;
            
            const form = document.getElementById('form-edit-hutang');
            form.action = '/admin/users/' + userId + '/hutang-limit';
            
            document.getElementById('edit-hutang-modal').classList.remove('hidden');
            document.getElementById('edit-hutang-modal').style.display = 'flex';
            
            const notif = document.getElementById('custom-notification');
            if (notif) notif.remove();
        } else {
            showNotification('❌ Gagal memuat data hutang', 'error');
        }
    })
    .catch(error => {
        showNotification('❌ Terjadi kesalahan: ' + error.message, 'error');
        console.error('Error:', error);
    });
}

// ============================================
// CLOSE HUTANG MODAL
// ============================================
function closeHutangModal() {
    document.getElementById('edit-hutang-modal').classList.add('hidden');
    document.getElementById('edit-hutang-modal').style.display = 'none';
    hutangUserId = null;
}

// ============================================
// HUTANG FORM SUBMIT
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-edit-hutang');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const hutangLimit = document.getElementById('hutang_limit_input').value;
            
            if (hutangLimit < 0) {
                showNotification('❌ Limit hutang tidak boleh negatif', 'error');
                return;
            }
            
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
                        closeHutangModal();
                        location.reload();
                    }, 1000);
                } else {
                    let errorMsg = '❌ Gagal mengupdate hutang!';
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
                console.error('Error:', error);
                showNotification('❌ Terjadi kesalahan: ' + error.message, 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});

// ============================================
// NUMBER FORMAT HELPER
// ============================================
function numberFormat(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}

// ============================================
// CLOSE MODAL WITH ESCAPE
// ============================================
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeEditModal();
        closeAdminModal();
        closeHutangModal();
    }
});

// ============================================
// CLICK OUTSIDE MODAL TO CLOSE
// ============================================
document.addEventListener('click', function(event) {
    const editModal = document.getElementById('edit-user-modal');
    if (editModal && !editModal.classList.contains('hidden')) {
        if (event.target === editModal) {
            closeEditModal();
        }
    }
    
    const adminModal = document.getElementById('edit-admin-modal');
    if (adminModal && !adminModal.classList.contains('hidden')) {
        if (event.target === adminModal) {
            closeAdminModal();
        }
    }
    
    const hutangModal = document.getElementById('edit-hutang-modal');
    if (hutangModal && !hutangModal.classList.contains('hidden')) {
        if (event.target === hutangModal) {
            closeHutangModal();
        }
    }
});

// ============================================
// SEARCH USER
// ============================================
let searchTimeout;
document.getElementById('searchUser').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const search = this.value;
        const url = "{{ route('admin.users') }}?search=" + encodeURIComponent(search);
        window.location.href = url;
    }, 500);
});

// ============================================
// RESET SEARCH
// ============================================
function resetSearch() {
    document.getElementById('searchUser').value = '';
    window.location.href = "{{ route('admin.users') }}";
}

// ============================================
// EXPORT USERS
// ============================================
function exportUsers() {
    window.location.href = "{{ route('admin.users.export') }}";
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

// ============================================
// PREVIEW GABUNGAN KELAS (Modal Tambah)
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
// FORM SUBMIT - TAMBAH USER
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const addForm = document.getElementById('form-add-user');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = `
                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Menyimpan...
            `;
            submitBtn.disabled = true;
            
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
                    setTimeout(() => location.reload(), 1000);
                } else {
                    let errorMsg = '❌ Gagal menyimpan!';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('\n');
                    }
                    showNotification(errorMsg, 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                showNotification('❌ Terjadi kesalahan!', 'error');
                console.error('Error:', error);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});
</script>

<style>
/* Custom scrollbar */
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

#userTableBody tr {
    animation: fadeIn 0.3s ease-out;
}

input:focus {
    outline: none;
}

.transition-all {
    transition: all 0.2s ease;
}

button:hover {
    transform: translateY(-1px);
}

/* Modal backdrop */
.fixed.bg-black\/50 {
    background-color: rgba(0, 0, 0, 0.5);
}

/* Modal animation */
#edit-user-modal .relative,
#add-user-modal .relative,
#edit-admin-modal .relative {
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

@media (max-width: 768px) {
    .px-6 {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
    .px-8 {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
}
</style>

@endsection