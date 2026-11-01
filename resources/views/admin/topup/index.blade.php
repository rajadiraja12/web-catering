{{-- resources/views/admin/topup/index.blade.php --}}

@extends('app')
@include('layout.admin')
@section('content')
@yield('nav')
<br><br><br><br>

<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Manajemen TopUp</h1>
            <p class="text-sm text-gray-500">Kelola permintaan topup saldo customer</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            {{-- Tombol Export dengan dropdown (tanpa Alpine.js) --}}
            <div class="relative inline-block" id="exportDropdown">
                <button onclick="toggleDropdown()" 
                        class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-all text-sm flex items-center gap-2">
                    Export
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div id="exportDropdownMenu" 
                     class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                    <a href="{{ route('admin.export.excel') }}" 
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-t-lg">
                        Export CSV (Semua)
                    </a>
                    <a href="{{ route('admin.export.excel') }}?status={{ request('status') }}&date_from={{ request('date_from') }}&date_to={{ request('date_to') }}" 
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Export dengan Filter
                    </a>
                    {{-- <hr class="my-1 border-gray-200">
                    <a href="#" 
                       onclick="exportWithDate()"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Export Periode Ini
                    </a>
                    <a href="#" 
                       onclick="exportWithStatus()"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-b-lg">
                        Export Status Ini
                    </a> --}}
                </div>
            </div>

            <span class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg text-sm font-medium">
                Pending: {{ $statistics['total_pending'] ?? 0 }}
            </span>
            <span class="px-4 py-2 bg-green-100 text-green-700 rounded-lg text-sm font-medium">
                Diverifikasi: {{ $statistics['total_verified'] ?? 0 }}
            </span>
            <span class="px-4 py-2 bg-red-100 text-red-700 rounded-lg text-sm font-medium">
                Gagal: {{ ($statistics['total_rejected'] ?? 0) + ($statistics['total_expired'] ?? 0) }}
            </span>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total TopUp</p>
            <p class="text-xl font-bold text-blue-600">{{ $statistics['total_topup'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Pending</p>
            <p class="text-xl font-bold text-yellow-600">{{ $statistics['total_pending'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Diverifikasi</p>
            <p class="text-xl font-bold text-green-600">{{ $statistics['total_verified'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Gagal</p>
            <p class="text-xl font-bold text-red-600">{{ ($statistics['total_rejected'] ?? 0) + ($statistics['total_expired'] ?? 0) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Total Nominal</p>
        <p class="text-xl font-bold text-green-600">Rp {{ number_format($statistics['total_nominal'] ?? 0, 0, ',', '.') }}</p>
    </div>
    <br>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Hari Ini</p>
        <p class="text-xl font-bold text-purple-600">Rp {{ number_format($statistics['today_nominal'] ?? 0, 0, ',', '.') }}</p>
    </div>
    <br>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs font-medium text-gray-500">Status</label>
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                    <option value="">Semua</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Diverifikasi</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Kadaluarsa</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Metode</label>
                <select name="metode" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                    <option value="">Semua</option>
                    <option value="midtrans" {{ request('metode') == 'midtrans' ? 'selected' : '' }}>Midtrans</option>
                    <option value="saldo" {{ request('metode') == 'saldo' ? 'selected' : '' }}>Saldo</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Dari</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Sampai</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="text-xs font-medium text-gray-500">Cari</label>
                <input type="text" name="search" placeholder="Kode / Customer" value="{{ request('search') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-all text-sm">
                Filter
            </button>
            <a href="{{ route('admin.topup.index') }}" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg transition-all text-sm">
                Reset
            </a>
        </form>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Nominal</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Metode</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($topups as $index => $topup)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-sm">{{ $loop->iteration + (($topups->currentPage() - 1) * $topups->perPage()) }}</td>
                        <td class="px-4 py-3">
                            <p class="font-mono text-sm font-medium">{{ $topup->kode_topup }}</p>
                            @if($topup->midtrans_order_id)
                                <p class="text-xs text-gray-400 truncate max-w-[100px]">{{ $topup->midtrans_order_id }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div>
                                <p class="font-medium text-gray-800 text-sm">{{ $topup->user->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500">{{ $topup->user->username ?? '' }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-green-600">
                            Rp {{ number_format($topup->nominal, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($topup->metode_pembayaran == 'midtrans')
                                <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">Midtrans</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">Saldo</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($topup->status == 'pending')
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Pending</span>
                            @elseif($topup->status == 'verified')
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Diverifikasi</span>
                            @elseif($topup->status == 'rejected')
                                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">Ditolak</span>
                            @else
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">Kadaluarsa</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            {{ $topup->created_at->format('d-m-Y') }}
                            <br>
                            <span class="text-xs text-gray-400">{{ $topup->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.topup.detail', $topup->id) }}" 
                               class="text-blue-500 hover:text-blue-700 text-sm font-medium">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-16 h-16 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-lg font-medium text-gray-600">Belum ada data topup</p>
                                <p class="text-sm text-gray-400">Customer akan melakukan topup melalui halaman customer</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200">
            {{ $topups->links() }}
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- SCRIPT UNTUK DROPDOWN EXPORT --}}
{{-- ============================================ --}}
<script>
    // Toggle dropdown
    function toggleDropdown() {
        const menu = document.getElementById('exportDropdownMenu');
        menu.classList.toggle('hidden');
    }

    // Tutup dropdown saat klik di luar
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('exportDropdown');
        const menu = document.getElementById('exportDropdownMenu');
        
        if (dropdown && !dropdown.contains(event.target)) {
            menu.classList.add('hidden');
        }
    });

    // Export dengan tanggal
    function exportWithDate() {
        const dateFrom = document.querySelector('input[name="date_from"]').value;
        const dateTo = document.querySelector('input[name="date_to"]').value;
        let url = '{{ route("admin.export.excel") }}';
        let params = [];
        
        if (dateFrom) params.push('date_from=' + dateFrom);
        if (dateTo) params.push('date_to=' + dateTo);
        if (params.length > 0) url += '?' + params.join('&');
        
        window.location.href = url;
    }

    // Export dengan status
    function exportWithStatus() {
        const status = document.querySelector('select[name="status"]').value;
        let url = '{{ route("admin.export.excel") }}';
        if (status) url += '?status=' + status;
        window.location.href = url;
    }

    // Tutup dropdown dengan Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const menu = document.getElementById('exportDropdownMenu');
            if (!menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
            }
        }
    });

    console.log('✅ Admin TopUp page loaded');
</script>

<style>
    /* Animasi dropdown */
    #exportDropdownMenu {
        animation: fadeInDown 0.2s ease-out;
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Hover effect */
    #exportDropdownMenu a:hover {
        background-color: #f3f4f6;
    }

    /* Responsive */
    @media (max-width: 640px) {
        #exportDropdownMenu {
            right: auto;
            left: 0;
            width: 100%;
            min-width: 200px;
        }
    }
</style>
@endsection