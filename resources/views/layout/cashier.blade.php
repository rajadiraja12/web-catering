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
          <div class="flex items-center">
              <ul class="flex flex-row font-medium mt-0 space-x-2 rtl:space-x-reverse text-sm">
                  {{-- ===== MENU KASIR ===== --}}
                  <li class="relative group">
                      <button onclick="toggleCashierMenu()" 
                              class="inline-flex items-center px-3 py-2 text-body hover:text-heading hover:bg-neutral-tertiary-medium/50 rounded-lg transition-all duration-150">
                          Kasir
                          <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                          </svg>
                      </button>
                      
                      {{-- Dropdown Kasir --}}
                      <div id="cashier-dropdown" 
                           class="hidden absolute left-0 mt-1 w-56 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1.5">
                          <a href="{{ route('kasir.dashboard') }}" 
                             class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors duration-150">
                              <span></span> Dashboard Kasir
                          </a>
                          <a href="{{ route('kasir.history') }}" 
                             class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors duration-150">
                              <span></span> Riwayat Transaksi
                          </a>
                          <hr class="my-1 border-gray-200">
                          <a href="{{ route('kasir.scan') }}" 
                             class="flex items-center gap-2 px-4 py-2 text-sm text-green-600 hover:bg-green-50 transition-colors duration-150 font-semibold">
                              <span></span> Scan Pembayaran
                          </a>
                      </div>
                  </li>
                  <li>
                      <a href="{{ route('kasir.order.manual') }}" 
                      class="inline-block px-3 py-2 text-body hover:text-heading hover:bg-neutral-tertiary-medium/50 rounded-lg transition-all duration-150 {{ request()->routeIs('kasir.order.*') ? 'bg-neutral-tertiary-medium font-semibold' : '' }}">
                          Order Manual
                      </a>
                  </li>
                  <li>
                      <a href="{{ route('kasir.customers') }}" 
                      class="inline-block px-3 py-2 text-body hover:text-heading hover:bg-neutral-tertiary-medium/50 rounded-lg transition-all duration-150 {{ request()->routeIs('kasir.customers.*') ? 'bg-neutral-tertiary-medium font-semibold' : '' }}">
                          Customer
                      </a>
                  </li>
              </ul>
          </div>
      </div>
  </nav>
</header>

{{-- {--- Dropdown Menu ---} --}}
<div class="z-50 hidden bg-white border border-default-medium rounded-xl shadow-xl w-52 divide-y divide-default transition-all" id="user-dropdown">
  <div class="px-4 py-3 text-sm">
    <span class="block text-heading font-semibold">Kasir</span>
  </div>
  <ul class="p-1.5 text-sm text-body font-medium space-y-0.5" aria-labelledby="user-menu-button">
    <li>
      <a href="{{ route('kasir.dashboard') }}" class="flex items-center w-full px-3 py-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded-lg transition-colors duration-150">
        Dashboard
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
    
    /* Responsive */
    @media (max-width: 768px) {
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