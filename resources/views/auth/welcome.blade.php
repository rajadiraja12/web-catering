<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5" />
  <title>Hanif Catering</title>
    <link rel="shortcut icon" href="{{ asset('image/logo.png') }}" type="image/x-icon">
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
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,1" />
  <style>
    .lobster-regular {
      font-family: 'Lobster', cursive;
    }
    /* fixed header */
    .fixed-header {
      z-index: 20;
    }
    /* tombol login hover */
    .btn-login {
      transition: all 0.2s ease;
    }
    .btn-login:hover {
      box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
    }
    /* gambar potret full viewport height, tapi tetap bisa di-scroll */
    .hero-portrait {
      width: 100%;
      height: 100vh;
      object-fit:contain;
      object-position: center;
      display: block;
    }
    /* body bisa di-scroll */
    html, body {
      margin: 0;
      padding: 0;
      overflow-y: auto; /* ← ini kunci biar bisa scroll */
      overflow-x: hidden;
      height: 100%;
    }
    /* navbar bg solid */
    .navbar-bg {
      background-color: white;
    }
    .dark .navbar-bg {
      background-color: #111827;
    }
  </style>
</head>
<body class="bayang">

  <!-- ======== NAVBAR ======== -->
  <header class="fixed top-0 start-0 z-20 w-full border-b border-default shadow-sm bg-white dark:bg-gray-900">
    <nav class="bg-white dark:bg-gray-900 border-b border-default/50">
      <div class="flex flex-wrap justify-between items-center mx-auto max-w-screen-xl p-4">
        <a href="#" class="flex items-center space-x-3 rtl:space-x-reverse group">
          <img src="{{ asset('image/logo.png') }}" class="h-8 transition-transform group-hover:scale-105" alt="Logo" />
          <div class="lobster-regular">
                <span class="self-center text-xl text-heading font-bold whitespace-nowrap tracking-tight">Hanif Catering</span>
              </div>
        </a>
        <div class="flex items-center space-x-4 rtl:space-x-reverse">             
          <a href="login">
            <button type="button" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-md transition-all duration-200 shadow-sm hover:shadow-md focus:ring-4 focus:ring-blue-300 focus:outline-none btn-login">
              <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#FFFFFF">
                <path d="M234-276q51-39 114-61.5T480-360q69 0 132 22.5T726-276q35-41 54.5-93T800-480q0-133-93.5-226.5T480-800q-133 0-226.5 93.5T160-480q0 59 19.5 111t54.5 93Zm146.5-204.5Q340-521 340-580t40.5-99.5Q421-720 480-720t99.5 40.5Q620-639 620-580t-40.5 99.5Q539-440 480-440t-99.5-40.5ZM480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm100-95.5q47-15.5 86-44.5-39-29-86-44.5T480-280q-53 0-100 15.5T294-220q39 29 86 44.5T480-160q53 0 100-15.5ZM523-537q17-17 17-43t-17-43q-17-17-43-17t-43 17q-17 17-17 43t17 43q17 17 43 17t43-17Zm-43-43Zm0 360Z"/>
              </svg>
              Login
            </button>
          </a>
        </div>
      </div>
    </nav>
  </header>

  <!-- ======== GAMBAR POTRET SATU LAYAR (bisa di-scroll) ======== -->
  <main class="pt-16"> <!-- padding top agar tidak ketutupan navbar -->
    <br><br>
    <img 
      src="{{ asset('image/utama.jpg') }}" 
      alt="Potret Chef Catering" 
      class="hero-portrait"
      loading="eager"
    />
  </main>

  <!-- ======== FOOTER (di bawah gambar, bisa di-scroll) ======== -->
  <section class="bg-gray-50 dark:bg-gray-800 py-16">
    <div class="container mx-auto px-4 max-w-screen-xl">
      <!-- Judul Section -->
      <div class="text-center mb-10">
        <div class="lobster-regular">
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Punya Acara Dalam Waktu Dekat?</h2>
          <p class="text-gray-500 dark:text-gray-400">Hanif Catering Selalu Siap Melayani.</p>
        </div>
      </div>

      <!-- Grid 3 Kotak -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Info Kontak -->
        <div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Info Kontak</h3>
          <ul class="space-y-3 text-sm">
            <li class="flex items-start gap-3 text-gray-600 dark:text-gray-300">
              <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-base">location_on</span>
              <span>Hanif Catering, Gedung KBSA, Kompleks Pendidikan dan Dakwah Yayasan Al Hanif, Jln. Garuda, Perumnas Bumi Cibeber Kencana<br>Cilegon - Banten</span>
            </li>
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
            <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
              <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-base">schedule</span>
              <span>Senin - Jum'at: 08.00 - 16.00</span>
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
{{-- FLOATING WHATSAPP BUTTON (ICON ONLY) --}}
<div class="fixed bottom-6 right-6 z-50">
    {{-- Pulsing ring --}}
    <div class="absolute inset-0 flex items-center justify-center">
        <div class="w-16 h-16 bg-green-400 rounded-full opacity-75 animate-ping"></div>
    </div>
        <a href="https://wa.me/6289675704781?text=Halo%20Hanif%20Catering%2C%20saya%20ingin%20bertanya%20tentang%20menu%20catering" 
        target="_blank" 
        rel="noopener noreferrer"
        class="relative flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-3 py-3 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
            
            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
        </a>
</div>
</body>
</html>