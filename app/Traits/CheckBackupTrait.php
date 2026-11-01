<?php

namespace App\Traits;

use App\Models\Backup;
use App\Exports\OrderBackupExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Report;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

trait CheckBackupTrait
{
    /**
     * Cek dan buat backup otomatis
     */
    protected function checkAndCreateBackup()
    {
        try {
            // Cek apakah hari ini tanggal 1 (awal bulan)
            if (Carbon::now()->day == 1) {
                $this->checkBackupTransaksi();
                $this->checkBackupKeuangan();
            }
        } catch (\Exception $e) {
            Log::error('Check backup error: ' . $e->getMessage());
        }
    }

    /**
     * Cek dan buat backup transaksi
     */
    private function checkBackupTransaksi()
    {
        $startDate = Carbon::now()->subMonth()->startOfMonth();
        $endDate = Carbon::now()->subMonth()->endOfMonth();
        $monthName = $startDate->format('F_Y');

        // Cek apakah backup bulan ini sudah ada
        $exists = Backup::where('nama_backup', 'LIKE', "%{$monthName}%")
            ->where('type', 'otomatis')
            ->where('keterangan_data', 'LIKE', '%transaksi%')
            ->exists();

        if ($exists) {
            Log::info("Backup transaksi bulan {$monthName} sudah ada, skip...");
            return;
        }

        Log::info("Membuat backup transaksi bulan: {$monthName}");

        // Generate backup
        $result = $this->generateOrderBackup($startDate, $endDate, 'otomatis');

        if ($result) {
            Backup::create([
                'nama_backup' => 'Backup_Transaksi_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d'),
                'lokasi_file' => $result['path'],
                'type' => 'otomatis',
                'keterangan_data' => "Backup transaksi otomatis bulan " . $monthName,
                'size' => $result['size'],
                'created_by' => 1,
                'backup_date' => now(),
                'periode_start' => $startDate,
                'periode_end' => $endDate,
            ]);

            Log::info('✅ Backup transaksi otomatis berhasil: ' . $monthName);
        }
    }

    /**
     * Cek dan buat backup keuangan
     */
    private function checkBackupKeuangan()
    {
        $startDate = Carbon::now()->subMonth()->startOfMonth();
        $endDate = Carbon::now()->subMonth()->endOfMonth();
        $monthName = $startDate->format('F_Y');

        // Cek apakah backup keuangan bulan ini sudah ada
        $exists = Backup::where('nama_backup', 'LIKE', "%{$monthName}%")
            ->where('type', 'otomatis')
            ->where('keterangan_data', 'LIKE', '%keuangan%')
            ->exists();

        if ($exists) {
            Log::info("Backup keuangan bulan {$monthName} sudah ada, skip...");
            return;
        }

        Log::info("Membuat backup keuangan bulan: {$monthName}");

        // Generate backup keuangan
        $result = $this->generateKeuanganBackup($startDate, $endDate, 'otomatis');

        if ($result) {
            Backup::create([
                'nama_backup' => 'Backup_Keuangan_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d'),
                'lokasi_file' => $result['path'],
                'type' => 'otomatis',
                'keterangan_data' => "Backup keuangan otomatis bulan " . $monthName,
                'size' => $result['size'],
                'created_by' => 1,
                'backup_date' => now(),
                'periode_start' => $startDate,
                'periode_end' => $endDate,
            ]);

            Log::info('✅ Backup keuangan otomatis berhasil: ' . $monthName);
        }
    }

    /**
     * Generate backup order
     */
    private function generateOrderBackup($startDate, $endDate, $type)
    {
        try {
            $filename = 'Backup_Transaksi_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.xlsx';
            $path = 'backups/transaksi/' . $filename;

            // Buat Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Laporan Transaksi');

            // Header
            $sheet->setCellValue('A1', 'LAPORAN TRANSAKSI');
            $sheet->mergeCells('A1:J1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValue('A2', 'Periode: ' . $startDate->format('d-m-Y') . ' s/d ' . $endDate->format('d-m-Y'));
            $sheet->mergeCells('A2:J2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValue('A3', 'Tanggal Backup: ' . Carbon::now()->format('d-m-Y H:i:s'));
            $sheet->mergeCells('A3:J3');
            $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row = 5;

            // Header Tabel
            $headers = ['No', 'Kode Order', 'Tanggal Order', 'Tanggal Menu', 'Customer', 'Kelas', 'Menu', 'Paket', 'Harga', 'Status'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID);
                $sheet->getStyle($col . $row)->getFill()->getStartColor()->setARGB('FFD9E1F2');
                $col++;
            }

            $row++;

            // Data Order
            $orders = \App\Models\Order::with(['customer', 'menu'])
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

                if ($order->payment_status == 'paid') {
                    $sheet->getStyle('J' . $row)->getFont()->getColor()->setARGB('FF00B050');
                } else {
                    $sheet->getStyle('J' . $row)->getFont()->getColor()->setARGB('FFFF0000');
                }

                $row++;
                $no++;
            }

            // Auto size
            foreach (range('A', 'J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Simpan
            $writer = new Xlsx($spreadsheet);
            $fullPath = storage_path('app/public/' . $path);
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $writer->save($fullPath);

            return [
                'path' => $path,
                'size' => filesize($fullPath) / 1024,
            ];

        } catch (\Exception $e) {
            Log::error('Generate order backup error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate backup keuangan
     */
    private function generateKeuanganBackup($startDate, $endDate, $type)
    {
        try {
            $filename = 'Backup_Keuangan_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.xlsx';
            $path = 'backups/keuangan/' . $filename;

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Laporan Keuangan');

            // Header
            $sheet->setCellValue('A1', 'LAPORAN KEUANGAN');
            $sheet->mergeCells('A1:F1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValue('A2', 'Periode: ' . $startDate->format('d-m-Y') . ' s/d ' . $endDate->format('d-m-Y'));
            $sheet->mergeCells('A2:F2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValue('A3', 'Tanggal Backup: ' . Carbon::now()->format('d-m-Y H:i:s'));
            $sheet->mergeCells('A3:F3');
            $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row = 5;

            // Header Tabel
            $headers = ['Tanggal', 'Keterangan', 'Debit', 'Kredit', 'Saldo', 'Kategori'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID);
                $sheet->getStyle($col . $row)->getFill()->getStartColor()->setARGB('FFD9E1F2');
                $col++;
            }

            $row++;

            $reports = Report::whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($reports as $report) {
                $sheet->setCellValue('A' . $row, $report->tanggal_laporan);
                $sheet->setCellValue('B' . $row, $report->keterangan);
                $sheet->setCellValue('C' . $row, 'Rp ' . number_format($report->debit, 0, ',', '.'));
                $sheet->setCellValue('D' . $row, 'Rp ' . number_format($report->kredit, 0, ',', '.'));
                $sheet->setCellValue('E' . $row, 'Rp ' . number_format($report->saldo, 0, ',', '.'));
                $sheet->setCellValue('F' . $row, $report->kategori ?? '-');
                $row++;
            }

            // Auto size
            foreach (range('A', 'F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $fullPath = storage_path('app/public/' . $path);
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $writer->save($fullPath);

            return [
                'path' => $path,
                'size' => filesize($fullPath) / 1024,
            ];

        } catch (\Exception $e) {
            Log::error('Generate keuangan backup error: ' . $e->getMessage());
            return null;
        }
    }
}