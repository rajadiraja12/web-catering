<?php
// app/Http/Controllers/Admin/LogActivityController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LogActivity;
use App\Helpers\ActivityHelper;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class LogActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = LogActivity::with('user');

        // Filter search
        $search = $request->input('search');
        if ($search) {
            $query->search($search);
        }

        // Filter tanggal
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        if ($dateFrom || $dateTo) {
            $query->dateRange($dateFrom, $dateTo);
        }

        // Filter module
        $module = $request->input('module');
        if ($module) {
            $query->where('module', $module);
        }

        // Filter aksi
        $aksi = $request->input('aksi');
        if ($aksi) {
            $query->where('aksi', $aksi);
        }

        // Order by
        $query->orderBy('created_at', 'desc');

        // Pagination
        $logs = $query->paginate(10);

        // Data untuk filter
        $modules = LogActivity::distinct()->pluck('module')->filter()->values();
        $aksis = LogActivity::distinct()->pluck('aksi')->filter()->values();

        return view('admin.log_activity', compact('logs', 'modules', 'aksis'));
    }

    public function exportExcel(Request $request)
    {
        try {
            $query = LogActivity::with('user');

            // Filter search
            $search = $request->input('search');
            if ($search) {
                $query->search($search);
            }

            // Filter tanggal
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            if ($dateFrom || $dateTo) {
                $query->dateRange($dateFrom, $dateTo);
            }

            // Filter module
            $module = $request->input('module');
            if ($module) {
                $query->where('module', $module);
            }

            $logs = $query->orderBy('created_at', 'desc')->get();

            if ($logs->isEmpty()) {
                return back()->with('error', 'Tidak ada data untuk diexport');
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header
            $sheet->setCellValue('A1', 'LAPORAN LOG AKTIVITAS');
            $sheet->setCellValue('A2', 'Tanggal Export: ' . date('d/m/Y H:i:s'));
            $sheet->setCellValue('A3', 'Total Data: ' . $logs->count());

            $sheet->mergeCells('A1:H1');
            $sheet->mergeCells('A2:H2');
            $sheet->mergeCells('A3:H3');

            $sheet->getStyle('A1:A3')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Header tabel
            $headers = ['No', 'Tanggal', 'Waktu', 'Username', 'Aksi', 'Module', 'Keterangan', 'IP Address'];
            $col = 'A';
            $rowHeader = 5;
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $rowHeader, $header);
                $col++;
            }

            $sheet->getStyle('A5:H5')->getFont()->setBold(true);
            $sheet->getStyle('A5:H5')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
            $sheet->getStyle('A5:H5')->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle('A5:H5')->getFill()->getStartColor()->setARGB('FF2563EB');
            $sheet->getStyle('A5:H5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A5:H5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            // Data
            $row = 6;
            $no = 1;
            foreach ($logs as $log) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, Carbon::parse($log->created_at)->format('d/m/Y'));
                $sheet->setCellValue('C' . $row, Carbon::parse($log->created_at)->format('H:i:s'));
                $sheet->setCellValue('D' . $row, $log->user ? $log->user->username : 'System');
                $sheet->setCellValue('E' . $row, $log->aksi);
                $sheet->setCellValue('F' . $row, $log->module);
                $sheet->setCellValue('G' . $row, $log->keterangan);
                $sheet->setCellValue('H' . $row, $log->ip ?? '-');

                $sheet->getStyle('A' . $row . ':H' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $row++;
            }

            // Auto size kolom
            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $filename = 'Laporan_Log_Aktivitas_' . date('Ymd_His') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal export Excel: ' . $e->getMessage());
        }
    }

    public function clear()
    {
        try {
            LogActivity::truncate();
            ActivityHelper::log('Hapus Semua', 'System', 'Menghapus semua log aktivitas');
            return back()->with('success', 'Semua log aktivitas berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus log: ' . $e->getMessage());
        }
    }
}