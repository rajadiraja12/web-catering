{{-- resources/views/customer/order.blade.php --}}
@extends('app')
@include('layout.customer')
@section('content')
@yield('nav')
<br>
<br>
<br>
<br>
<br>

{{-- Header --}}
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Pesanan Saya</h1>
        <p class="text-sm text-gray-500">Daftar semua pesanan yang telah Anda buat</p>
    </div>
    <a href="{{ route('customer.dashboard') }}" 
        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-sm rounded-lg transition-all duration-200">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali
    </a>
</div>

{{-- Tabel Order --}}
<div class="relative overflow-x-auto bg-white shadow-sm rounded-2xl border border-gray-200">
    <table class="w-full text-sm text-left rtl:text-right text-gray-700">
        <thead class="text-xs text-gray-800 uppercase bg-blue-200 from-blue-600 to-blue-800 border-b border-blue-900">
            <tr>
                <th scope="col" class="px-6 py-4 text-center w-16">No</th>
                <th scope="col" class="px-6 py-4 whitespace-nowrap">Kode Order</th>
                <th scope="col" class="px-6 py-4 whitespace-nowrap">Tanggal Pesan</th>
                <th scope="col" class="px-6 py-4 whitespace-nowrap">Tanggal Menu</th>
                <th scope="col" class="px-6 py-4">Nama Menu</th>
                <th scope="col" class="px-6 py-4 text-center">Paket</th>
                <th scope="col" class="px-6 py-4 text-right">Harga</th>
                <th scope="col" class="px-6 py-4 text-center">Status</th>
                <th scope="col" class="px-6 py-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $index => $order)
            <tr class="{{ $loop->iteration % 2 == 0 ? 'bg-gray-50' : 'bg-white' }} border-b border-gray-200 hover:bg-blue-50 transition-colors duration-200">
                <td class="px-6 py-4 text-center font-medium text-gray-900">
                    {{ $loop->iteration + (($orders->currentPage() - 1) * $orders->perPage()) }}
                </td>
                <td class="px-6 py-4 font-mono text-xs font-medium text-gray-800">
                    {{ $order->kode_order }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    {{ $order->created_at ? date('d-m-Y', strtotime($order->created_at)) : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    {{ $order->tanggal_menu ? date('d-m-Y', strtotime($order->tanggal_menu)) : '-' }}
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        @if($order->menu && $order->menu->gambar)
                            <img src="{{ asset($order->menu->gambar) }}" alt="{{ $order->menu->nama_menu }}" 
                                class="w-10 h-10 rounded-lg object-cover border border-gray-200">
                        @else
                            <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                        @endif
                        <div>
                            <p class="font-medium text-gray-800">{{ $order->menu->nama_menu ?? 'Menu tidak tersedia' }}</p>
                            @if($order->menu)
                                <p class="text-xs text-gray-500">{{ Str::limit($order->menu->deskripsi ?? '', 30) }}</p>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-center">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium 
                        {{ $order->paket == 'kecil' ? 'bg-blue-100 text-blue-700' : ($order->paket == 'sedang' ? 'bg-purple-100 text-purple-700' : 'bg-orange-100 text-orange-700') }}">
                        <span class="w-1.5 h-1.5 rounded-full 
                            {{ $order->paket == 'kecil' ? 'bg-blue-500' : ($order->paket == 'sedang' ? 'bg-purple-500' : 'bg-orange-500') }}"></span>
                        {{ ucfirst($order->paket ?? 'Standar') }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right font-semibold text-blue-600">
                    Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                    <br>
                    <span class="text-xs text-gray-500 font-normal">@ Rp {{ number_format($order->harga_satuan, 0, ',', '.') }}</span>
                </td>
                <td class="px-6 py-4 text-center">
                    @if($order->payment_status == 'paid')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                            Lunas
                        </span>
                    @elseif($order->payment_status == 'unpaid')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                            <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full"></span>
                            Hutang
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                            Dibatalkan
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 text-center">
                    {{-- Tombol Aksi --}}
                    <div class="flex items-center justify-center gap-2">
                        @if($order->payment_status == 'paid')
                            {{-- Download Bukti --}}
                            <button onclick="downloadReceipt('{{ $order->kode_order }}')" 
                                    class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded-lg transition-all duration-200"
                                    title="Download Bukti">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </button>
                            
                            {{-- Kirim WhatsApp --}}
                            <button onclick="sendWhatsAppOrder('{{ $order->kode_order }}', '{{ $order->menu->nama_menu ?? 'Menu' }}', '{{ $order->paket }}', {{ $order->total_harga }})" 
                                    class="p-2 text-green-600 hover:text-green-800 hover:bg-green-100 rounded-lg transition-all duration-200"
                                    title="Kirim ke WhatsApp">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                            </button>
                        @elseif($order->payment_status == 'unpaid')
                            {{-- Download Bukti --}}
                            <button onclick="downloadReceipt('{{ $order->kode_order }}')" 
                                    class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded-lg transition-all duration-200"
                                    title="Download Bukti">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </button>
                            
                            {{-- Kirim WhatsApp --}}
                            <button onclick="sendWhatsAppOrder('{{ $order->kode_order }}', '{{ $order->menu->nama_menu ?? 'Menu' }}', '{{ $order->paket }}', {{ $order->total_harga }})" 
                                    class="p-2 text-green-600 hover:text-green-800 hover:bg-green-100 rounded-lg transition-all duration-200"
                                    title="Kirim ke WhatsApp">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="w-20 h-20 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-lg font-medium text-gray-600">Belum ada pesanan</p>
                        <p class="text-sm text-gray-400 mt-1">Silakan pesan menu terlebih dahulu</p>
                        <a href="{{ route('customer.dashboard') }}" 
                            class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-lg transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Pesan Sekarang
                        </a>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between p-4 bg-gray-50 border-t border-gray-200 rounded-b-2xl">
        <span class="text-sm font-normal text-gray-700 mb-4 md:mb-0 block w-full md:inline md:w-auto">
            Menampilkan <span class="font-semibold text-gray-900">{{ $orders->firstItem() ?? 0 }}</span>-<span class="font-semibold text-gray-900">{{ $orders->lastItem() ?? 0 }}</span> 
            dari <span class="font-semibold text-gray-900">{{ $orders->total() }}</span> pesanan
        </span>
        <div>
            {{ $orders->links() }}
        </div>
    </nav>
</div>

{{-- ============================================ --}}
{{-- SCRIPT UNTUK AKSI ORDER --}}
{{-- ============================================ --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
    // ============================================
    // DOWNLOAD BUKTI PER ITEM
    // ============================================
    function downloadReceipt(orderCode) {
        showNotification('⏳ Membuat bukti transaksi...', 'info');
        
        // Cari baris berdasarkan kode order - cara yang lebih kompatibel
        let row = null;
        const allRows = document.querySelectorAll('tbody tr');
        
        for (let i = 0; i < allRows.length; i++) {
            const cells = allRows[i].querySelectorAll('td');
            if (cells.length >= 2) {
                // Cek di kolom kode order (kolom ke-2, index 1)
                const kodeOrder = cells[1]?.textContent?.trim() || '';
                if (kodeOrder === orderCode) {
                    row = allRows[i];
                    break;
                }
            }
        }
        
        if (!row) {
            showNotification('❌ Data tidak ditemukan', 'error');
            return;
        }
        
        // Ambil data dari baris
        const cells = row.querySelectorAll('td');
        
        // Kolom: 0=No, 1=Kode Order, 2=Tanggal Pesan, 3=Tanggal Menu, 4=Nama Menu, 5=Paket, 6=Harga, 7=Status, 8=Aksi
        const menuName = cells[4]?.querySelector('.font-medium')?.textContent || 'Menu';
        const paketEl = cells[5]?.querySelector('span');
        const paket = paketEl ? paketEl.textContent.trim() : 'Standar';
        const priceText = cells[6]?.textContent?.trim() || 'Rp 0';
        const date = cells[2]?.textContent?.trim() || '-';
        const statusEl = cells[7]?.querySelector('span');
        const status = statusEl ? statusEl.textContent.trim() : 'Lunas';
        
        // Bersihkan price (ambil angka saja)
        const priceMatch = priceText.match(/Rp\s*([\d,.]+)/);
        const price = priceMatch ? priceMatch[0] : priceText;
        
        // Buat HTML receipt
        const receiptHTML = `
            <div style="max-width: 500px; margin: 0 auto; padding: 30px; font-family: Arial, sans-serif; border: 2px solid #e5e7eb; border-radius: 12px; background: white;">
                <div style="text-align: center; border-bottom: 2px solid #22c55e; padding-bottom: 20px; margin-bottom: 20px;">
                    <h1 style="color: #22c55e; font-size: 24px;">✅ BUKTI PEMBAYARAN</h1>
                    <p style="color: #6b7280; font-size: 14px;">Terima kasih telah melakukan pembayaran</p>
                </div>
                
                <div style="margin: 20px 0; padding: 15px; background: #f9fafb; border-radius: 8px;">
                    <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #e5e7eb;">
                        <span style="color: #6b7280;">Nomor Pesanan</span>
                        <strong>${orderCode}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #e5e7eb;">
                        <span style="color: #6b7280;">Tanggal</span>
                        <strong>${date}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 5px 0;">
                        <span style="color: #6b7280;">Status</span>
                        <strong style="color: #22c55e;">${status}</strong>
                    </div>
                </div>
                
                <div style="margin: 20px 0;">
                    <h3 style="margin-bottom: 15px; color: #374151;">📋 Detail Pesanan</h3>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                        <div>
                            <div style="font-weight: 500;">${menuName}</div>
                            <div style="font-size: 12px; color: #6b7280;">${paket}</div>
                        </div>
                        <div style="font-weight: 600;">${price}</div>
                    </div>
                </div>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #22c55e; display: flex; justify-content: space-between; font-size: 18px; font-weight: bold;">
                    <span>Total Pembayaran</span>
                    <span style="color: #22c55e;">${price}</span>
                </div>
                
                <div style="margin-top: 30px; text-align: center; color: #9ca3af; font-size: 12px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
                    <p>Dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
                    <p>Terima kasih telah berbelanja di LaundryKita</p>
                </div>
            </div>
        `;
        
        // Buka popup untuk print
        const receiptWindow = window.open('', '_blank', 'width=800,height=600');
        if (!receiptWindow) {
            showNotification('⚠️ Harap izinkan popup untuk mengunduh', 'warning');
            return;
        }
        
        receiptWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Bukti Transaksi - ${orderCode}</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { 
                        font-family: Arial, sans-serif; 
                        padding: 40px;
                        background: #f3f4f6;
                    }
                    .print-btn {
                        display: block;
                        margin: 20px auto;
                        padding: 12px 40px;
                        background: #3b82f6;
                        color: white;
                        border: none;
                        border-radius: 8px;
                        font-size: 16px;
                        cursor: pointer;
                        transition: background 0.3s;
                    }
                    .print-btn:hover {
                        background: #2563eb;
                    }
                    @media print {
                        .no-print { display: none !important; }
                        body { padding: 20px; background: white; }
                    }
                </style>
            </head>
            <body>
                ${receiptHTML}
                <div style="text-align: center; margin-top: 20px;" class="no-print">
                    <button onclick="window.print()" class="print-btn">
                        🖨️ Cetak / Simpan PDF
                    </button>
                    <p style="color: #9ca3af; font-size: 12px; margin-top: 10px;">
                        Klik tombol di atas, lalu pilih "Save as PDF"
                    </p>
                    <button onclick="window.close()" class="print-btn" style="background: #6b7280; margin-top: 10px;">
                        ❌ Tutup
                    </button>
                </div>
                <script>
                    // Auto print setelah load
                    window.onload = function() {
                        setTimeout(function() {
                            window.print();
                        }, 800);
                    };
                <\/script>
            </body>
            </html>
        `);
        receiptWindow.document.close();
        
        setTimeout(() => {
            const notif = document.getElementById('custom-notification');
            if (notif) notif.remove();
        }, 2000);
    }

    // ============================================
    // KIRIM KE WHATSAPP PER ITEM
    // ============================================
    function sendWhatsAppOrder(orderCode, menuName, paket, price) {
        // Ganti dengan nomor admin
        const adminPhone = '{{ $user->no_hp }}';
        
        const formattedPrice = 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
        const date = new Date().toLocaleString('id-ID');
        
        const message = `✅ *BUKTI PEMBAYARAN KATERING* ✅

📌 *Nomor Pesanan:* ${orderCode}
📅 *Tanggal:* ${date}
📊 *Status:* ✅ Lunas

*📋 Detail Pesanan:*
${menuName} (${paket}) - ${formattedPrice}

*💰 Total: ${formattedPrice}*

🙏 Terima kasih telah berbelanja di Hanif Katering`;
        
        const encodedMessage = encodeURIComponent(message);
        const waUrl = `https://wa.me/${adminPhone}?text=${encodedMessage}`;
        
        window.open(waUrl, '_blank');
        showNotification('✅ Membuka WhatsApp...', 'success');
    }

    // ============================================
    // BAYAR ORDER
    // ============================================
    function payOrder(orderId) {
        if (!confirm('Konfirmasi pembayaran untuk pesanan ini?')) return;
        
        showNotification('⏳ Memproses pembayaran...', 'info');
        
        fetch(`/customer/order/${orderId}/pay`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('✅ Pembayaran berhasil!', 'success');
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
    // BATAL ORDER
    // ============================================
    function cancelOrder(orderId) {
        if (!confirm('Yakin ingin membatalkan pesanan ini?')) return;
        
        showNotification('⏳ Membatalkan pesanan...', 'info');
        
        fetch(`/customer/order/${orderId}/cancel`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('✅ Pesanan dibatalkan', 'success');
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

    console.log('✅ Order page loaded');
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

/* Line clamp */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Tombol aksi hover */
td .flex .p-2 {
    transition: all 0.2s ease;
}

td .flex .p-2:hover {
    transform: scale(1.1);
}

/* Responsive tabel */
@media (max-width: 768px) {
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    td .flex {
        flex-wrap: wrap;
        gap: 4px;
    }
    
    td .flex .p-2 {
        padding: 6px;
    }
}
</style>

@endsection