{{-- resources/views/admin/log_activity.blade.php --}}
@extends('app')
@include('layout.admin')
@section('content')
@yield('nav')
<br>
<br>
<br>
<br>
<br>

{{-- Filter dan Search --}}
<div class="mb-6">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-4">
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.log-activity.index') }}" 
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-all duration-200">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </a>
            
            @if($logs->count() > 0)
            <form action="{{ route('admin.log-activity.clear') }}" method="POST" class="inline" 
                  onsubmit="return confirm('Yakin ingin menghapus semua log aktivitas?')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition-all duration-200">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus Semua Log
                </button>
            </form>
            @endif
        </div>
        
        <div>
            <a href="{{ route('admin.log-activity.export', request()->query()) }}" 
                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-all duration-200 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </a>
        </div>
    </div>

    {{-- Search dan Filter --}}
    <form action="{{ route('admin.log-activity.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-4 bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
        <div class="relative flex-1 w-full">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}" 
                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-sm text-gray-900 placeholder-gray-400"
                placeholder="Cari berdasarkan username, aksi, module, atau keterangan...">
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <div class="relative flex-1 sm:flex-none">
                <select name="module" class="px-3 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-sm text-gray-900 w-full sm:w-32">
                    <option value="">Semua Modul</option>
                    @foreach($modules as $mod)
                    <option value="{{ $mod }}" {{ request('module') == $mod ? 'selected' : '' }}>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>
            <div class="relative flex-1 sm:flex-none">
                <select name="aksi" class="px-3 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-sm text-gray-900 w-full sm:w-32">
                    <option value="">Semua Aksi</option>
                    @foreach($aksis as $ak)
                    <option value="{{ $ak }}" {{ request('aksi') == $ak ? 'selected' : '' }}>{{ $ak }}</option>
                    @endforeach
                </select>
            </div>
            <div class="relative flex-1 sm:flex-none">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="pl-10 pr-3 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-sm text-gray-900 w-full sm:w-40">
            </div>
            <span class="text-gray-500 text-sm font-medium">s/d</span>
            <div class="relative flex-1 sm:flex-none">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="pl-10 pr-3 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-sm text-gray-900 w-full sm:w-40">
            </div>
            <button type="submit" 
                class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-sm hover:shadow-md whitespace-nowrap">
                Filter
            </button>
            <a href="{{ route('admin.log-activity.index') }}" 
                class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition-all duration-200">
                Reset
            </a>
        </div>
    </form>
</div>

