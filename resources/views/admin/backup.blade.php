@extends('app')
@include('layout.admin')
@section('content')
@yield('nav')
<br>
<br>
<br>
<br>
<br>

{{-- Header --}}
<div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Backup Data</h1>
        <p class="text-sm text-gray-600 mt-1">
            Total: {{ $totalBackups ?? 0 }} backup | 
            Manual: {{ $totalManual ?? 0 }} | 
            Otomatis: {{ $totalOtomatis ?? 0 }} | 
            Total Size: {{ number_format($totalSize ?? 0, 2) }} KB
        </p>
    </div>
    <div class="flex items-center gap-3 mt-3 sm:mt-0">
        {{-- Backup Transaksi --}}
        <button type="button" onclick="backupTransaksi()" 
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Backup Transaksi
        </button>

        {{-- Backup Keuangan --}}
        <button type="button" onclick="backupKeuangan()" 
            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Backup Keuangan
        </button>
    </div>
</div>

{{-- Tabel Backup --}}
<div class="relative overflow-x-auto bg-white shadow-sm rounded-2xl border border-gray-200">
    <table class="w-full text-sm text-left rtl:text-right text-gray-700">
        <thead class="text-xs text-gray-800 uppercase bg-blue-200 border-b border-blue-900">
            <tr>
                <th scope="col" class="px-6 py-4 text-center">No</th>
                <th scope="col" class="px-6 py-4">Nama Backup</th>
                <th scope="col" class="px-6 py-4">Tipe</th>
                <th scope="col" class="px-6 py-4">Periode</th>
                <th scope="col" class="px-6 py-4">Keterangan</th>
                <th scope="col" class="px-6 py-4 text-right">Size</th>
                <th scope="col" class="px-6 py-4">Tanggal</th>
                <th scope="col" class="px-6 py-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($backups as $index => $backup)
            <tr class="{{ $loop->iteration % 2 == 0 ? 'bg-gray-50' : 'bg-white' }} border-b border-gray-200 hover:bg-blue-50">
                <td class="px-6 py-4 text-center font-medium text-gray-900">
                    {{ $loop->iteration + (($backups->currentPage() - 1) * $backups->perPage()) }}
                </td>
                <td class="px-6 py-4 font-medium text-gray-800">
                    {{ Str::limit($backup->nama_backup, 40) }}
                </td>
                <td class="px-6 py-4">
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium 
                        {{ $backup->type == 'otomatis' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ $backup->type == 'otomatis' ? 'Otomatis' : 'Manual' }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm">
                    @if($backup->periode_start && $backup->periode_end)
                        {{ date('d/m/Y', strtotime($backup->periode_start)) }} - {{ date('d/m/Y', strtotime($backup->periode_end)) }}
                    @else
                        -
                    @endif
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    {{ Str::limit($backup->keterangan_data, 30) }}
                </td>
                <td class="px-6 py-4 text-right font-medium">
                    {{ number_format($backup->size, 2) }} KB
                </td>
                <td class="px-6 py-4 text-sm">
                    {{ $backup->created_at ? date('d-m-Y H:i', strtotime($backup->created_at)) : '-' }}
                </td>
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <a href="{{ route('admin.backups.download', $backup->id) }}" 
                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-100 hover:bg-blue-600 text-blue-700 hover:text-white text-xs rounded-lg transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download
                        </a>
                        <button onclick="deleteBackup({{ $backup->id }})" 
                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-100 hover:bg-red-600 text-red-700 hover:text-white text-xs rounded-lg transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hapus
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <p class="text-sm font-medium">Belum ada backup</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    <nav class="flex items-center justify-between p-4 bg-gray-50 border-t border-gray-200 rounded-b-2xl">
        <span class="text-sm text-gray-700">
            Menampilkan {{ $backups->firstItem() ?? 0 }} - {{ $backups->lastItem() ?? 0 }} 
            dari {{ $backups->total() }} data
        </span>
        <div>
            {{ $backups->links() }}
        </div>
    </nav>
</div>

<script>
function backupTransaksi() {
    if (!confirm('Yakin ingin membuat backup transaksi sekarang?')) return;
    
    const url = "{{ route('admin.backups.transaksi') }}";
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✅ ' + data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('❌ ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('❌ Terjadi kesalahan!', 'error');
    });
}

function backupKeuangan() {
    if (!confirm('Yakin ingin membuat backup keuangan sekarang?')) return;
    
    const url = "{{ route('admin.backups.store') }}";
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✅ ' + data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('❌ ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('❌ Terjadi kesalahan!', 'error');
    });
}

function deleteBackup(id) {
    if (!confirm('Yakin ingin menghapus backup ini?')) return;
    
    const url = "{{ route('admin.backups.delete', ['id' => ':id']) }}".replace(':id', id);
    
    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✅ ' + data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('❌ ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('❌ Terjadi kesalahan!', 'error');
    });
}

function showNotification(message, type = 'success') {
    const old = document.getElementById('custom-notification');
    if (old) old.remove();
    
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500',
        warning: 'bg-yellow-500'
    };
    
    const notification = document.createElement('div');
    notification.id = 'custom-notification';
    notification.className = `fixed top-4 right-4 z-50 transform transition-all duration-500 translate-x-0 opacity-100 ${colors[type] || colors.success} text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-3`;
    notification.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" class="text-white hover:text-gray-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;
    
    document.body.appendChild(notification);
    setTimeout(() => { if (notification.parentNode) notification.remove(); }, 3000);
}
</script>

@endsection