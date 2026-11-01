{{-- resources/views/admin/hutang/detail.blade.php --}}

@extends('app')
@include('layout.admin')
@section('content')
@yield('nav')
<br><br><br><br>

<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.hutang.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Detail Hutang</h1>
            <span class="ml-auto text-sm text-gray-400">#{{ $hutang->kode_order }}</span>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
            {{-- Status Banner --}}
            <div class="p-4 border-b">
                <div class="flex flex-wrap justify-between items-center gap-2">
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        @if($hutang->due_date && $hutang->due_date < now())
                            <span class="px-4 py-2 bg-red-100 text-red-700 rounded-full text-sm font-medium">🔴 Jatuh Tempo</span>
                        @else
                            <span class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium">⏳ Menunggu Pembayaran</span>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Total Hutang</p>
                        <p class="text-2xl font-bold text-red-600">Rp {{ number_format($hutang->total_harga, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            {{-- Informasi --}}
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500">Customer</p>
                        <p class="font-semibold">{{ $hutang->customer->name ?? 'Unknown' }}</p>
                        <p class="text-sm text-gray-500">@ {{ $hutang->customer->username ?? '' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500">Menu</p>
                        <p class="font-semibold">{{ $hutang->menu->nama_menu ?? 'Menu' }}</p>
                        <p class="text-sm text-gray-500">Paket: {{ ucfirst($hutang->paket) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500">Tanggal Order</p>
                        <p class="font-semibold">{{ $hutang->created_at ? $hutang->created_at->format('d-m-Y H:i') : '-' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500">Jatuh Tempo</p>
                        @if($hutang->due_date)
                            <p class="font-semibold {{ $hutang->due_date < now() ? 'text-red-600' : '' }}">
                                {{ $hutang->due_date->format('d-m-Y') }}
                                @if($hutang->due_date < now())
                                    <span class="text-xs text-red-500">(Lewat)</span>
                                @endif
                            </p>
                        @else
                            <p class="text-gray-400">-</p>
                        @endif
                    </div>
                </div>

                {{-- Form Konfirmasi --}}
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="font-semibold text-gray-700 mb-4">✅ Konfirmasi Pembayaran</h4>
                    
                    <form id="form-confirm-hutang" enctype="multipart/form-data">
                        @csrf
                        
                        {{-- <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Bukti Pembayaran</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-500 transition-all cursor-pointer"
                                 onclick="document.getElementById('bukti_pembayaran').click()">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-sm text-gray-600">Klik untuk upload bukti pembayaran</p>
                                <p class="text-xs text-gray-400">JPG, PNG, JPEG (Max 2MB)</p>
                                <input type="file" id="bukti_pembayaran" name="bukti_pembayaran" 
                                       class="hidden" accept="image/*" onchange="previewImage(this)">
                            </div>
                            <div id="preview-container" class="mt-3 hidden">
                                <img id="preview-image" src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg border border-gray-200">
                                <button type="button" onclick="removeImage()" class="text-red-500 text-sm mt-1">Hapus</button>
                            </div>
                        </div> --}}

                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan (Opsional)</label>
                            <textarea name="catatan" id="catatan" rows="2"
                                      class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                      placeholder="Tambahkan catatan jika diperlukan"></textarea>
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" 
                                    class="flex-1 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-all hover:scale-[1.02]">
                                <span id="btnText">✅ Konfirmasi Bayar</span>
                                <span id="btnLoading" class="hidden">
                                    <svg class="inline w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Memproses...
                                </span>
                            </button>
                            <button type="button" onclick="rejectConfirm()" 
                                    class="flex-1 py-3 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-xl transition-all">
                                ❌ Tolak
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================
// PREVIEW IMAGE
// ============================================
function previewImage(input) {
    const container = document.getElementById('preview-container');
    const preview = document.getElementById('preview-image');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.classList.remove('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    document.getElementById('bukti_pembayaran').value = '';
    document.getElementById('preview-container').classList.add('hidden');
}

// ============================================
// CONFIRM HUTANG
// ============================================
document.getElementById('form-confirm-hutang').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    const submitBtn = this.querySelector('button[type="submit"]');
    
    btnText.classList.add('hidden');
    btnLoading.classList.remove('hidden');
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    
    fetch('{{ route("admin.hutang.confirm", $hutang->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✅ ' + data.message, 'success');
            setTimeout(() => {
                window.location.href = '{{ route("admin.hutang.index") }}';
            }, 1500);
        } else {
            let errorMsg = '❌ Gagal!';
            if (data.errors) {
                errorMsg = Object.values(data.errors).flat().join('\n');
            } else if (data.message) {
                errorMsg = data.message;
            }
            showNotification(errorMsg, 'error');
            btnText.classList.remove('hidden');
            btnLoading.classList.add('hidden');
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        showNotification('❌ ' + error.message, 'error');
        btnText.classList.remove('hidden');
        btnLoading.classList.add('hidden');
        submitBtn.disabled = false;
    });
});

// ============================================
// REJECT CONFIRM
// ============================================
function rejectConfirm() {
    const alasan = prompt('Masukkan alasan penolakan:');
    if (alasan === null) return;
    
    if (!alasan.trim()) {
        showNotification('❌ Alasan harus diisi!', 'error');
        return;
    }
    
    fetch('{{ route("admin.hutang.reject", $hutang->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ alasan: alasan })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✅ ' + data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification('❌ ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('❌ ' + error.message, 'error');
    });
}

// ============================================
// NOTIFICATION
// ============================================
function showNotification(message, type = 'info') {
    const oldNotification = document.getElementById('custom-notification');
    if (oldNotification) oldNotification.remove();
    
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500',
        warning: 'bg-yellow-500'
    };
    
    const notification = document.createElement('div');
    notification.id = 'custom-notification';
    notification.className = `fixed top-4 right-4 z-50 transform transition-all duration-500 translate-x-0 opacity-100 ${colors[type] || colors.info} text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-3 max-w-md`;
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
        if (notification.parentNode) notification.remove();
    }, 5000);
}
</script>
@endsection