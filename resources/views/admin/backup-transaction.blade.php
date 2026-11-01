{{-- resource/views/admin/backup.blade.php  --}}
@extends('app')
@include('layout.admin')
@section('content')
@yield('nav')
<br>
<br>
<br>
<br>
<br>

{{-- Header Laporan --}}
<div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Backup Data</h1>
        <p class="text-sm text-gray-600 mt-1">Total Backup: {{ $backups->total() }}</p>
    </div>
    <div class="flex items-center gap-3 mt-3 sm:mt-0">
        {{-- Tombol Buat Backup --}}
        <button type="button" onclick="createBackup()" 
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium text-sm rounded-lg transition-all duration-200 shadow-sm hover:shadow-md focus:ring-4 focus:ring-green-300 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFFFF">
                <path d="M480-336 288-528l51-51 105 105v-342h72v342l105-105 51 51-192 192ZM263.72-192Q234-192 213-213.15T192-264v-72h72v72h432v-72h72v72q0 29.7-21.16 50.85Q725.68-192 695.96-192H263.72Z"/>
            </svg>
            Buat Backup
        </button>
    </div>
</div>

<div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-2xl border border-default">
    <table class="w-full text-sm text-left rtl:text-right text-body">
        <thead class="text-sm text-body bg-neutral-secondary-medium border-b border-default-medium">
            <tr>
                <th scope="col" class="px-6 py-3">
                    No
                </th>
                <th scope="col" class="px-6 py-3">
                    Keterangan
                </th>
                <th scope="col" class="px-6 py-3">
                    Unduh
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse($backups as $index => $backup)
            <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                <td class="px-6 py-4">
                    {{ $loop->iteration + (($backups->currentPage() - 1) * $backups->perPage()) }}
                </td>
                <th class="px-8 py-4">
                    {{ $backup->nama_backup }}
                    <div class="text-xs text-gray-500 mt-0.5">
                        {{ $backup->created_at ? date('d-m-Y H:i', strtotime($backup->created_at)) : '-' }}
                        @if($backup->created_by)
                            | Oleh: {{ $backup->createdBy->name ?? 'System' }}
                        @endif
                    </div>
                </th>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        {{-- Tombol Download --}}
                        <a href="{{ route('admin.backups.download', $backup->id) }}" 
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFFFF">
                                <path d="M480-336 288-528l51-51 105 105v-342h72v342l105-105 51 51-192 192ZM263.72-192Q234-192 213-213.15T192-264v-72h72v72h432v-72h72v72q0 29.7-21.16 50.85Q725.68-192 695.96-192H263.72Z"/>
                            </svg>
                            Download
                        </a>
                        
                        {{-- Tombol Hapus --}}
                        <button type="button" 
                            onclick="deleteBackup({{ $backup->id }}, '{{ $backup->nama_backup }}')"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium text-sm rounded-lg transition-all duration-200 shadow-sm hover:shadow-md focus:ring-4 focus:ring-red-300 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFFFF">
                                <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                            </svg>
                            Hapus
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <p class="text-sm font-medium">Tidak ada data backup</p>
                        <p class="text-xs text-gray-400 mt-1">Belum ada backup yang dibuat</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between p-4" aria-label="Table navigation">
        <span class="text-sm font-normal text-body mb-4 md:mb-0 block w-full md:inline md:w-auto">
            Menampilkan <span class="font-semibold text-heading">{{ $backups->firstItem() ?? 0 }}</span>-<span class="font-semibold text-heading">{{ $backups->lastItem() ?? 0 }}</span> 
            dari <span class="font-semibold text-heading">{{ $backups->total() }}</span> data
        </span>
        <div>
            {{ $backups->links() }}
        </div>
    </nav>
</div>

<script>
// ============================================
// FUNGSI CREATE BACKUP
// ============================================
function createBackup() {
    if (!confirm('Yakin ingin membuat backup data sekarang?')) {
        return;
    }
    
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
        showNotification('❌ Terjadi kesalahan saat membuat backup!', 'error');
        console.error('Error:', error);
    });
}

// ============================================
// FUNGSI DELETE BACKUP
// ============================================
function deleteBackup(id, namaBackup) {
    if (!confirm('Yakin ingin menghapus backup "' + namaBackup + '"?')) {
        return;
    }
    
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
        console.error('Error:', error);
    });
}

// ============================================
// FUNGSI NOTIFICATION
// ============================================
function showNotification(message, type = 'success') {
    const oldNotification = document.getElementById('custom-notification');
    if (oldNotification) {
        oldNotification.remove();
    }
    
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
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}
</script>
@endsection