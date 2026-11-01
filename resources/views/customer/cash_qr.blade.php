{{-- resources/views/customer/cash_qr.blade.php --}}

@extends('app')
@section('content')
<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .qr-card {
        background: white;
        border-radius: 24px;
        padding: 40px;
        max-width: 480px;
        width: 100%;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        animation: slideUp 0.5s ease-out;
    }
    
    .qr-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .qr-header .icon {
        font-size: 48px;
        margin-bottom: 10px;
    }
    
    .qr-header h1 {
        font-size: 24px;
        font-weight: 700;
        color: #1a202c;
        margin: 0;
    }
    
    .qr-header p {
        color: #718096;
        font-size: 14px;
        margin: 5px 0 0;
    }
    
    .qr-image-container {
        background: #f7fafc;
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        border: 2px dashed #e2e8f0;
        margin-bottom: 20px;
    }
    
    .qr-image-container svg {
        max-width: 100%;
        height: auto;
    }
    
    .qr-image-container img {
        max-width: 100%;
        height: auto;
    }
    
    .qr-info {
        background: #ebf8ff;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
    }
    
    .qr-info-item {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        font-size: 14px;
    }
    
    .qr-info-item .label {
        color: #4a5568;
    }
    
    .qr-info-item .value {
        font-weight: 600;
        color: #2d3748;
    }
    
    .qr-status {
        text-align: center;
        padding: 12px;
        border-radius: 12px;
        margin-bottom: 20px;
        background: #fefcbf;
        border: 1px solid #f6e05e;
    }
    
    .qr-status .status-text {
        font-weight: 600;
        color: #744210;
    }
    
    .qr-status .status-icon {
        font-size: 20px;
        margin-right: 8px;
    }
    
    .qr-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 20px;
    }
    
    .qr-actions .btn {
        padding: 12px 16px;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .btn-primary {
        background: #4299e1;
        color: white;
    }
    
    .btn-primary:hover {
        background: #3182ce;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(66, 153, 225, 0.4);
    }
    
    .btn-success {
        background: #48bb78;
        color: white;
    }
    
    .btn-success:hover {
        background: #38a169;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(72, 187, 120, 0.4);
    }
    
    .btn-warning {
        background: #ed8936;
        color: white;
    }
    
    .btn-warning:hover {
        background: #dd6b20;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(237, 137, 54, 0.4);
    }
    
    .btn-outline {
        background: transparent;
        color: #4a5568;
        border: 2px solid #e2e8f0;
    }
    
    .btn-outline:hover {
        background: #f7fafc;
        border-color: #cbd5e0;
    }
    
    .btn-full {
        grid-column: 1 / -1;
    }
    
    .btn-check-status {
        background: #9f7aea;
        color: white;
        width: 100%;
        padding: 14px;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-check-status:hover {
        background: #805ad5;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(159, 122, 234, 0.4);
    }
    
    .btn-check-status:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    .qr-footer {
        text-align: center;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }
    
    .qr-footer a {
        color: #4299e1;
        text-decoration: none;
        font-size: 14px;
    }
    
    .qr-footer a:hover {
        text-decoration: underline;
    }
    
    /* Loading spinner */
    .spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 1s ease-in-out infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
</style>

<div class="qr-card">
    {{-- Header --}}
    <div class="qr-header">
        <div class="icon"></div>
        <h1>Pembayaran Tunai</h1>
        <p>Scan QR Code untuk konfirmasi pembayaran</p>
    </div>
    
    {{-- QR Code --}}
    <div class="qr-image-container" id="qr-image-container">
        @if($pending->qr_image)
            {{-- Tampilkan sebagai gambar HTML --}}
            <img src="{{ $pending->qr_image }}" alt="QR Code" style="max-width: 100%; height: auto;">
        @else
            <div style="padding: 40px; color: #718096;">
                <div class="spinner" style="border-color: #4299e1; border-top-color: transparent;"></div>
                <p style="margin-top: 10px;">Memuat QR Code...</p>
            </div>
        @endif
    </div>
    
    {{-- Status --}}
    <div class="qr-status">
        <span class="status-icon">⏳</span>
        <span class="status-text" id="status-text">Menunggu pembayaran...</span>
    </div>
    
    {{-- Informasi --}}
    <div class="qr-info">
        <div class="qr-info-item">
            <span class="label">Nomor Pesanan</span>
            <span class="value">{{ $pending->order_id }}</span>
        </div>
        <div class="qr-info-item">
            <span class="label">Total</span>
            <span class="value" style="color: #48bb78; font-size: 18px;">
                Rp {{ number_format($pending->total, 0, ',', '.') }}
            </span>
        </div>
        <div class="qr-info-item">
            <span class="label">Customer</span>
            {{-- Gunakan $user dari controller --}}
            <span class="value">{{ $user->name ?? session('user_name', 'Customer') }}</span>
        </div>
        <div class="qr-info-item">
            <span class="label">Berlaku hingga</span>
            <span class="value" style="color: #e53e3e;">
                {{ $pending->expires_at ? date('d-m-Y H:i', strtotime($pending->expires_at)) : '24 jam' }}
            </span>
        </div>
    </div>
    
    {{-- Tombol Aksi --}}
    <div class="qr-actions">
        <a href="{{ route('cash.qr.print', $pending->order_id) }}" 
           target="_blank" class="btn btn-primary">
            Print
        </a>
        <a href="{{ route('cash.qr.download', $pending->order_id) }}" 
           class="btn btn-success">
            Download
        </a>
        <button onclick="copyOrderId()" class="btn btn-outline">
            Copy ID
        </button>
        <a href="{{ route('customer.orders') }}" class="btn btn-outline">
            Pesanan Saya
        </a>
    </div>
    
    {{-- Tombol Cek Status --}}
    <button onclick="checkStatus()" class="btn-check-status" id="btnCheckStatus">
        Cek Status Pembayaran
    </button>
    
    {{-- Footer --}}
    <div class="qr-footer">
        <p style="color: #718096; font-size: 12px; margin: 0;">
            Tunjukkan QR Code ini ke kasir untuk pembayaran tunai
        </p>
        <a href="{{ route('customer.dashboard') }}">← Kembali ke Dashboard</a>
    </div>
</div>

{{-- ============================================ --}}
{{-- SCRIPT --}}
{{-- ============================================ --}}
<script>
    const orderId = '{{ $pending->order_id }}';
    let checkCount = 0;
    const maxChecks = 60;
    let checkInterval = null;
    
    // ============================================
    // CHECK STATUS
    // ============================================
    function checkStatus() {
        const btn = document.getElementById('btnCheckStatus');
        const statusText = document.getElementById('status-text');
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Mengecek...';
        statusText.innerHTML = '⏳ Mengecek status...';
        
        fetch('/cash-qr/status/' + orderId, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Status:', data);
            
            if (data.status === 'paid') {
                statusText.innerHTML = '✅ Pembayaran berhasil dikonfirmasi!';
                document.querySelector('.qr-status').style.background = '#c6f6d5';
                document.querySelector('.qr-status').style.borderColor = '#48bb78';
                showNotification('✅ Pembayaran berhasil!', 'success');
                
                setTimeout(() => {
                    window.location.href = data.redirect || '{{ route("customer.orders") }}';
                }, 1500);
                
            } else if (data.status === 'expired') {
                statusText.innerHTML = '⏰ QR Code sudah kadaluarsa!';
                document.querySelector('.qr-status').style.background = '#fed7d7';
                document.querySelector('.qr-status').style.borderColor = '#fc8181';
                showNotification('⏰ QR Code kadaluarsa', 'error');
                btn.disabled = false;
                btn.innerHTML = '🔄 Cek Status Pembayaran';
                
            } else if (data.status === 'pending') {
                statusText.innerHTML = '⏳ Menunggu konfirmasi kasir...';
                document.querySelector('.qr-status').style.background = '#fefcbf';
                document.querySelector('.qr-status').style.borderColor = '#f6e05e';
                showNotification('⏳ Menunggu konfirmasi kasir...', 'info');
                btn.disabled = false;
                btn.innerHTML = '🔄 Cek Status Pembayaran';
                
            } else {
                statusText.innerHTML = '⚠️ ' + (data.message || 'Status tidak diketahui');
                btn.disabled = false;
                btn.innerHTML = '🔄 Cek Status Pembayaran';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            statusText.innerHTML = '❌ Gagal mengecek status';
            btn.disabled = false;
            btn.innerHTML = '🔄 Cek Status Pembayaran';
            showNotification('❌ Gagal mengecek status', 'error');
        });
    }
    
    // ============================================
    // AUTO CHECK SETIAP 10 DETIK
    // ============================================
    function startAutoCheck() {
        if (checkInterval) clearInterval(checkInterval);
        
        checkInterval = setInterval(() => {
            checkCount++;
            
            // Cek status
            fetch('/cash-qr/status/' + orderId, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'paid') {
                    clearInterval(checkInterval);
                    document.getElementById('status-text').innerHTML = '✅ Pembayaran berhasil dikonfirmasi!';
                    document.querySelector('.qr-status').style.background = '#c6f6d5';
                    document.querySelector('.qr-status').style.borderColor = '#48bb78';
                    showNotification('✅ Pembayaran berhasil!', 'success');
                    
                    setTimeout(() => {
                        window.location.href = '{{ route("customer.orders") }}';
                    }, 1500);
                }
                
                if (checkCount >= maxChecks) {
                    clearInterval(checkInterval);
                }
            })
            .catch(error => {
                console.error('Auto check error:', error);
            });
        }, 10000); // Cek setiap 10 detik
    }
    
    // ============================================
    // COPY ORDER ID
    // ============================================
    function copyOrderId() {
        navigator.clipboard.writeText(orderId).then(() => {
            showNotification('✅ ID Pesanan disalin: ' + orderId, 'success');
        }).catch(() => {
            // Fallback
            const textArea = document.createElement('textarea');
            textArea.value = orderId;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showNotification('✅ ID Pesanan disalin: ' + orderId, 'success');
        });
    }
    
    // ============================================
    // SHOW NOTIFICATION
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
    
    // ============================================
    // START AUTO CHECK
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        console.log('QR Code page loaded for order:', orderId);
        startAutoCheck();
    });
</script>

@endsection