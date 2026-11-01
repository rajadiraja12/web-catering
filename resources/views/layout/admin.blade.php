{{-- resource/views/layout/admin.blade.php --}}
@section('nav')
{{-- 1. Pastikan di sini pakai bg-white (atau warna solid pilihanmu) TANPA/95 atau opacity --}}
<header class="fixed top-0 start-0 z-20 w-full border-b border-default shadow-sm bg-white dark:bg-gray-900">
{{-- 2. Nav atas juga diberi bg-white/solid agar tidak tembus --}}
  <nav class="bg-white dark:bg-gray-900 border-b border-default/50">
      <div class="flex flex-wrap justify-between items-center mx-auto max-w-screen-xl p-4">
          <a href="#" class="flex items-center space-x-3 rtl:space-x-reverse group">
              <img src="{{ asset('image/logo.png') }}" class="h-8 transition-transform group-hover:scale-105" alt="Logo" />
              <div class="lobster-regular">
                <span class="self-center text-xl text-heading font-bold whitespace-nowrap tracking-tight">Hanif Catering</span>
              </div>
            </a>
          
          <div class="flex items-center space-x-4 rtl:space-x-reverse">             
            <button type="button" class="flex text-sm bg-white dark:bg-gray-900 rounded-full md:me-0 focus:ring-4 focus:ring-neutral-tertiary transition-all duration-200 hover:scale-105" id="user-menu-button" aria-expanded="false" data-dropdown-toggle="user-dropdown" data-dropdown-placement="bottom">
                <span class="sr-only">Open user menu</span>
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M234-276q51-39 114-61.5T480-360q69 0 132 22.5T726-276q35-41 54.5-93T800-480q0-133-93.5-226.5T480-800q-133 0-226.5 93.5T160-480q0 59 19.5 111t54.5 93Zm146.5-204.5Q340-521 340-580t40.5-99.5Q421-720 480-720t99.5 40.5Q620-639 620-580t-40.5 99.5Q539-440 480-440t-99.5-40.5ZM480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm100-95.5q47-15.5 86-44.5-39-29-86-44.5T480-280q-53 0-100 15.5T294-220q39 29 86 44.5T480-160q53 0 100-15.5ZM523-537q17-17 17-43t-17-43q-17-17-43-17t-43 17q-17 17-17 43t17 43q17 17 43 17t43-17Zm-43-43Zm0 360Z"/></svg>
            </button>
          </div>
      </div>
  </nav>

    {{-- 3. Nav bawah juga dipastikan pakai warna solid --}}
    <nav class="bg-gray-50 dark:bg-gray-800">
        <div class="max-w-screen-xl px-4 py-1 mx-auto">
            <div class="flex items-center overflow-x-auto overflow-y-hidden scrollbar-hide">
                <ul class="flex flex-row font-medium mt-0 space-x-2 rtl:space-x-reverse text-sm whitespace-nowrap">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="inline-block px-3 py-2 text-heading bg-neutral-tertiary-medium rounded-lg font-semibold transition-colors duration-150" aria-current="page">Dashboard</a>
                    </li>
                    <li>
                        <a href="{{ route('admin.menus') }}" class="inline-block px-3 py-2 text-body hover:text-heading hover:bg-neutral-tertiary-medium/50 rounded-lg transition-all duration-150">Kelola Menu</a>
                    </li>
                    <li>
                        <a href="{{ route('admin.orders.index') }}" class="inline-block px-3 py-2 text-body hover:text-heading hover:bg-neutral-tertiary-medium/50 rounded-lg transition-all duration-150">Kelola Pesanan</a>
                    </li>

                    <li>
                        <a href="{{ route('admin.reports') }}" class="inline-block px-3 py-2 text-body hover:text-heading hover:bg-neutral-tertiary-medium/50 rounded-lg transition-all duration-150">Laporan Keuangan</a>
                    </li>
                    <li>
                        <a href="{{ route('admin.log-activity.index') }}" class="inline-block px-3 py-2 text-body hover:text-heading hover:bg-neutral-tertiary-medium/50 rounded-lg transition-all duration-150">Log Aktivitas</a>
                    </li>
                    <li>
                        <a href="{{ route('admin.backups') }}" class="inline-block px-3 py-2 text-body hover:text-heading hover:bg-neutral-tertiary-medium/50 rounded-lg transition-all duration-150">Backup Data</a>
                    </li>
                    <li>
                        <a href="{{ route('admin.payment') }}" class="inline-block px-3 py-2 text-body hover:text-heading hover:bg-neutral-tertiary-medium/50 rounded-lg transition-all duration-150">Pembayaran</a>
                    </li>
                    <li>
                        <a href="{{ route('admin.hutang.index') }}" 
                        class="inline-block px-3 py-2 text-body hover:text-heading hover:bg-neutral-tertiary-medium/50 rounded-lg transition-all duration-150 {{ request()->routeIs('admin.hutang.*') ? 'bg-neutral-tertiary-medium font-semibold' : '' }}">
                            Kredit
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.users') }}" class="inline-block px-3 py-2 text-body hover:text-heading hover:bg-neutral-tertiary-medium/50 rounded-lg transition-all duration-150">Kelola User</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

