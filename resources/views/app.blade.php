{{-- resource/views/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests"> --}}
    <title>Hanif Catering</title>
    <link rel="shortcut icon" href="image/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.css"  rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/midtrans-snap/1.0.0/snap.min.js"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
    {{-- <script>
    // On page load or when changing themes, best to add inline in `head` to avoid FOUC
    if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark')
    }
  </script> --}}
  <style>
        .lobster-regular {
            font-family: "Lobster", sans-serif;
            font-weight: 400;
            font-style: normal;
        }
        /* Tambahkan di file CSS utama Anda */
        .swal2-popup {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        }
        
        .swal2-title {
            color: #1f2937 !important;
        }
        
        .swal2-confirm {
            transition: all 0.2s ease !important;
        }
        
        .swal2-confirm:hover {
            transform: scale(1.02) !important;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3) !important;
        }
        
        .swal2-cancel:hover {
            transform: scale(1.02) !important;
        }
        
        /* Styling untuk konten HTML di SweetAlert */
        .swal2-html-container {
            color: #374151 !important;
        }
        
        /* Scrollbar styling */
        .swal2-html-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .swal2-html-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .swal2-html-container::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }
        
        .swal2-html-container::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
    </style>
  
</head>
<body class="bg-white dark:bg-gray-800">
<div class="bayang">
    <div class="container mx-auto p-4">
        @yield('content')
    </div>

    <section class="py-16">
        <div class="container mx-auto px-4 max-w-screen-xl">
            
            <!-- Judul Section -->
            <div class="text-center mb-10">
                <div class="lobster-regular">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Punya Acara Dalam Waktu Dekat?</h2>
                    <p class="text-gray-500 dark:text-gray-400">Hanif Catering Selalu Siap Melayani.</p>
                </div>
            </div>

            <!-- Grid 3 Kotak Kutipan -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                <!-- Info Kontak -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Info Kontak</h3>
                    <ul class="space-y-3 text-sm">
                        <!-- WhatsApp Link -->
                        <li>
                            <a href="https://wa.me/6289675704781" class="flex items-center gap-3 text-gray-600 dark:text-gray-300 hover:text-blue-600 transition-colors">
                                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-base">phone</span>
                                <span>(+62) 896-7570-4781</span>
                            </a>
                        </li>
                        
                        <!-- Email Link -->
                        <li>
                            <a href="mailto:kbsacatering@gmail.com" class="flex items-center gap-3 text-gray-600 dark:text-gray-300 hover:text-blue-600 transition-colors">
                                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-base">email</span>
                                <span>kbsacatering@gmail.com</span>
                            </a>
                        </li>
                
                        <!-- Jam Operasional (Bukan Link) -->
                        <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                            <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-base">schedule</span>
                            <span>Senin - Jum'at: 08.00 - 16.00</span>
                        </li>
                
                        <!-- Lokasi (Bukan Link) -->
                        <li class="flex items-start gap-3 text-gray-600 dark:text-gray-300">
                            <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-base">location_on</span>
                            <span>Hanif Catering, Gedung KBSA, Kompleks Pendidikan dan Dakwah Yayasan Al Hanif, Jln. Garuda, Perumnas Bumi Cibeber Kencana<br>Cilegon - Banten</span>
                        </li>
                        <li class="flex items-start gap-3 text-gray-600 dark:text-gray-300">
                            <p class="text-xs text-gray-400">
                                Silahkan kunjungi lebih lanjut untuk mengetahui 
                                <a href="{{ route('terms') }}" target="_blank" class="text-blue-400 hover:underline">Syarat & Ketentuan</a> 
                            </p>
                        </li>
                    </ul>
                </div>

                <!-- Maps -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Lokasi Kami</h3>
                    <div class="w-full h-48 rounded-lg overflow-hidden shadow-md">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3967.7068847119876!2d106.06104111017827!3d-6.034903293925424!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e418f9537e0356d%3A0x25ca3dd9b34a94f9!2sKoperasi%20Al-Hanif!5e0!3m2!1sid!2sid!4v1785727919590!5m2!1sid!2sid" 
                            width="100%" 
                            height="100%" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade"
                            class="w-full h-full"
                            title="Lokasi Catering"
                        ></iframe>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Klik peta untuk melihat rute</p>
                </div>


            </div>

        </div>
    </section>
</div>


</body>
</html>