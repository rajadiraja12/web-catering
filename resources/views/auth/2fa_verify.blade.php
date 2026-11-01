{{-- resources/views/auth/2fa_verify.blade.php --}}
@extends('app')
@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
            {{-- Header --}}
            <div class="bg-gray-800 px-8 py-6 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-2xl backdrop-blur-sm mb-2">
                    <i class="fas fa-shield-alt text-white text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-white">Verifikasi 2FA</h1>
                <p class="text-blue-100 text-sm mt-1">Masukkan kode dari Google Authenticator</p>
            </div>

            {{-- Form --}}
            <div class="px-8 py-8">
                @if(session('error'))
                    <div class="mb-4 p-3 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg">
                        <p class="text-sm">{{ session('error') }}</p>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-3 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg">
                        @foreach($errors->all() as $error)
                            <p class="text-sm">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('2fa.verify') }}" id="verifyForm">
                    @csrf

                    <div class="mb-6">
                        <label for="code" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-key mr-2"></i>
                            Kode Verifikasi
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-shield-alt text-gray-400"></i>
                            </div>
                            <input id="code" type="text" 
                                class="w-full pl-10 pr-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('code') border-red-500 @enderror"
                                name="code" placeholder="Masukkan 6 digit kode" 
                                required autofocus maxlength="6" 
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6)">
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Buka aplikasi Google Authenticator dan masukkan kode 6 digit
                        </p>
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Hidden untuk debugging --}}
                    <input type="hidden" name="2fa_verify" value="true">

                    <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200 transform hover:scale-[1.02] hover:shadow-lg flex items-center justify-center gap-2">
                        <i class="fas fa-check-circle"></i>
                        Verifikasi
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-blue-600">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Kembali ke Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('code');
    
    // Auto focus
    codeInput.focus();

    // Auto submit jika kode lengkap 6 digit (opsional)
    codeInput.addEventListener('input', function() {
        if (this.value.length === 6) {
            // Optional: auto submit
            // document.getElementById('verifyForm').submit();
        }
    });
});
</script>
@endsection