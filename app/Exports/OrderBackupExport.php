<?php

namespace App\Exports;

use App\Models\Order;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class OrderBackupExport
{
    /**
     * Generate Excel Backup Order
     */
    public static function generate($startDate, $endDate, $type = 'manual')
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Transaksi');

        $row = 1;

        // ============================================
        // HEADER
        // ============================================
        $sheet->setCellValue('A1', 'LAPORAN TRANSAKSI / ORDER');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 2;
        $sheet->setCellValue('A2', 'Periode: ' . $startDate->format('d-m-Y') . ' s/d ' . $endDate->format('d-m-Y'));
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 3;
        $sheet->setCellValue('A3', 'Tanggal Backup: ' . Carbon::now()->format('d-m-Y H:i:s'));
        $sheet->mergeCells('A3:H3');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 4;
        $sheet->setCellValue('A4', 'Tipe Backup: ' . ($type == 'otomatis' ? 'Otomatis (Akhir Bulan)' : 'Manual'));
        $sheet->mergeCells('A4:H4');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 6;

        // ============================================
        // RINGKASAN
        // ============================================
        $sheet->setCellValue('A' . $row, 'RINGKASAN');
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle('A' . $row)->getFill()->getStartColor()->setARGB('FF4472C4');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row += 2;

        // Statistik
        $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        $totalRevenue = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_harga');
        $totalUnpaid = Order::where('payment_status', 'unpaid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_harga');
        $totalCustomers = Order::whereBetween('created_at', [$startDate, $endDate])
            ->distinct('id_customer')
            ->count('id_customer');

        $sheet->setCellValue('B' . $row, 'Total Transaksi');
        $sheet->setCellValue('C' . $row, $totalOrders);
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);

        $row++;
        $sheet->setCellValue('B' . $row, 'Total Pendapatan');
        $sheet->setCellValue('C' . $row, 'Rp ' . number_format($totalRevenue, 0, ',', '.'));
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);
        $sheet->getStyle('C' . $row)->getFont()->getColor()->setARGB('FF00B050');

        $row++;
        $sheet->setCellValue('B' . $row, 'Total Belum Dibayar');
        $sheet->setCellValue('C' . $row, 'Rp ' . number_format($totalUnpaid, 0, ',', '.'));
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);
        $sheet->getStyle('C' . $row)->getFont()->getColor()->setARGB('FFFF0000');

        $row++;
        $sheet->setCellValue('B' . $row, 'Total Customer');
        $sheet->setCellValue('C' . $row, $totalCustomers);
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);

        $row += 2;

        // ============================================
        // DETAIL ORDER
        // ============================================
        $sheet->setCellValue('A' . $row, 'DETAIL TRANSAKSI');
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle('A' . $row)->getFill()->getStartColor()->setARGB('FF4472C4');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row += 2;

        // Header Tabel
        $headers = ['No', 'Kode Order', 'Tanggal Order', 'Tanggal Menu', 'Customer', 'Kelas', 'Menu', 'Paket', 'Harga', 'Status'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle($col . $row)->getFill()->getStartColor()->setARGB('FFD9E1F2');
            $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $col++;
        }

        $row++;

        // Data Order
        $orders = Order::with(['customer', 'menu'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $no = 1;
        foreach ($orders as $order) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $order->kode_order);
            $sheet->setCellValue('C' . $row, $order->tanggal_order);
            $sheet->setCellValue('D' . $row, $order->tanggal_menu);
            $sheet->setCellValue('E' . $row, $order->customer->name ?? '-');
            $sheet->setCellValue('F' . $row, $order->customer->kelas ?? '-');
            $sheet->setCellValue('G' . $row, $order->menu->nama_menu ?? '-');
            $sheet->setCellValue('H' . $row, $order->paket ?? '-');
            $sheet->setCellValue('I' . $row, 'Rp ' . number_format($order->total_harga, 0, ',', '.'));
            $sheet->setCellValue('J' . $row, $order->payment_status == 'paid' ? 'Lunas' : 'Belum Bayar');

            // Warna status
            if ($order->payment_status == 'paid') {
                $sheet->getStyle('J' . $row)->getFont()->getColor()->setARGB('FF00B050');
            } else {
                $sheet->getStyle('J' . $row)->getFont()->getColor()->setARGB('FFFF0000');
            }

            $row++;
            $no++;
        }

        // ============================================
        // AUTO SIZE
        // ============================================
        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        foreach ($columns as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ============================================
        // FOOTER
        // ============================================
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Total Data: ' . $orders->count() . ' transaksi');
        $sheet->mergeCells('A' . $row . ':J' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Save
        $writer = new Xlsx($spreadsheet);

        // Return writer untuk disimpan
        return $writer;
    }

    /**
     * Generate dan simpan ke file
     */
    public static function generateAndSave($startDate, $endDate, $type = 'manual')
    {
        $filename = 'Backup_Transaksi_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.xlsx';
        $path = 'backups/transaksi/' . $filename;

        $writer = self::generate($startDate, $endDate, $type);

        $fullPath = storage_path('app/public/' . $path);
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $writer->save($fullPath);

        return [
            'path' => $path,
            'filename' => $filename,
            'size' => filesize($fullPath) / 1024,
        ];
    }
}