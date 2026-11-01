{{-- resources/views/admin/cashier/scan.blade.php --}}

@extends('app')
@include('layout.cashier')
@section('content')
@yield('nav')
<br><br><br><br>
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            {{-- Header --}}
            <div class="text-center mb-6">
                <div class="text-5xl mb-2"></div>
                <h1 class="text-2xl font-bold text-gray-800">Scan QR Code Pembayaran</h1>
                <p class="text-sm text-gray-500">Arahkan kamera ke QR Code customer untuk konfirmasi pembayaran</p>
            </div>

            {{-- QR Scanner --}}
            <div class="relative bg-black rounded-xl overflow-hidden aspect-square max-w-md mx-auto">
                <div id="reader" class="w-full h-full"></div>
                
                {{-- Overlay --}}
                <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                    <div class="w-48 h-48 border-2 border-blue-500 rounded-lg"></div>
                </div>
                
                {{-- Loading --}}
                <div id="scanner-loading" class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                    <div class="text-white text-center">
                        <svg class="w-12 h-12 animate-spin mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p>Mengaktifkan kamera...</p>
                    </div>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex gap-3 justify-center mt-6">
                <button onclick="startScanner()" 
                        class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Start Scanner
                </button>
                
                <button onclick="stopScanner()" 
                        class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Stop
                </button>

                <button onclick="manualInput()" 
                        class="px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Input Manual
                </button>
            </div>

            {{-- Manual Input --}}
            <div id="manual-input" class="mt-4 hidden">
                <div class="flex gap-2">
                    <input type="text" id="manual-order-id" 
                           placeholder="Masukkan kode order (contoh: ORD-20260716-XXXXXX)"
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <button onclick="checkManualOrder()" 
                            class="px-6 py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition-all">
                        Cek
                    </button>
                </div>
            </div>

            {{-- Hasil Scan --}}
            <div id="scan-result" class="mt-6 hidden">
                <div class="p-4 rounded-xl border-2">
                    <div id="scan-result-content"></div>
                </div>
            </div>

            {{-- Informasi --}}
            <div class="mt-6 p-4 bg-blue-50 rounded-xl">
                <h4 class="font-semibold text-blue-800 mb-2">Panduan:</h4>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Arahkan kamera ke QR Code yang ditampilkan customer</li>
                    <li>• Scanner akan otomatis mendeteksi dan memproses</li>
                    <li>• Pastikan QR Code tidak buram atau rusak</li>
                    <li>• Jika gagal, gunakan input manual</li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- HTML5 QR Code Library --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
    let html5QrCode = null;
    let isScanning = false;

    // ============================================
    // START SCANNER
    // ============================================
    function startScanner() {
        const loading = document.getElementById('scanner-loading');
        loading.classList.remove('hidden');
        
        const readerElement = document.getElementById('reader');
        readerElement.innerHTML = '';
        
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                html5QrCode.clear();
                initializeScanner();
            }).catch(err => {
                console.error('Error stopping scanner:', err);
                initializeScanner();
            });
        } else {
            initializeScanner();
        }
    }

    function initializeScanner() {
        html5QrCode = new Html5Qrcode("reader");
        
        const config = {
            fps: 10,
            qrbox: { width: 200, height: 200 },
            aspectRatio: 1.0
        };

        html5QrCode.start(
            { facingMode: "environment" },
            config,
            onScanSuccess,
            onScanError
        )
        .then(() => {
            isScanning = true;
            document.getElementById('scanner-loading').classList.add('hidden');
            showNotification('📷 Scanner aktif, arahkan ke QR Code', 'success');
        })
        .catch(err => {
            document.getElementById('scanner-loading').classList.add('hidden');
            console.error('Error starting scanner:', err);
            
            // Fallback ke kamera belakang
            html5QrCode.start(
                { facingMode: "user" },
                config,
                onScanSuccess,
                onScanError
            )
            .then(() => {
                isScanning = true;
                document.getElementById('scanner-loading').classList.add('hidden');
                showNotification('📷 Scanner aktif (kamera depan)', 'success');
            })
            .catch(err2 => {
                showNotification('❌ Gagal mengakses kamera: ' + err2.message, 'error');
            });
        });
    }

    // ============================================
    // ON SCAN SUCCESS
    // ============================================
    function onScanSuccess(decodedText, decodedResult) {
        console.log('QR Scanned:', decodedText);
        
        // Parse QR data
        try {
            const data = JSON.parse(decodedText);
            if (data.order_id && data.type === 'cash_payment') {
                // Berhenti scan sementara
                if (html5QrCode) {
                    html5QrCode.stop();
                    isScanning = false;
                }
                
                // Redirect ke halaman konfirmasi
                window.location.href = '/kasir/confirm/' + data.order_id;
            } else {
                showNotification('❌ QR Code tidak valid untuk pembayaran cash', 'error');
                // Lanjutkan scan
                if (!isScanning && html5QrCode) {
                    html5QrCode.resume();
                    isScanning = true;
                }
            }
        } catch (e) {
            // Jika bukan JSON, coba cek sebagai order ID langsung
            if (decodedText.startsWith('ORD-')) {
                if (html5QrCode) {
                    html5QrCode.stop();
                    isScanning = false;
                }
                window.location.href = '/kasir/confirm/' + decodedText;
            } else {
                showNotification('❌ QR Code tidak dikenali', 'error');
                if (!isScanning && html5QrCode) {
                    html5QrCode.resume();
                    isScanning = true;
                }
            }
        }
    }

    // ============================================
    // ON SCAN ERROR
    // ============================================
    function onScanError(err) {
        // Ignore - terus scan
    }

    // ============================================
    // STOP SCANNER
    // ============================================
    function stopScanner() {
        if (html5QrCode && isScanning) {
            html5QrCode.stop().then(() => {
                isScanning = false;
                showNotification('⏸️ Scanner dihentikan', 'warning');
            }).catch(err => {
                console.error('Error stopping scanner:', err);
            });
        }
    }

    // ============================================
    // MANUAL INPUT
    // ============================================
    function manualInput() {
        const input = document.getElementById('manual-input');
        input.classList.toggle('hidden');
        if (!input.classList.contains('hidden')) {
            document.getElementById('manual-order-id').focus();
        }
    }

    function checkManualOrder() {
        const orderId = document.getElementById('manual-order-id').value.trim();
        if (!orderId) {
            showNotification('⚠️ Masukkan kode order', 'warning');
            return;
        }
        
        if (!orderId.startsWith('ORD-')) {
            showNotification('⚠️ Format kode order tidak valid', 'error');
            return;
        }
        
        window.location.href = '/kasir/confirm/' + orderId;
    }

    // Enter key untuk manual input
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('manual-order-id').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                checkManualOrder();
            }
        });
    });

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
</script>

<style>
    #reader {
        min-height: 300px;
    }
    #reader video {
        border-radius: 12px;
    }
</style>

@endsection