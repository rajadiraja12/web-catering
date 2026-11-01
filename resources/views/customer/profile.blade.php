{{-- resources/views/customer/profile.blade.php --}}

@extends('app')
@include('layout.customer')
@section('content')
@yield('nav')
<br><br><br><br>

<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            {{-- Header --}}
            <div class="bg-blue-800 from-blue-500 to-blue-600 px-6 py-8 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 rounded-full backdrop-blur-sm mb-3">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white">Profile Saya</h1>
                <p class="text-blue-100 text-sm mt-1">Kelola data diri Anda</p>
            </div>

            {{-- Form --}}
            <div class="p-6">
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                <form id="profileForm" action="{{ route('customer.profile.update') }}" method="POST">
                    @csrf

                    {{-- Nama --}}
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" 
                               value="{{ $user->name }}"
                               class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                               required>
                    </div>

                    {{-- Username (Readonly) --}}
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                        <input type="text" value="{{ $user->username }}"
                               class="w-full px-4 py-3 bg-gray-100 border-2 border-gray-200 rounded-xl cursor-not-allowed"
                               readonly disabled>
                        <p class="text-xs text-gray-400 mt-1">Username tidak dapat diubah</p>
                    </div>

                    {{-- Kelas - Angka --}}
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Kelas <span class="text-red-500">*</span></label>
                        <select name="kelas" id="kelas" 
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                required>
                            <option value="">Pilih Kelas</option>
                            @php
                                $kelasArray = explode('-', $user->kelas);
                                $kelasValue = $kelasArray[0] ?? '';
                            @endphp
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $kelasValue == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Nama Kelas (Jurusan) --}}
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Kelas / Jurusan <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_kelas" id="nama_kelas" 
                               value="{{ $kelasArray[1] ?? '' }}"
                               class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                               placeholder="Contoh: IPA 1, IPS 2, A, B"
                               required>
                    </div>

                    {{-- No HP --}}
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">No HP <span class="text-red-500">*</span></label>
                        <input type="text" name="no_hp" id="no_hp" 
                               value="{{ $user->no_hp }}"
                               class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                               required>
                    </div>

                    {{-- Alergi --}}
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Alergi</label>
                        <input type="text" name="alergi" id="alergi" 
                               value="{{ $user->alergi }}"
                               class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                               placeholder="Contoh: Kacang, Udang, Telur">
                        <p class="text-xs text-gray-400 mt-1">Isi jika ada alergi makanan</p>
                    </div>

                    {{-- Informasi Akun --}}
                    <div class="bg-gray-50 rounded-xl p-4 mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Informasi Akun</h4>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="text-gray-500">Role</span>
                                <p class="font-semibold">{{ ucfirst($user->role) }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Status</span>
                                <p class="font-semibold">
                                    @if($user->status == 'Active')
                                        <span class="text-green-600">Active</span>
                                    @else
                                        <span class="text-red-600">Non Aktif</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-span-2">
                                <span class="text-gray-500">Saldo</span>
                                <p class="font-semibold text-green-600 text-lg">Rp {{ number_format($user->saldo ?? 0, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol --}}
                    <div class="flex gap-3">
                        <button type="submit" 
                                class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-all hover:scale-[1.02]">
                            <span id="btnText">Simpan Perubahan</span>
                            <span id="btnLoading" class="hidden">
                                <svg class="inline w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Menyimpan...
                            </span>
                        </button>
                        <a href="{{ route('customer.dashboard') }}" 
                           class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-xl transition-all">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    const submitBtn = this.querySelector('button[type="submit"]');
    
    btnText.classList.add('hidden');
    btnLoading.classList.remove('hidden');
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    
    fetch('{{ route("customer.profile.update") }}', {
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
                window.location.reload();
            }, 1500);
        } else {
            let errorMsg = '❌ Gagal menyimpan!';
            if (data.errors) {
                errorMsg = Object.values(data.errors).flat().join('\n');
            }
            showNotification(errorMsg, 'error');
            btnText.classList.remove('hidden');
            btnLoading.classList.add('hidden');
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('❌ Terjadi kesalahan!', 'error');
        btnText.classList.remove('hidden');
        btnLoading.classList.add('hidden');
        submitBtn.disabled = false;
    });
});

// ============================================
// AUTO-FORMAT NO HP
// ============================================
document.getElementById('no_hp').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9+\-]/g, '');
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
</script>
@endsection