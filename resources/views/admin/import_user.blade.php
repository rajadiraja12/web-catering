{{-- resources/views/admin/import_user.blade.php --}}

@extends('app')
@include('layout.admin')
@section('content')
@yield('nav')
<br>
<br>
<br>
<br>
<br>

<div class="container mx-auto px-4">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 md:p-8">
        <div class="flex items-center justify-between pb-5 mb-5 border-b-2 border-gray-100">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg shadow-blue-500/30">
                    <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Import Data User</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Upload file Excel untuk menambah user customer</p>
                </div>
            </div>
            <a href="{{ route('admin.users') }}" class="text-gray-400 bg-gray-50 hover:bg-gray-200 hover:text-gray-900 rounded-xl text-sm w-10 h-10 inline-flex justify-center items-center transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Form Upload --}}
            <div class="bg-gray-50 rounded-xl p-6 border-2 border-dashed border-gray-300">
                <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Upload File Excel</label>
                        <div class="relative">
                            <input type="file" name="file" accept=".xlsx,.xls,.csv" required 
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Format: .xlsx, .xls, .csv (Max 5MB)</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-all hover:scale-105 hover:shadow-lg hover:shadow-blue-500/30">
                            Import Data
                        </button>
                        <a href="{{ route('admin.users.template') }}" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border-2 border-gray-200 hover:border-gray-300 rounded-xl transition-all">
                            Download Template
                        </a>
                    </div>
                </form>
            </div>

            {{-- Informasi --}}
            <div class="bg-blue-50 rounded-xl p-6 border border-blue-200">
                <h4 class="font-semibold text-gray-900 mb-3">Panduan Import</h4>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start gap-2">
                        <span class="text-blue-600 font-bold">1.</span>
                        <span>Download template Excel dengan tombol di samping</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-600 font-bold">2.</span>
                        <span>Isi data sesuai format template <strong>(jangan ubah header)</strong></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-600 font-bold">3.</span>
                        <span>Kolom wajib: <strong>username, name, kelas, no_hp</strong></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-600 font-bold">4.</span>
                        <span>Format kelas: <strong>kelas-nama_kelas</strong> (contoh: 1-Alpukat)</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-600 font-bold">5.</span>
                        <span>Role otomatis menjadi <strong>customer</strong> (jika tidak diisi)</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-600 font-bold">6.</span>
                        <span>Password default: <strong>password123</strong> (jika tidak diisi)</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-600 font-bold">7.</span>
                        <span>Status default: <strong>Active</strong> (jika tidak diisi)</span>
                    </li>
                </ul>

                <div class="mt-4 p-3 bg-white rounded-lg border border-blue-200">
                    <p class="text-xs text-blue-700">
                        <strong>Perhatian:</strong> Data dengan username yang sudah terdaftar akan dilewati secara otomatis.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.bg-white {
    animation: fadeIn 0.3s ease-out;
}

input[type="file"]::file-selector-button {
    transition: all 0.2s ease;
}
</style>

@endsection