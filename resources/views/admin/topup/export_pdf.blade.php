{{-- resources/views/admin/topup/export_pdf.blade.php --}}

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan TopUp</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 12px;
        }
        .stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .stats-item {
            text-align: center;
        }
        .stats-item .label {
            font-size: 10px;
            color: #666;
        }
        .stats-item .value {
            font-size: 16px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background: #4a5568;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        td {
            padding: 6px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .status-pending {
            color: #d69e2e;
        }
        .status-verified {
            color: #38a169;
        }
        .status-rejected {
            color: #e53e3e;
        }
        .status-expired {
            color: #718096;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        @page {
            margin: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN TOPUP SALDO</h1>
        <p>Periode: {{ request('date_from') ?? 'Semua' }} - {{ request('date_to') ?? 'Semua' }}</p>
        <p>Dicetak: {{ date('d-m-Y H:i:s') }}</p>
    </div>

    <div class="stats">
        <div class="stats-item">
            <div class="label">Total Transaksi</div>
            <div class="value">{{ $statistics['total'] }}</div>
        </div>
        <div class="stats-item">
            <div class="label">Diverifikasi</div>
            <div class="value" style="color: #38a169;">{{ $statistics['verified'] }}</div>
        </div>
        <div class="stats-item">
            <div class="label">Pending</div>
            <div class="value" style="color: #d69e2e;">{{ $statistics['pending'] }}</div>
        </div>
        <div class="stats-item">
            <div class="label">Total Nominal</div>
            <div class="value" style="color: #2b6cb0;">Rp {{ number_format($statistics['total_nominal'], 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode TopUp</th>
                <th>Customer</th>
                <th>Nominal</th>
                <th>Metode</th>
                <th>Status</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topups as $index => $topup)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $topup->kode_topup }}</td>
                <td>{{ $topup->user->name ?? 'Unknown' }}</td>
                <td class="text-right">Rp {{ number_format($topup->nominal, 0, ',', '.') }}</td>
                <td class="text-center">{{ $topup->metode_pembayaran == 'midtrans' ? 'Midtrans' : 'Saldo' }}</td>
                <td class="text-center">
                    @if($topup->status == 'pending')
                        <span class="status-pending">⏳ Pending</span>
                    @elseif($topup->status == 'verified')
                        <span class="status-verified">✅ Diverifikasi</span>
                    @elseif($topup->status == 'rejected')
                        <span class="status-rejected">❌ Ditolak</span>
                    @else
                        <span class="status-expired">Kadaluarsa</span>
                    @endif
                </td>
                <td class="text-center">{{ $topup->created_at->format('d-m-Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem</p>
        <p>&copy; {{ date('Y') }} - Sistem Keuangan</p>
    </div>
</body>
</html>