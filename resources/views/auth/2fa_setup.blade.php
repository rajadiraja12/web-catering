{{-- resources/views/auth/2fa_setup.blade.php --}}

@extends('app')
@include('layout.utama')
@section('content')
@yield('nav')
<br>
<br>
<br>
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-green-600 to-teal-700 px-8 py-8 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 rounded-2xl backdrop-blur-sm mb-2">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-blu-800">Setup Google Authenticator</h1>
                <p class="text-green-100 text-sm mt-1">Tingkatkan keamanan akun Anda</p>
            </div>

            {{-- Form --}}
            <div class="px-8 py-8">
                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg">
                        @foreach($errors->all() as $error)
                            <p class="text-sm">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                {{-- Step 1: Scan QR Code --}}
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-800 mb-2">Step 1: Scan QR Code</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Scan QR Code ini dengan aplikasi Google Authenticator
                    </p>
                    <div class="bg-white p-4 rounded-xl border-2 border-gray-200 text-center">
                        @if($qrCodeUrl)
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCodeUrl) }}" 
                                 alt="QR Code" 
                                 class="mx-auto">
                        @else
                            <p class="text-gray-500">QR Code tidak tersedia</p>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-center">
                        Atau masukkan kode manual: <span class="font-mono font-bold">{{ $secret }}</span>
                    </p>
                </div>

                {{-- Step 2: Verifikasi --}}
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-800 mb-2">Step 2: Verifikasi</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Masukkan kode 6 digit dari Google Authenticator
                    </p>
                    
                    <form method="POST" action="{{ route('2fa.enable') }}">
                        @csrf
                        <input type="hidden" name="secret" value="{{ $secret }}">
                        
                        <div class="flex justify-center gap-3 mb-4">
                            @for($i = 0; $i < 6; $i++)
                                <input type="text" 
                                    maxlength="1" 
                                    class="code-input w-12 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500 transition-all"
                                    data-index="{{ $i }}"
                                    oninput="moveToNext(this)"
                                    onkeydown="handleBackspace(this, event)"
                                    onfocus="this.select()"
                                    required>
                            @endfor
                        </div>
                        <input type="hidden" id="code_combined" name="code" value="">

                        <button type="submit" 
                            class="w-full bg-green-600 from-green-600 to-teal-700 hover:from-green-700 hover:to-teal-800 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200 transform hover:scale-[1.02] hover:shadow-lg hover:shadow-green-500/30 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Aktifkan Google Authenticator
                        </button>
                    </form>
                </div>

                <div class="mt-6 text-center border-t border-gray-200 pt-6">
                    <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700">
                        ← Lewati (Tidak Aman)
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function moveToNext(current) {
    const inputs = document.querySelectorAll('.code-input');
    const index = parseInt(current.dataset.index);
    
    if (current.value.length >= 1 && index < 5) {
        inputs[index + 1].focus();
    }
    
    updateCombinedCode();
}

function handleBackspace(input, event) {
    if (event.key === 'Backspace' && input.value === '') {
        const inputs = document.querySelectorAll('.code-input');
        const index = parseInt(input.dataset.index);
        if (index > 0) {
            inputs[index - 1].focus();
            inputs[index - 1].value = '';
        }
        updateCombinedCode();
    }
}

function updateCombinedCode() {
    const inputs = document.querySelectorAll('.code-input');
    let code = '';
    inputs.forEach(input => {
        code += input.value;
    });
    document.getElementById('code_combined').value = code;
}

// Pastikan hanya angka yang bisa dimasukkan
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.code-input');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            updateCombinedCode();
            
            // Auto submit jika semua terisi
            const allFilled = Array.from(inputs).every(el => el.value.length === 1);
            if (allFilled) {
                document.querySelector('form').submit();
            }
        });
        
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const digits = paste.replace(/\D/g, '').slice(0, 6);
            
            if (digits.length > 0) {
                const inputs = document.querySelectorAll('.code-input');
                const digitsArray = digits.split('');
                digitsArray.forEach((digit, index) => {
                    if (index < inputs.length) {
                        inputs[index].value = digit;
                    }
                });
                updateCombinedCode();
                
                const lastIndex = Math.min(digitsArray.length, inputs.length) - 1;
                if (lastIndex >= 0 && lastIndex < inputs.length) {
                    inputs[lastIndex].focus();
                }
            }
        });
    });
});
</script>

<style>
.code-input {
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    width: 48px;
    height: 56px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.code-input:focus {
    border-color: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
    transform: scale(1.05);
}

.code-input.filled {
    border-color: #22c55e;
    background: #f0fdf4;
}
</style>
@endsection