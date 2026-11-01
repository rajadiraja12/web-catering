{{-- resources/views/customer/receipt_pdf.blade.php --}}

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bukti Transaksi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: 'Courier New', monospace;
            background: white;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        
        .receipt { 
            max-width: 320px;
            width: 100%;
            margin: 0 auto;
            padding: 20px 15px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
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
        
        .status-unpaid {
            background: #f59e0b;
        }
        
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-muted {
            color: #9ca3af;
        }
        .text-success {
            color: #22c55e;
        }
        .fw-bold {
            font-weight: bold;
        }
        .mt-1 {
            margin-top: 4px;
        }
        .mb-1 {
            margin-bottom: 4px;
        }
        
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            .receipt {
                border: none;
                border-radius: 0;
                padding: 10px 15px;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="receipt">
    {{-- HEADER --}}
    <div class="header">
        <div class="logo"></div>
        <h1>BUKTI PEMBAYARAN</h1>
        <div class="sub-title">Terima kasih telah melakukan pembayaran</div>
        <div class="line">━━━━━━━━━━━━━━━━━━━━━━</div>
    </div>

    {{-- INFO ORDER --}}
    <div class="detail">
        <div class="detail-item">
            <span class="label">Kode Order</span>
            <span class="value">{{ $order_id }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Tanggal</span>
            <span class="value">{{ date('d-m-Y H:i') }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Status</span>
            <span class="value">
                <span class="status-badge">LUNAS</span>
            </span>
        </div>
    </div>

    {{-- ITEMS --}}
    <div class="items">
        <div class="item-header">
            <span class="nama">Menu</span>
            <span class="qty">Qty</span>
            <span class="harga">Total</span>
        </div>
        
        @foreach($orders as $order)
        <div class="item">
            <span class="nama">{{ $order->menu->nama_menu ?? 'Menu' }}</span>
            <span class="qty">1</span>
            <span class="harga">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
        </div>
        <div class="item-paket">
            Paket: {{ ucfirst($order->paket) }}
            @if($order->catatan)
                <br>{{ $order->catatan }}
            @endif
        </div>
        @endforeach
    </div>

    {{-- TOTAL --}}
    <div class="total-section">
        <div class="row">
            <span class="label">Total Bilangan</span>
            <span class="value">Rp {{ number_format($total, 0, ',', '.') }}</span>
        </div>
        <div class="row row-total">
            <span class="label">Total</span>
            <span class="value">Rp {{ number_format($total, 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- PEMBAYARAN --}}
    <div class="payment">
        <div class="row">
            <span class="label">Bayar</span>
            <span class="value">Rp {{ number_format($total, 0, ',', '.') }}</span>
        </div>
        <div class="row">
            <span class="label">Kembali</span>
            <span class="value">Rp 0</span>
        </div>
        <div class="row">
            <span class="label">Metode</span>
            <span class="value">{{ $orders->first()->payment_method ?? 'Midtrans' }}</span>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        <div class="terima-kasih">Terima Kasih</div>
        <div class="tanggal">{{ date('d-m-Y H:i:s') }}</div>
        <div class="info">Katering SDIT-SMPIT-SMAIT</div>
        <div style="font-size: 9px; color: #ccc; margin-top: 5px;">
            * Struk ini adalah bukti pembayaran resmi *
        </div>
    </div>
</div>

</body>
</html>