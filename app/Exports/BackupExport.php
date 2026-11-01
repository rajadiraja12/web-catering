<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\User;
use App\Models\Report;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BackupExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $type;

    public function __construct($startDate, $endDate, $type = 'manual')
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->type = $type;
    }

    /**
     * Data yang akan diexport
     */
    public function array(): array
    {
        $data = [];

        // Header Ringkasan
        $data[] = ['=== RINGKASAN KEUANGAN ' . $this->startDate . ' s/d ' . $this->endDate . ' ==='];
        $data[] = [];

        // Statistik
        $totalOrders = Order::whereBetween('created_at', [$this->startDate, $this->endDate])->count();
        $totalRevenue = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->sum('total_harga');
        $totalUnpaid = Order::where('payment_status', 'unpaid')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->sum('total_harga');

        $data[] = ['Total Order', $totalOrders];
        $data[] = ['Total Pendapatan (Paid)', 'Rp ' . number_format($totalRevenue, 0, ',', '.')];
        $data[] = ['Total Belum Dibayar (Unpaid)', 'Rp ' . number_format($totalUnpaid, 0, ',', '.')];
        $data[] = [];

        // Detail Laporan
        $data[] = ['=== DETAIL LAPORAN KEUANGAN ==='];
        $data[] = [];

        $reports = Report::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($reports->count() > 0) {
            $data[] = ['Tanggal', 'Keterangan', 'Debit (Masuk)', 'Kredit (Keluar)', 'Saldo', 'Kategori'];
            foreach ($reports as $report) {
                $data[] = [
                    $report->tanggal_laporan,
                    $report->keterangan,
                    'Rp ' . number_format($report->debit, 0, ',', '.'),
                    'Rp ' . number_format($report->kredit, 0, ',', '.'),
                    'Rp ' . number_format($report->saldo, 0, ',', '.'),
                    $report->kategori,
                ];
            }
        } else {
            $data[] = ['Tidak ada data laporan keuangan untuk periode ini'];
        }

        $data[] = [];

        // Detail Order
        $data[] = ['=== DETAIL ORDER ==='];
        $data[] = [];

        $orders = Order::with(['customer', 'menu'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($orders->count() > 0) {
            $data[] = ['Kode Order', 'Tanggal', 'Customer', 'Kelas', 'Menu', 'Paket', 'Harga', 'Status'];
            foreach ($orders as $order) {
                $data[] = [
                    $order->kode_order,
                    $order->tanggal_order,
                    $order->customer->name ?? '-',
                    $order->customer->kelas ?? '-',
                    $order->menu->nama_menu ?? '-',
                    $order->paket ?? '-',
                    'Rp ' . number_format($order->total_harga, 0, ',', '.'),
                    $order->payment_status == 'paid' ? 'Lunas' : 'Belum Bayar',
                ];
            }
        } else {
            $data[] = ['Tidak ada order untuk periode ini'];
        }

        $data[] = [];

        // Data User (Customer) yang aktif
        $data[] = ['=== DATA CUSTOMER AKTIF ==='];
        $data[] = [];

        $customers = User::where('role', 'customer')
            ->where('status', 'Active')
            ->orderBy('name')
            ->get();

        if ($customers->count() > 0) {
            $data[] = ['Username', 'Nama', 'Kelas', 'No HP', 'Alergi'];
            foreach ($customers as $customer) {
                $data[] = [
                    $customer->username,
                    $customer->name,
                    $customer->kelas,
                    $customer->no_hp,
                    $customer->alergi ?? '-',
                ];
            }
        } else {
            $data[] = ['Tidak ada customer aktif'];
        }

        $data[] = [];
        $data[] = ['=== INFORMASI BACKUP ==='];
        $data[] = ['Dibuat pada', now()->format('d-m-Y H:i:s')];
        $data[] = ['Tipe Backup', $this->type == 'otomatis' ? 'Otomatis (Akhir Bulan)' : 'Manual'];
        $data[] = ['Periode', $this->startDate . ' s/d ' . $this->endDate];

        return $data;
    }

    /**
     * Header untuk styling
     */
    public function headings(): array
    {
        return [];
    }

    /**
     * Styling Excel
     */
    public function styles(Worksheet $sheet)
    {
        // Merge cells untuk judul
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
        ];
    }
}