{{-- Tabel --}}
<div class="relative overflow-x-auto bg-white shadow-sm rounded-2xl border border-gray-200">
    <table class="w-full text-sm text-left rtl:text-right text-gray-700">
        <thead class="text-xs text-white uppercase bg-blue-700 border-b border-blue-900">
            <tr>
                <th scope="col" class="px-6 py-4 text-center w-16">No</th>
                <th scope="col" class="px-6 py-4 whitespace-nowrap">Tanggal & Waktu</th>
                <th scope="col" class="px-6 py-4">Username</th>
                <th scope="col" class="px-6 py-4">Aksi</th>
                <th scope="col" class="px-6 py-4">Module</th>
                <th scope="col" class="px-6 py-4">Keterangan</th>
                <th scope="col" class="px-6 py-4 text-center">IP Address</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $index => $log)
            <tr class="{{ $loop->iteration % 2 == 0 ? 'bg-gray-50' : 'bg-white' }} border-b border-gray-200 hover:bg-blue-50 transition-colors duration-200">
                <td class="px-6 py-4 text-center font-medium text-gray-900">
                    {{ $loop->iteration + (($logs->currentPage() - 1) * $logs->perPage()) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm">{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y') }}</div>
                    <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}</div>
                </td>
                <td class="px-6 py-4 font-medium text-gray-800">
                    <span class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                        {{ $log->user ? $log->user->username : 'System' }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    @php
                        $actionColors = [
                            'Tambah' => 'bg-green-100 text-green-700',
                            'Edit' => 'bg-yellow-100 text-yellow-700',
                            'Hapus' => 'bg-red-100 text-red-700',
                            'Login' => 'bg-blue-100 text-blue-700',
                            'Logout' => 'bg-gray-100 text-gray-700',
                            'Bayar' => 'bg-purple-100 text-purple-700',
                        ];
                        $color = $actionColors[$log->aksi] ?? 'bg-gray-100 text-gray-700';
                    @endphp
                    <span class="px-2.5 py-1 {{ $color }} rounded-full text-xs font-medium">
                        {{ $log->aksi }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2.5 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">
                        {{ $log->module }}
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-600 max-w-xs">
                    <div class="truncate" title="{{ $log->keterangan }}">
                        {{ $log->keterangan }}
                    </div>
                    @if($log->old_data || $log->new_data)
                    <button onclick="showDetail({{ $log->id }})" 
                        class="text-xs text-blue-600 hover:text-blue-800 mt-1">
                        Lihat Detail
                    </button>
                    @endif
                </td>
                <td class="px-6 py-4 text-center text-xs font-mono text-gray-500">
                    {{ $log->ip ?? '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm font-medium">Tidak ada log aktivitas</p>
                        <p class="text-xs text-gray-400">Belum ada aktivitas yang tercatat</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between p-4 bg-gray-50 border-t border-gray-200 rounded-b-2xl">
        <span class="text-sm font-normal text-gray-700 mb-4 md:mb-0 block w-full md:inline md:w-auto">
            Menampilkan <span class="font-semibold text-gray-900">{{ $logs->firstItem() ?? 0 }}</span>-<span class="font-semibold text-gray-900">{{ $logs->lastItem() ?? 0 }}</span> 
            dari <span class="font-semibold text-gray-900">{{ $logs->total() }}</span> data
        </span>
        <div>
            {{ $logs->appends(request()->query())->links() }}
        </div>
    </nav>
</div>

{{-- Modal Detail --}}
<div id="detailModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 p-6 max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-900">Detail Log Aktivitas</h3>
            <button onclick="closeDetail()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div id="detailContent" class="mt-4">
            <!-- Detail akan diisi oleh JavaScript -->
        </div>
        <div class="mt-6 flex justify-end">
            <button onclick="closeDetail()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg">
                Tutup
            </button>
        </div>
    </div>
</div>

@if(session('success'))
<div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
    {{ session('error') }}
</div>
@endif

<script>
function showDetail(id) {
    // Fetch detail data
    fetch(`/admin/log-activity/${id}/detail`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const content = document.getElementById('detailContent');
                let html = `
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-semibold text-gray-600">Username</label>
                                <p class="text-gray-900">${data.data.username}</p>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-gray-600">Aksi</label>
                                <p class="text-gray-900">${data.data.aksi}</p>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-gray-600">Module</label>
                                <p class="text-gray-900">${data.data.module}</p>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-gray-600">IP Address</label>
                                <p class="text-gray-900">${data.data.ip || '-'}</p>
                            </div>
                            <div class="col-span-2">
                                <label class="text-sm font-semibold text-gray-600">Keterangan</label>
                                <p class="text-gray-900">${data.data.keterangan}</p>
                            </div>
                        </div>
                `;
                
                if (data.data.old_data) {
                    html += `
                        <div>
                            <label class="text-sm font-semibold text-gray-600">Data Lama</label>
                            <pre class="mt-1 p-3 bg-gray-50 rounded-lg text-xs overflow-x-auto">${JSON.stringify(data.data.old_data, null, 2)}</pre>
                        </div>
                    `;
                }
                
                if (data.data.new_data) {
                    html += `
                        <div>
                            <label class="text-sm font-semibold text-gray-600">Data Baru</label>
                            <pre class="mt-1 p-3 bg-gray-50 rounded-lg text-xs overflow-x-auto">${JSON.stringify(data.data.new_data, null, 2)}</pre>
                        </div>
                    `;
                }
                
                html += `</div>`;
                content.innerHTML = html;
                document.getElementById('detailModal').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat detail');
        });
}

function closeDetail() {
    document.getElementById('detailModal').classList.add('hidden');
}

// Tutup modal saat klik di luar
document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDetail();
    }
});
</script>

<style>
/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Animasi fade */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

tbody tr {
    animation: fadeIn 0.3s ease-out;
}

/* Pagination styling */
.pagination {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: 4px;
}

.pagination li {
    display: inline-block;
}

.pagination li a,
.pagination li span {
    display: block;
    padding: 6px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    color: #374151;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.2s;
}

.pagination li a:hover {
    background-color: #f3f4f6;
}

.pagination li.active span {
    background-color: #2563eb;
    color: white;
    border-color: #2563eb;
}

.pagination li.disabled span {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Modal animasi */
#detailModal {
    animation: fadeInModal 0.3s ease-out;
}

@keyframes fadeInModal {
    from { opacity: 0; }
    to { opacity: 1; }
}

#detailModal > div {
    animation: slideInModal 0.3s ease-out;
}

@keyframes slideInModal {
    from { transform: translateY(-20px) scale(0.95); opacity: 0; }
    to { transform: translateY(0) scale(1); opacity: 1; }
}
</style>

@endsection