{{-- {--- Dropdown Menu ---} --}}
<div class="z-50 hidden bg-white border border-default-medium rounded-xl shadow-xl w-52 divide-y divide-default transition-all" id="user-dropdown">
  <div class="px-4 py-3 text-sm">
    <span class="block text-heading font-semibold">Admin</span>
  </div>
  <ul class="p-1.5 text-sm text-body font-medium space-y-0.5" aria-labelledby="user-menu-button">
    <li>
      <a href="{{ route('admin.dashboard') }}" class="flex items-center w-full px-3 py-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded-lg transition-colors duration-150">
        Dashboard
      </a>
    </li>
    <li>
      <a href="{{ route('admin.topup.index') }}" class="flex items-center w-full px-3 py-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded-lg transition-colors duration-150">
        Saldo
      </a>
    </li>
    <li>
      <a href="{{ route('admin.cashier.dashboard') }}" class="flex items-center w-full px-3 py-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded-lg transition-colors duration-150">
        Kasir Dashboard
      </a>
    </li>
    <li>
      <a href="{{ route('admin.cashier.scan') }}" class="flex items-center w-full px-3 py-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded-lg transition-colors duration-150">
        Kasir Scan
      </a>
    </li>
    <li>
      <a href="{{ route('admin.cashier.history') }}" class="flex items-center w-full px-3 py-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded-lg transition-colors duration-150">
        Kasir History
      </a>
    </li>
  </ul>
  <div class="p-1.5">
    <form action="{{ route('logout') }}" method="POST">
    @csrf
    <button type="submit" class="flex items-center w-full px-3 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-colors duration-150 font-semibold">
      Sign out
    </button>
    </form>
  </div>
</div>

{{-- ============================================ --}}
{{-- SCRIPT UNTUK TOGGLE DROPDOWN KASIR --}}
{{-- ============================================ --}}
<script>
    // Toggle dropdown kasir
    function toggleCashierMenu() {
        const dropdown = document.getElementById('cashier-dropdown');
        if (dropdown) {
            dropdown.classList.toggle('hidden');
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('cashier-dropdown');
        const button = event.target.closest('button');
        
        if (dropdown && !dropdown.classList.contains('hidden')) {
            const isClickInside = dropdown.contains(event.target) || (button && button.textContent.includes('Kasir'));
            if (!isClickInside) {
                dropdown.classList.add('hidden');
            }
        }
    });

    // Close dropdown on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const dropdown = document.getElementById('cashier-dropdown');
            if (dropdown && !dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
            }
        }
    });
</script>

<style>
    /* Animasi dropdown */
    #cashier-dropdown {
        animation: slideDown 0.2s ease-out;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Hover effect untuk menu */
    .group:hover #cashier-dropdown {
        display: block;
    }
    
    /* ===== SCROLL HORIZONTAL UNTUK MOBILE ===== */
    .scrollbar-hide {
        -ms-overflow-style: none;  /* Internet Explorer 10+ */
        scrollbar-width: none;  /* Firefox */
    }
    
    .scrollbar-hide::-webkit-scrollbar {
        display: none;  /* Safari and Chrome */
    }
    
    /* Tambahan untuk indikator scroll di mobile */
    @media (max-width: 768px) {
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
        }
        
        /* Indikator fade di ujung kanan (opsional) */
        .overflow-x-auto::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 40px;
            height: 100%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.8));
            pointer-events: none;
        }
        
        /* Tambahkan padding di container nav untuk memberi ruang scroll */
        .max-w-screen-xl {
            position: relative;
        }
        
        #cashier-dropdown {
            position: fixed;
            top: auto;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            border-radius: 20px 20px 0 0;
            max-height: 60vh;
            overflow-y: auto;
            padding-bottom: 20px;
        }
        
        #cashier-dropdown a {
            padding: 12px 20px;
            font-size: 15px;
        }
    }
</style>

@endsection