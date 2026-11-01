{{-- resources/views/customer/payment_success.blade.php --}}

@extends('app')
@section('content')
<br><br><br><br>

<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            {{-- Header --}}
            <div class="text-center">
                <div class="text-6xl mb-4"></div>
                <h1 class="text-2xl font-bold text-green-600 mb-2">Pembayaran Berhasil!</h1>
                <p class="text-gray-600 mb-4">Terima kasih, pesanan Anda telah kami terima.</p>
            </div>

            {{-- Detail Pesanan --}}
            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                <div class="flex justify-between items-center border-b pb-2">
                    <span class="text-sm text-gray-500">Nomor Pesanan</span>
                    <span class="font-bold text-lg text-blue-600">{{ $order_id ?? 'N/A' }}</span>
                </div>
                
                <div class="flex justify-between items-center border-b py-2">
                    <span class="text-sm text-gray-500">Tanggal</span>
                    <span class="font-medium">{{ date('d-m-Y H:i', strtotime(now())) }}</span>
                </div>

                <div class="flex justify-between items-center border-b py-2">
                    <span class="text-sm text-gray-500">Metode Pembayaran</span>
                    <span class="font-medium">{{ $orders->first()->payment_method ?? 'Midtrans' }}</span>
                </div>

                <div class="flex justify-between items-center py-2">
                    <span class="text-sm text-gray-500">Status</span>
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">✅ Berhasil</span>
                </div>
            </div>

            {{-- Detail Menu --}}
            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                <h3 class="font-semibold text-gray-700 mb-3">Detail Pesanan</h3>
                
                @if(isset($orders) && $orders->count() > 0)
                    <div class="space-y-2">
                        @foreach($orders as $order)
                            <div class="flex justify-between items-center py-2 border-b border-gray-200 last:border-0">
                                <div class="flex items-center gap-3">
                                    @if($order->menu && $order->menu->gambar)
                                        <img src="{{ asset($order->menu->gambar) }}" 
                                             alt="{{ $order->menu->nama_menu }}" 
                                             class="w-12 h-12 rounded-lg object-cover border border-gray-200">
                                    @else
                                        <div class="w-12 h-12 rounded-lg bg-gray-200 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="text-left">
                                        <p class="font-medium text-gray-800">{{ $order->menu->nama_menu ?? 'Menu tidak tersedia' }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ ucfirst($order->paket) }} 
                                            @if($order->quantity)
                                                × {{ $order->quantity }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <span class="font-semibold text-gray-800">
                                    Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-4 pt-3 border-t-2 border-gray-300">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-700">Total Pembayaran</span>
                            <span class="font-bold text-xl text-green-600">
                                Rp {{ number_format($orders->sum('total_harga'), 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                @else
                    <p class="text-center text-gray-500 py-4">Tidak ada detail pesanan</p>
                @endif
            </div>

            {{-- Tombol Aksi - UKURAN SAMA RATA --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                {{-- Tombol Unduh PDF --}}
                <button onclick="downloadReceipt()" 
                        class="inline-flex items-center justify-center gap-2 px-4 py-3 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition-all duration-200 hover:scale-105 shadow-md text-sm whitespace-nowrap">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Unduh PDF</span>
                </button>

                {{-- Tombol Kirim WhatsApp --}}
                <button onclick="sendWhatsApp()" 
                        class="inline-flex items-center justify-center gap-2 px-4 py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition-all duration-200 hover:scale-105 shadow-md text-sm whitespace-nowrap">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    <span>WhatsApp</span>
                </button>

                {{-- Tombol Lihat Pesanan --}}
                <a href="{{ route('customer.orders') }}" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition-all duration-200 hover:scale-105 shadow-md text-sm whitespace-nowrap">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span>Lihat Pesanan</span>
                </a>

                {{-- Tombol Pesan Lagi --}}
                <a href="{{ route('customer.dashboard') }}" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition-all duration-200 hover:scale-105 shadow-md text-sm whitespace-nowrap">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span>Pesan Lagi</span>
                </a>
            </div>

            <p class="text-xs text-gray-400 text-center mt-4">
                Bukti pembayaran ini akan dikirim ke email Anda
            </p>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- SCRIPT UNTUK DOWNLOAD PDF & KIRIM WA --}}
{{-- ============================================ --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
    // ============================================
    // DATA TRANSAKSI - DARI PHP
    // ============================================
    // Data transaksi dari PHP
    const orderData = {
        order_id: '{{ $order_id ?? "N/A" }}',
        date: '{{ date("d-m-Y H:i", strtotime(now())) }}',
        payment_method: '{{ $orders->first()->payment_method ?? "Midtrans" }}',
        items: [],
        total: '{{ $orders->count() > 0 ? "Rp " . number_format($orders->sum("total_harga"), 0, ",", ".") : "Rp 0" }}',
        total_raw: {{ $orders->sum('total_harga') ?? 0 }}
    };

    @if(isset($orders) && $orders->count() > 0)
        @foreach($orders as $order)
            orderData.items.push({
                name: '{{ $order->menu->nama_menu ?? "Menu" }}',
                paket: '{{ ucfirst($order->paket) }}',
                price: 'Rp {{ number_format($order->total_harga, 0, ",", ".") }}',
                subtotal: {{ $order->total_harga }}
            });
        @endforeach
    @endif

    console.log('Order Data:', orderData);

    // ============================================
    // DOWNLOAD PDF (STRUK VERSION)
    // ============================================
    function downloadReceipt() {
        showNotification('⏳ Sedang membuat struk transaksi...', 'info');

        // Buat element untuk di-render
        const receiptHTML = createReceiptHTML();
        const receiptWindow = window.open('', '_blank', 'width=400,height=700');
        
        if (!receiptWindow) {
            showNotification('⚠️ Harap izinkan popup untuk mengunduh', 'warning');
            return;
        }

        receiptWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Struk Transaksi - {{ $order_id ?? "N/A" }}</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { 
                        font-family: 'Courier New', monospace;
                        padding: 20px;
                        background: #f5f5f5;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        min-height: 100vh;
                    }
                    .receipt {
                        max-width: 320px;
                        width: 100%;
                        margin: 0 auto;
                        padding: 20px 15px;
                        background: white;
                        border: 1px solid #e5e7eb;
                        border-radius: 8px;
                        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    }
                    .header {
                        text-align: center;
                        border-bottom: 2px dashed #333;
                        padding-bottom: 12px;
                        margin-bottom: 12px;
                    }
                    .header .logo {
                        font-size: 24px;
                        margin-bottom: 5px;
                    }
                    .header h1 {
                        font-size: 16px;
                        font-weight: bold;
                        letter-spacing: 2px;
                        margin: 0;
                        color: #22c55e;
                    }
                    .header .sub-title {
                        color: #6b7280;
                        font-size: 11px;
                        margin: 3px 0 0;
                    }
                    .header .line {
                        font-size: 12px;
                        color: #999;
                        margin-top: 5px;
                    }
                    .detail {
                        margin: 10px 0;
                        padding: 10px;
                        background: #f9fafb;
                        border-radius: 4px;
                        font-size: 11px;
                        border-bottom: 1px dashed #ddd;
                    }
                    .detail-item {
                        display: flex;
                        justify-content: space-between;
                        padding: 3px 0;
                    }
                    .detail-item .label {
                        color: #6b7280;
                    }
                    .detail-item .value {
                        font-weight: bold;
                    }
                    .items {
                        margin: 10px 0;
                        border-bottom: 2px dashed #333;
                        padding-bottom: 10px;
                    }
                    .items .item-header {
                        display: flex;
                        justify-content: space-between;
                        font-weight: bold;
                        font-size: 11px;
                        border-bottom: 1px solid #ddd;
                        padding-bottom: 4px;
                        margin-bottom: 4px;
                    }
                    .items .item {
                        display: flex;
                        justify-content: space-between;
                        font-size: 11px;
                        padding: 3px 0;
                    }
                    .items .item .nama {
                        flex: 1;
                    }
                    .items .item .qty {
                        width: 30px;
                        text-align: center;
                    }
                    .items .item .harga {
                        width: 80px;
                        text-align: right;
                    }
                    .items .item-paket {
                        font-size: 10px;
                        color: #999;
                        text-align: right;
                        margin-top: -2px;
                        margin-bottom: 4px;
                    }
                    .total-section {
                        margin: 10px 0;
                        border-bottom: 2px dashed #333;
                        padding-bottom: 10px;
                    }
                    .total-section .row {
                        display: flex;
                        justify-content: space-between;
                        font-size: 12px;
                        padding: 3px 0;
                    }
                    .total-section .row-total {
                        font-size: 16px;
                        font-weight: bold;
                        padding-top: 5px;
                        border-top: 1px solid #ddd;
                    }
                    .total-section .row-total .label {
                        font-size: 14px;
                    }
                    .total-section .row-total .value {
                        font-size: 18px;
                        color: #22c55e;
                    }
                    .payment {
                        margin: 10px 0;
                        border-bottom: 1px dashed #ddd;
                        padding-bottom: 10px;
                    }
                    .payment .row {
                        display: flex;
                        justify-content: space-between;
                        font-size: 12px;
                        padding: 2px 0;
                    }
                    .footer {
                        text-align: center;
                        margin-top: 10px;
                        padding-top: 10px;
                        border-top: 2px dashed #333;
                    }
                    .footer .terima-kasih {
                        font-size: 14px;
                        font-weight: bold;
                        color: #333;
                        margin-bottom: 3px;
                    }
                    .footer .info {
                        font-size: 10px;
                        color: #999;
                        margin-top: 3px;
                    }
                    .footer .tanggal {
                        font-size: 11px;
                        color: #666;
                        margin: 3px 0;
                    }
                    .status-badge {
                        display: inline-block;
                        background: #22c55e;
                        color: white;
                        padding: 2px 10px;
                        border-radius: 4px;
                        font-size: 10px;
                        font-weight: bold;
                    }
                    .print-btn {
                        display: block;
                        width: 100%;
                        margin: 10px auto 5px;
                        padding: 10px;
                        background: #2563eb;
                        color: white;
                        border: none;
                        border-radius: 8px;
                        font-size: 14px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.3s;
                    }
                    .print-btn:hover {
                        background: #1d4ed8;
                        transform: scale(1.02);
                    }
                    .btn-close {
                        display: block;
                        width: 100%;
                        padding: 10px;
                        margin-top: 5px;
                        background: #e5e7eb;
                        color: #374151;
                        border: none;
                        border-radius: 8px;
                        font-size: 14px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.3s;
                    }
                    .btn-close:hover {
                        background: #d1d5db;
                    }
                    .text-muted {
                        color: #9ca3af;
                    }
                    .text-success {
                        color: #22c55e;
                    }
                    @media print {
                        body {
                            background: white;
                            padding: 0;
                            margin: 0;
                        }
                        .receipt {
                            border: none;
                            border-radius: 0;
                            padding: 10px 15px;
                            box-shadow: none;
                        }
                        .no-print {
                            display: none !important;
                        }
                    }
                </style>
            </head>
            <body>
                ${receiptHTML}
                <div class="no-print" style="max-width:320px;width:100%;">
                    <button onclick="window.print()" class="print-btn">
                        🖨️ Cetak / Simpan PDF
                    </button>
                    <p style="color: #9ca3af; font-size: 11px; text-align:center; margin: 5px 0;">
                        Klik tombol di atas, lalu pilih "Save as PDF"
                    </p>
                    <button onclick="window.close()" class="btn-close">
                        ❌ Tutup
                    </button>
                </div>
                <script>
                    window.onload = function() {
                        // Auto print after load (optional)
                        // setTimeout(() => { window.print(); }, 1000);
                    };
                <\/script>
            </body>
            </html>
        `);

        receiptWindow.document.close();
        
        setTimeout(() => {
            const notif = document.getElementById('custom-notification');
            if (notif) notif.remove();
        }, 3000);
    }

    // ============================================
    // CREATE RECEIPT HTML (STRUK VERSION)
    // ============================================
    function createReceiptHTML() {
        let itemsHTML = '';
        let totalItems = 0;
        
        if (orderData.items && orderData.items.length > 0) {
            orderData.items.forEach((item, index) => {
                totalItems += 1;
                itemsHTML += `
                    <div class="item">
                        <span class="nama">${item.name}</span>
                        <span class="qty">1</span>
                        <span class="harga">${item.price}</span>
                    </div>
                    <div class="item-paket">
                        Paket: ${item.paket}
                    </div>
                `;
            });
        } else {
            itemsHTML = '<p style="text-align:center;color:#6b7280;font-size:11px;">Tidak ada item</p>';
        }

        return `
            <div class="receipt" id="receipt-content">
                {{-- HEADER --}}
                <div class="header">
                    <div class="logo">🍽️</div>
                    <h1>BUKTI PEMBAYARAN</h1>
                    <div class="sub-title">Terima kasih telah melakukan pembayaran</div>
                    <div class="line">━━━━━━━━━━━━━━━━━━━━━━</div>
                </div>

                {{-- INFO ORDER --}}
                <div class="detail">
                    <div class="detail-item">
                        <span class="label">Kode Order</span>
                        <span class="value">${orderData.order_id}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Tanggal</span>
                        <span class="value">${orderData.date}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Metode</span>
                        <span class="value">${orderData.payment_method}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Status</span>
                        <span class="value"><span class="status-badge">✅ LUNAS</span></span>
                    </div>
                </div>

                {{-- ITEMS --}}
                <div class="items">
                    <div class="item-header">
                        <span class="nama">Menu</span>
                        <span class="qty">Qty</span>
                        <span class="harga">Total</span>
                    </div>
                    ${itemsHTML}
                </div>

                {{-- TOTAL --}}
                <div class="total-section">
                    <div class="row">
                        <span class="label">Total Bilangan</span>
                        <span class="value">${orderData.total}</span>
                    </div>
                    <div class="row row-total">
                        <span class="label">Total</span>
                        <span class="value">${orderData.total}</span>
                    </div>
                </div>

                {{-- PEMBAYARAN --}}
                <div class="payment">
                    <div class="row">
                        <span class="label">Bayar</span>
                        <span class="value">${orderData.total}</span>
                    </div>
                    <div class="row">
                        <span class="label">Kembali</span>
                        <span class="value">Rp 0</span>
                    </div>
                    <div class="row">
                        <span class="label">Metode</span>
                        <span class="value">${orderData.payment_method}</span>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="footer">
                    <div class="terima-kasih">🙏 Terima Kasih</div>
                    <div class="tanggal">${new Date().toLocaleString('id-ID')}</div>
                    <div class="info">Katering RA-SD-SMP-SMA</div>
                    <div style="font-size: 9px; color: #ccc; margin-top: 5px;">
                        * Struk ini adalah bukti pembayaran resmi *
                    </div>
                </div>
            </div>
        `;
    }

    // ============================================
    // KIRIM KE WHATSAPP
    // ============================================
    function sendWhatsApp() {
        const adminPhone = '6281234567890';
        
        let message = '✅ *BUKTI PEMBAYARAN LAUNDRY* ✅\n\n';
        message += `📌 *Nomor Pesanan:* ${orderData.order_id}\n`;
        message += `📅 *Tanggal:* ${orderData.date}\n`;
        message += `💳 *Metode:* ${orderData.payment_method}\n`;
        message += `📊 *Status:* ✅ Lunas\n\n`;
        message += `*📋 Detail Pesanan:*\n`;
        
        if (orderData.items && orderData.items.length > 0) {
            orderData.items.forEach((item, index) => {
                message += `${index + 1}. ${item.name} (${item.paket}) - ${item.price}\n`;
            });
        }
        
        message += `\n*💰 Total: ${orderData.total}*\n\n`;
        message += `🙏 Terima kasih telah berbelanja di LaundryKita`;

        const encodedMessage = encodeURIComponent(message);
        const waUrl = `https://wa.me/${adminPhone}?text=${encodedMessage}`;
        
        window.open(waUrl, '_blank');
        showNotification('✅ Membuka WhatsApp...', 'success');
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
    // AUTO LOAD
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Payment success page loaded');
        console.log('Order Data:', orderData);
    });
</script>

<style>
    button:hover, a:hover {
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
    
    .shadow-md {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
</style>

@endsection