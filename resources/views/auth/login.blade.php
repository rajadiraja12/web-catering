{{-- resources/views/auth/login.blade.php --}}
@extends('app')
@include('layout.utama')
@section('content')
@yield('nav')
<br>
<br>
<br>
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 p-4">
    <div class="w-full max-w-md">
        {{-- Card Login --}}
        <div class="bg-blue-800 rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
            {{-- Header --}}
            <div class="bg-gray-800 from-blue-600 to-indigo-700 px-8 py-8 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 rounded-2xl backdrop-blur-sm mb-2">
                    <img src="{{ asset('image/logo.png') }}" alt="">
                </div>
                <h1 class="text-2xl font-bold text-white">Hanif Katering</h1>
                <p class="text-blue-100 text-sm mt-1">Silakan login untuk melanjutkan</p>
            </div>

            {{-- Form --}}
            <div class="px-8 py-8">
                {{-- Error biasa --}}
                @if($errors->any() && !session('session_alert'))
                    <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                            </div>
                            <div class="ml-3">
                                @foreach($errors->all() as $error)
                                    <p class="text-sm">{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- SUCCESS MESSAGE --}}
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-lg">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    {{-- Username --}}
                    <div class="mb-5">
                        <label for="username" class="block text-sm font-semibold text-white mb-2">
                            <i class="fas fa-user mr-2"></i>
                            Username
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input id="username" type="text" 
                                class="w-full pl-10 pr-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('username') border-red-500 @enderror"
                                name="username" value="{{ old('username') }}" 
                                placeholder="Masukkan Username" required autofocus>
                        </div>
                        @error('username')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-5">
                        <label for="password" class="block text-sm font-semibold text-white mb-2">
                            <i class="fas fa-lock mr-2"></i>
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input id="password" type="password" 
                                class="w-full pl-10 pr-12 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('password') border-red-500 @enderror"
                                name="password" placeholder="Masukkan password" required>
                            <button type="button" onclick="togglePassword()" 
                                class="absolute inset-y-0 right-3 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i id="password-icon" class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember & Forgot --}}
                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="remember" id="remember" 
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-white">Ingat saya</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" 
                                class="text-sm text-white hover:text-white font-medium hover:underline">
                                Lupa password?
                            </a>
                        @endif
                    </div>

                    {{-- Login Button --}}
                    <button type="submit" 
                        class="w-full bg-gray-800 from-blue-600 to-indigo-700 hover:from-gray-700 hover:to-indigo-800 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200 transform hover:scale-[1.02] hover:shadow-lg hover:shadow-blue-500/30 flex items-center justify-center gap-2">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </button>
                </form>

                {{-- Divider --}}
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white text-gray-500">atau</span>
                    </div>
                </div>

                {{-- Register --}}
                <div class="text-center">
                    <p class="text-sm text-white">
                        Belum punya akun? 
                        <a href="{{ route('register') }}" class="text-gray-200 hover:text-blue-800 font-semibold hover:underline">
                            Registrasi
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- SCRIPT DAN STYLE --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{-- Font Awesome 6 (versi stabil) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
@if(session('session_alert') && session('session_data'))
    @php
        $data = session('session_data');
    @endphp
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Buat form tersembunyi untuk force login
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("login") }}';
            form.style.display = 'none';
            
            // CSRF token
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);
            
            // Username
            const username = document.createElement('input');
            username.type = 'hidden';
            username.name = 'username';
            username.value = '{{ $data["username"] ?? old("username") }}';
            form.appendChild(username);
            
            // Password
            const password = document.createElement('input');
            password.type = 'hidden';
            password.name = 'password';
            password.value = '{{ $data["password"] ?? old("password") }}';
            form.appendChild(password);
            
            // Force flag
            const force = document.createElement('input');
            force.type = 'hidden';
            force.name = 'force';
            force.value = 'true';
            form.appendChild(force);
            
            document.body.appendChild(form);

            // Tampilkan SweetAlert2
            Swal.fire({
                title: ' Akun Sedang Digunakan!',
                html: `
                    <div style="text-align: left; background: #fef2f2; padding: 15px; border-radius: 10px; border-left: 4px solid #ef4444; margin-bottom: 15px;">
                        <p style="color: #991b1b; font-weight: 600; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 20px;"></span>
                            Akun ini sedang aktif di perangkat lain!
                        </p>
                    </div>
                    <div style="text-align: left; background: #f9fafb; padding: 15px; border-radius: 10px; margin: 10px 0;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px; padding: 4px 0;">
                            <span style="font-size: 18px;"></span>
                            <div>
                                <p style="font-size: 12px; color: #6b7280; margin: 0;">Perangkat</p>
                                <p style="font-size: 14px; font-weight: 500; color: #1f2937; margin: 0;">{{ $data['device'] }}</p>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px; padding: 4px 0;">
                            <span style="font-size: 18px;"></span>
                            <div>
                                <p style="font-size: 12px; color: #6b7280; margin: 0;">Alamat IP</p>
                                <p style="font-size: 14px; font-weight: 500; color: #1f2937; margin: 0;">{{ $data['ip'] }}</p>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px; padding: 4px 0;">
                            <span style="font-size: 18px;"></span>
                            <div>
                                <p style="font-size: 12px; color: #6b7280; margin: 0;">Waktu Login</p>
                                <p style="font-size: 14px; font-weight: 500; color: #1f2937; margin: 0;">{{ $data['login_time'] }}</p>
                            </div>
                        </div>
                    </div>
                    <p style="font-size: 13px; color: #6b7280; text-align: left; margin: 10px 0 0 0; padding: 8px; background: #eff6ff; border-radius: 8px;">
                        ️ Anda harus logout dari perangkat tersebut terlebih dahulu, atau gunakan <strong>"Login Paksa"</strong> untuk mengakhiri session perangkat lain.
                    </p>
                `,
                icon: 'warning',
                iconColor: '#dc2626',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: ' Login Paksa',
                cancelButtonText: ' Tutup',
                reverseButtons: false,
                allowOutsideClick: true,
                allowEscapeKey: true,
                backdrop: 'rgba(0,0,0,0.5)',
                width: 520,
                padding: '20px',
                grow: false,
                customClass: {
                    container: 'session-alert-container',
                    popup: 'session-alert-popup',
                    title: 'session-alert-title',
                    htmlContainer: 'session-alert-html',
                    confirmButton: 'session-alert-confirm',
                    cancelButton: 'session-alert-cancel'
                },
                // Custom styling tanpa Flowbite
                background: '#ffffff',
                color: '#1f2937'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                } else {
                    document.getElementById('username').focus();
                }
            });
        });
    </script>

    <style>
        /* Custom styling SweetAlert tanpa Flowbite */
        .session-alert-popup {
            border-radius: 16px !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
        }
        
        .session-alert-title {
            font-size: 20px !important;
            font-weight: 700 !important;
            color: #1f2937 !important;
            padding: 0 !important;
            margin-bottom: 8px !important;
        }
        
        .session-alert-confirm {
            padding: 12px 28px !important;
            font-weight: 600 !important;
            border-radius: 10px !important;
            font-size: 14px !important;
            transition: all 0.2s ease !important;
            background: #dc2626 !important;
            border: none !important;
        }
        
        .session-alert-confirm:hover {
            background: #b91c1c !important;
            transform: scale(1.02) !important;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3) !important;
        }
        
        .session-alert-cancel {
            padding: 12px 28px !important;
            font-weight: 600 !important;
            border-radius: 10px !important;
            font-size: 14px !important;
            transition: all 0.2s ease !important;
            background: #e5e7eb !important;
            color: #374151 !important;
            border: none !important;
        }
        
        .session-alert-cancel:hover {
            background: #d1d5db !important;
            transform: scale(1.02) !important;
        }
        
        .session-alert-html {
            text-align: left !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        
        /* Styling untuk scroll */
        .swal2-html-container {
            max-height: 420px !important;
            overflow-y: auto !important;
            padding-right: 4px !important;
        }
        
        .swal2-html-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .swal2-html-container::-webkit-scrollbar-track {
            background: #f3f4f6;
            border-radius: 10px;
        }
        
        .swal2-html-container::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }
        
        .swal2-html-container::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
        
        /* Animasi icon warning */
        .swal2-icon.swal2-warning {
            animation: pulse-warning 1.5s ease-in-out infinite !important;
            border-color: #dc2626 !important;
        }
        
        .swal2-icon.swal2-warning .swal2-icon-content {
            color: #dc2626 !important;
        }
        
        @keyframes pulse-warning {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        /* Responsive */
        @media (max-width: 640px) {
            .swal2-popup {
                width: 95% !important;
                padding: 15px !important;
            }
            
            .session-alert-title {
                font-size: 18px !important;
            }
            
            .session-alert-confirm,
            .session-alert-cancel {
                padding: 10px 20px !important;
                font-size: 13px !important;
                width: 100% !important;
            }
        }
    </style>
@endif

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

// Auto dismiss alert after 5 seconds
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
@endsection