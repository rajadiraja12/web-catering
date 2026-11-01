{{-- resources/views/customer/cash_qr_print.blade.php --}}

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print QR Code - {{ $pending->order_id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #f7fafc;
            padding: 20px;
        }
        
        .print-container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #4299e1;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            color: #2d3748;
        }
        
        .header p {
            color: #718096;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .qr-image {
            text-align: center;
            padding: 20px;
            background: #f7fafc;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .qr-image svg {
            max-width: 100%;
            height: auto;
        }
        
        .qr-image img {
            max-width: 100%;
            height: auto;
        }
        
        .info {
            border-top: 2px solid #e2e8f0;
            padding-top: 20px;
            margin-top: 20px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 14px;
        }
        
        .info-item .label {
            color: #4a5568;
        }
        
        .info-item .value {
            font-weight: 600;
            color: #2d3748;
        }
        
        .total {
            font-size: 20px;
            color: #48bb78;
            font-weight: 700;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
            color: #a0aec0;
            font-size: 12px;
        }
        
        .no-print {
            text-align: center;
            margin-top: 20px;
        }
        
        .btn-print {
            padding: 12px 40px;
            background: #4299e1;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .btn-print:hover {
            background: #3182ce;
        }
        
        .btn-close {
            padding: 12px 40px;
            background: #718096;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-left: 10px;
        }
        
        .btn-close:hover {
            background: #4a5568;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                padding: 0 !important;
            }
            .print-container {
                box-shadow: none !important;
                padding: 20px !important;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="header">
            <h1>BUKTI PEMBAYARAN TUNAI</h1>
            <p>Scan QR Code untuk konfirmasi pembayaran</p>
        </div>
        
        <div class="qr-image">
            @if($pending->qr_image)
                @if(strpos($pending->qr_image, 'data:image') === 0)
                    {{-- Jika base64 image --}}
                    <img src="{{ $pending->qr_image }}" alt="QR Code" style="max-width: 100%; height: auto;">
                @else
                    {{-- Jika SVG langsung --}}
                    {!! $pending->qr_image !!}
                @endif
            @else
                <p style="color: #718096;">QR Code tidak tersedia</p>
            @endif
        </div>
        
        <div class="info">
            <div class="info-item">
                <span class="label">Nomor Pesanan</span>
                <span class="value">{{ $pending->order_id }}</span>
            </div>
            <div class="info-item">
                <span class="label">Customer</span>
                {{-- Gunakan $user dari controller --}}
                <span class="value">{{ $user->name ?? session('user_name', 'Customer') }}</span>
            </div>
            <div class="info-item">
                <span class="label">Tanggal</span>
                <span class="value">{{ date('d-m-Y H:i') }}</span>
            </div>
            <div class="info-item" style="border-top: 2px solid #e2e8f0; padding-top: 12px; margin-top: 6px;">
                <span class="label" style="font-size: 16px; font-weight: 600;">Total</span>
                <span class="value total">Rp {{ number_format($pending->total, 0, ',', '.') }}</span>
            </div>
            <div class="info-item">
                <span class="label">Berlaku hingga</span>
                <span class="value" style="color: #e53e3e;">
                    {{ $pending->expires_at ? date('d-m-Y H:i', strtotime($pending->expires_at)) : '24 jam' }}
                </span>
            </div>
        </div>
        
        <div class="footer">
            <p>Terima kasih telah berbelanja di LaundryKita</p>
            <p style="margin-top: 4px;">QR Code ini berlaku 1x scan</p>
        </div>
    </div>
    
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">Print QR Code</button>
        <button onclick="window.close()" class="btn-close">Tutup</button>
    </div>
    
    <script>
        // Auto print after load
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 1000);
        };
    </script>
</body>
</html>