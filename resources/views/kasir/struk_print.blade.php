{{-- resources/views/kasir/struk_print.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - {{ $data['order']->kode_order }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace;
        }

        body {
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 10px;
        }

        /* Container tampilan di layar laptop */
        .struk-container {
            background: #ffffff;
            width: 280px;
            padding: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            color: #000000;
        }

        .header { text-align: center; margin-bottom: 6px; }
        .header .nama-toko { font-size: 15px; font-weight: bold; }
        .header .alamat { font-size: 11px; margin-top: 2px; }
        .header .telp { font-size: 11px; }

        .divider {
            border-bottom: 1px dashed #000;
            margin: 6px 0;
            width: 100%;
        }

        .info-order { font-size: 11px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .row .label { color: #000; }
        .row .value { font-weight: bold; text-align: right; }

        .items { font-size: 11px; margin: 4px 0; }
        .item-header { font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 2px; margin-bottom: 4px; }
        .item-row { margin-bottom: 4px; }
        .item-title { font-weight: bold; }
        .item-details { display: flex; justify-content: space-between; font-size: 10px; }
        .item-subtext { font-size: 9px; font-style: italic; }

        .total-section, .payment { font-size: 11px; }
        .row-grand-total { font-size: 13px; font-weight: bold; margin-top: 4px; padding-top: 4px; border-top: 1px dashed #000; }

        .footer { text-align: center; font-size: 10px; margin-top: 8px; }
        .footer .terima-kasih { font-size: 12px; font-weight: bold; }

        .action-buttons { width: 280px; margin-top: 15px; }
        .btn-print { display: block; width: 100%; padding: 10px; background: #2563eb; color: white; border: none; border-radius: 6px; font-size: 14px; font-weight: bold; cursor: pointer; margin-bottom: 6px; }
        .btn-close { display: block; width: 100%; padding: 8px; background: #9ca3af; color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer; }

        /* ============================================================
           FIX PERFORMA UNTUK PRINTER RP58 / THERMAL 58MM
           ============================================================ */
        @media print {
    /* Set ukuran halaman pas dengan lebar 58mm dan tinggi fleksibel */
    @page {
        size: 58mm auto !important;
        margin: 0 !important;
    }

    html, body {
        width: 58mm !important;
        height: max-content !important; /* Paksa tinggi hanya seukuran isi konten */
        min-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        overflow: hidden !important;
    }

    .struk-container {
        width: 58mm !important;
        max-width: 58mm !important;
        height: auto !important;
        min-height: 0 !important;
        padding: 2mm 3mm !important;
        margin: 0 !important;
        box-shadow: none !important;
        box-sizing: border-box !important;
        
        /* Mencegah pemotongan halaman / ruang kosong otomatis */
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        page-break-after: avoid !important;
        break-after: avoid !important;
    }

    .struk-content {
        width: 100% !important;
        height: auto !important;
    }

    /* Sembunyikan elemen non-cetak */
    .no-print, .action-buttons {
        display: none !important;
    }
}
    </style>
</head>
<body>

<div class="struk-container">
    <div class="struk-content">
        
        {{-- HEADER --}}
        <div class="header">
            <div class="nama-toko">Hanif Catering</div>
            <div class="alamat">Gedung KBSA, Kompleks Pendidikan Yayasan Al Hanif, Cilegon</div>
            <div class="telp">Telp/WA: 0896-7570-4781</div>
        </div>

        <div class="divider"></div>

        {{-- INFO ORDER --}}
        <div class="info-order">
            <div class="row">
                <span class="label">Kode:</span>
                <span class="value">{{ $data['order']->kode_order }}</span>
            </div>
            <div class="row">
                <span class="label">Tgl:</span>
                <span class="value">{{ $data['tanggal'] }}</span>
            </div>
            <div class="row">
                <span class="label">Cust:</span>
                <span class="value">{{ $data['customer']->name ?? 'Unknown' }}</span>
            </div>
            <div class="row">
                <span class="label">Kelas:</span>
                <span class="value">{{ $data['customer']->kelas ?? '-' }}</span>
            </div>
            <div class="row">
                <span class="label">Status:</span>
                <span class="value">
                    @if(($data['order']->payment_status ?? '') == 'paid')
                        LUNAS
                    @else
                        BELUM BAYAR
                    @endif
                </span>
            </div>
        </div>

        <div class="divider"></div>

        {{-- ITEMS --}}
        <div class="items">
            <div class="row item-header">
                <span>Item</span>
                <span>Total</span>
            </div>
            
            @php
                $paketNama = [
                    'best' => 'BEST',
                    'happy' => 'HAPPY',
                    'jumbo' => 'JUMBO',
                    'smart' => 'SMART'
                ];
            @endphp

            <div class="item-row">
                <div class="item-title">{{ $data['menu']->nama_menu ?? 'Menu Catering' }}</div>
                <div class="item-details">
                    <span>1 x {{ number_format($data['order']->total_harga, 0, ',', '.') }}</span>
                    <span>Rp {{ number_format($data['order']->total_harga, 0, ',', '.') }}</span>
                </div>
                <div class="item-subtext">
                    Paket: {{ $paketNama[$data['order']->paket] ?? $data['order']->paket }}
                    @if(!empty($data['order']->catatan))
                        <br>Cat: {{ $data['order']->catatan }}
                    @endif
                </div>
            </div>
        </div>

        <div class="divider"></div>

        {{-- TOTAL --}}
        <div class="total-section">
            <div class="row row-grand-total">
                <span class="label">TOTAL:</span>
                <span class="value">Rp {{ number_format($data['total'], 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- PEMBAYARAN --}}
        <div class="payment">
            <div class="row">
                <span class="label">Bayar:</span>
                <span class="value">Rp {{ number_format($data['bayar'], 0, ',', '.') }}</span>
            </div>
            <div class="row">
                <span class="label">Kembali:</span>
                <span class="value">Rp {{ number_format($data['kembali'], 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="divider"></div>

        {{-- FOOTER --}}
        <div class="footer">
            <div class="terima-kasih">TERIMA KASIH</div>
            <div>Katering SDIT-SMPIT-SMAIT</div>
            <div style="font-size: 8px; margin-top: 3px;">*** Bukti Pembayaran Resmi ***</div>
        </div>

    </div>
</div>

{{-- TOMBOL PRINT --}}
<div class="action-buttons no-print">
    <button onclick="printStruk()" class="btn-print">Cetak Struk</button>
    <button onclick="window.close()" class="btn-close">Tutup</button>
</div>

<script>
function printStruk() {
    window.print();
}
</script>

</body>
</html>