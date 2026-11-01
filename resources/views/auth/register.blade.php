{{-- resources/views/auth/register.blade.php --}}
@extends('app')
@include('layout.utama')
@section('content')
@yield('nav')
<br>
<br>
<br>
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 p-4">
    <div class="w-full max-w-md">
        {{-- Card Register --}}
        <div class="bg-blue-800 rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
            {{-- Header --}}
            <div class="bg-gray-800 from-blue-600 to-indigo-700 px-8 py-8 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 rounded-2xl backdrop-blur-sm mb-2">
                    <img src="{{ asset('image/logo.png') }}" alt="">
                </div>
                <h1 class="text-2xl font-bold text-white">Daftar Akun</h1>
                <p class="text-blue-100 text-sm mt-1">Buat akun baru untuk memulai</p>
            </div>

            {{-- Form --}}
            <div class="px-8 py-8">
                {{-- TAMPILKAN ERROR --}}
                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg">
                        <p class="text-sm font-medium">{{ session('error') }}</p>
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-lg">
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    {{-- Nama Lengkap --}}
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-semibold text-white mb-2">
                            <i class="fas fa-user mr-2 text-blue-400"></i>
                            Nama Lengkap (Tidak Boleh di singkat dan harus lengkap!)
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input id="name" type="text" 
                                class="w-full pl-10 pr-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('name') border-red-500 @enderror"
                                name="name" value="{{ old('name') }}" 
                                placeholder="Masukkan nama lengkap" required autofocus>
                        </div>
                        @error('name')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Username/NISN --}}
                    <div class="mb-4">
                        <label for="username" class="block text-sm font-semibold text-white mb-2">
                            <i class="fas fa-user-tag mr-2 text-blue-400"></i>
                            Username
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user-tag text-gray-400"></i>
                            </div>
                            <input id="username" type="text" 
                                class="w-full pl-10 pr-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('username') border-red-500 @enderror"
                                name="username" value="{{ old('username') }}" 
                                placeholder="Masukkan NISN" required>
                        </div>
                        @error('username')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Grid untuk Kelas dan Nama Kelas --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        {{-- Kelas - Select Dropdown --}}
                        <div>
                            <label for="kelas" class="block text-sm font-semibold text-white mb-2">
                                <i class="fas fa-graduation-cap mr-2 text-blue-400"></i>
                                Kelas
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-graduation-cap text-gray-400"></i>
                                </div>
                                <select id="kelas" name="kelas" 
                                    class="w-full pl-10 pr-10 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 appearance-none cursor-pointer @error('kelas') border-red-500 @enderror"
                                    required>
                                    <option value="" disabled selected>Pilih Kelas</option>
                                    <option value="1" {{ old('kelas') == '1' ? 'selected' : '' }}>1</option>
                                    <option value="2" {{ old('kelas') == '2' ? 'selected' : '' }}>2</option>
                                    <option value="3" {{ old('kelas') == '3' ? 'selected' : '' }}>3</option>
                                    <option value="4" {{ old('kelas') == '4' ? 'selected' : '' }}>4</option>
                                    <option value="5" {{ old('kelas') == '5' ? 'selected' : '' }}>5</option>
                                    <option value="6" {{ old('kelas') == '6' ? 'selected' : '' }}>6</option>
                                    <option value="7" {{ old('kelas') == '7' ? 'selected' : '' }}>7</option>
                                    <option value="8" {{ old('kelas') == '8' ? 'selected' : '' }}>8</option>
                                    <option value="9" {{ old('kelas') == '9' ? 'selected' : '' }}>9</option>
                                    <option value="10" {{ old('kelas') == '10' ? 'selected' : '' }}>10</option>
                                    <option value="11" {{ old('kelas') == '11' ? 'selected' : '' }}>11</option>
                                    <option value="12" {{ old('kelas') == '12' ? 'selected' : '' }}>12</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                            @error('kelas')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        {{-- Nama Kelas --}}
                        <div>
                            <label for="nama_kelas" class="block text-sm font-semibold text-white mb-2">
                                <i class="fas fa-building mr-2 text-blue-400"></i>
                                Nama Kelas
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-building text-gray-400"></i>
                                </div>
                                <input id="nama_kelas" type="text"
                                    class="w-full pl-10 pr-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('nama_kelas') border-red-500 @enderror"
                                    name="nama_kelas" value="{{ old('nama_kelas') }}" 
                                    placeholder="Nama Kelas...." required>
                            </div>
                            @error('nama_kelas')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- No HP --}}
                    <div class="mb-4">
                        <label for="no_hp" class="block text-sm font-semibold text-white mb-2">
                            <i class="fas fa-phone mr-2 text-blue-400"></i>
                            No HP
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-phone text-gray-400"></i>
                            </div>
                            <input id="no_hp" type="text"
                                class="w-full pl-10 pr-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('no_hp') border-red-500 @enderror"
                                name="no_hp" value="{{ old('no_hp') }}" 
                                placeholder="Masukkan No HP (contoh: 08123456789)" required>
                        </div>
                        @error('no_hp')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    {{-- Alergi (Opsional) --}}
                    <div class="mb-4">
                        <label for="alergi" class="block text-sm font-semibold text-white mb-2">
                            <i class="fas fa-allergies mr-2 text-blue-400"></i>
                            Alergi <span class="text-xs text-gray-400">(Opsional)</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-allergies text-gray-400"></i>
                            </div>
                            <input id="alergi" type="text" 
                                class="w-full pl-10 pr-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('alergi') border-red-500 @enderror"
                                name="alergi" value="{{ old('alergi') }}" 
                                placeholder="Masukkan alergi (jika ada)">
                        </div>
                        @error('alergi')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    {{-- Password --}}
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-semibold text-white mb-2">
                            <i class="fas fa-lock mr-2 text-blue-400"></i>
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input id="password" type="password" 
                                class="w-full pl-10 pr-12 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('password') border-red-500 @enderror"
                                name="password" placeholder="Masukkan password (min. 8 karakter)" required>
                            <button type="button" onclick="togglePassword()" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i id="password-icon" class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div class="mb-5">
                        <label for="password_confirmation" class="block text-sm font-semibold text-white mb-2">
                            <i class="fas fa-check-circle mr-2 text-blue-400"></i>
                            Konfirmasi Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-check-circle text-gray-400"></i>
                            </div>
                            <input id="password_confirmation" type="password" 
                                class="w-full pl-10 pr-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('password_confirmation') border-red-500 @enderror"
                                name="password_confirmation" placeholder="Konfirmasi password" required>
                        </div>
                        @error('password_confirmation')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Terms & Conditions --}}
                    <div class="mb-6">
                        <label class="flex items-start cursor-pointer">
                            <input type="checkbox" name="terms" id="terms" 
                                class="w-4 h-4 mt-1 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                required>
                            <span class="ml-2 text-sm text-white">
                                Saya setuju dengan 
                                <a href="{{ route('terms') }}" target="_blank" class="text-blue-300 hover:text-blue-100 underline">Syarat & Ketentuan</a> 
                            </span>
                        </label>
                        @error('terms')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Register Button --}}
                    <button type="submit" 
                        class="w-full bg-gray-800 from-blue-600 to-indigo-700 hover:from-gray-700 hover:to-indigo-800 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200 transform hover:scale-[1.02] hover:shadow-lg hover:shadow-blue-500/30 flex items-center justify-center gap-2">
                        <i class="fas fa-user-plus"></i>
                        Daftar
                    </button>
                </form>

                {{-- Divider --}}
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-600"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-blue-800 text-gray-300">atau</span>
                    </div>
                </div>

                {{-- Login Link --}}
                <div class="text-center">
                    <p class="text-sm text-white">
                        Sudah punya akun? 
                        <a href="{{ route('login') }}" class="text-blue-300 hover:text-blue-100 font-semibold hover:underline">
                            Login
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const icon = document.getElementById('password-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Validasi form sebelum submit
document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const passwordConfirmation = document.getElementById('password_confirmation').value;
    const terms = document.getElementById('terms');
    
    if (password.length < 8) {
        e.preventDefault();
        alert('Password minimal 8 karakter!');
        return false;
    }
    
    if (password !== passwordConfirmation) {
        e.preventDefault();
        alert('Password dan konfirmasi password tidak cocok!');
        return false;
    }
    
    if (!terms.checked) {
        e.preventDefault();
        alert('Silakan setujui Syarat & Ketentuan terlebih dahulu!');
        return false;
    }
});

// Auto dismiss alert
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.bg-red-100, .bg-green-100');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 500);
        }, 5000);
    });
});
</script>

<style>
/* Animasi fade in */
.w-full.max-w-md {
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Hover effect untuk card */
.bg-blue-800.rounded-2xl {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.bg-blue-800.rounded-2xl:hover {
    transform: translateY(-2px);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

/* Input focus effect */
input:focus, select:focus {
    outline: none;
}

/* Custom select arrow */
select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

/* Placeholder color */
::placeholder {
    color: #9ca3af;
    opacity: 1;
}

/* Input autofill style */
input:-webkit-autofill {
    -webkit-box-shadow: 0 0 0 30px #f9fafb inset !important;
    -webkit-text-fill-color: #111827 !important;
}

/* Grid responsive untuk mobile */
@media (max-width: 768px) {
    .grid-cols-1.md\:grid-cols-2 {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
}
</style>
@endsection