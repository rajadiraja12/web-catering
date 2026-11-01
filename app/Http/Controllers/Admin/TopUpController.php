<?php
// app/Http/Controllers/Admin/TopUpController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\TopUp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TopUpController extends Controller
{
    /**
     * Daftar TopUp
     */
    public function index(Request $request)
    {
        $query = TopUp::with(['user', 'verifier'])
            ->orderBy('created_at', 'desc');

        // Filter status
        if ($request->status && in_array($request->status, ['pending', 'verified', 'rejected', 'expired'])) {
            $query->where('status', $request->status);
        }

        // Filter metode pembayaran
        if ($request->metode && in_array($request->metode, ['midtrans', 'saldo'])) {
            $query->where('metode_pembayaran', $request->metode);
        }

        // Filter tanggal
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('kode_topup', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('midtrans_order_id', 'LIKE', '%' . $request->search . '%')
                  ->orWhereHas('user', function($uq) use ($request) {
                      $uq->where('name', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('username', 'LIKE', '%' . $request->search . '%');
                  });
            });
        }

        $topups = $query->paginate(20);
        $statistics = $this->getStatistics();

        return view('admin.topup.index', compact('topups', 'statistics'));
    }

    /**
     * Detail TopUp
     */
    public function detail($id)
    {
        $topup = TopUp::with(['user', 'verifier'])->findOrFail($id);

        return view('admin.topup.detail', compact('topup'));
    }

    /**
     * Get Statistics
     */
    private function getStatistics()
    {
        $today = now()->startOfDay();
        $week = now()->startOfWeek();
        $month = now()->startOfMonth();

        return [
            'total_topup' => TopUp::count(),
            'total_pending' => TopUp::where('status', 'pending')->count(),
            'total_verified' => TopUp::where('status', 'verified')->count(),
            'total_rejected' => TopUp::where('status', 'rejected')->count(),
            'total_expired' => TopUp::where('status', 'expired')->count(),
            'total_nominal' => TopUp::where('status', 'verified')->sum('nominal'),
            'today_count' => TopUp::whereDate('created_at', $today)->count(),
            'today_nominal' => TopUp::whereDate('created_at', $today)->where('status', 'verified')->sum('nominal'),
            'week_count' => TopUp::whereDate('created_at', '>=', $week)->count(),
            'week_nominal' => TopUp::whereDate('created_at', '>=', $week)->where('status', 'verified')->sum('nominal'),
            'month_count' => TopUp::whereDate('created_at', '>=', $month)->count(),
            'month_nominal' => TopUp::whereDate('created_at', '>=', $month)->where('status', 'verified')->sum('nominal'),
            'by_status' => [
                'pending' => TopUp::where('status', 'pending')->count(),
                'verified' => TopUp::where('status', 'verified')->count(),
                'rejected' => TopUp::where('status', 'rejected')->count(),
                'expired' => TopUp::where('status', 'expired')->count(),
            ],
            'by_method' => [
                'midtrans' => TopUp::where('metode_pembayaran', 'midtrans')->count(),
                'saldo' => TopUp::where('metode_pembayaran', 'saldo')->count(),
            ]
        ];
    }

    /**
     * Export TopUp ke Excel dengan format profesional
     */
    public function exportExcel(Request $request)
    {
        try {
            // Build query dengan filter
            $query = TopUp::with(['user', 'verifier']);
            
            if ($request->status) {
                $query->where('status', $request->status);
            }
            
            if ($request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            $topups = $query->orderBy('created_at', 'desc')->get();
            
            // Validasi data
            if ($topups->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada data untuk diexport.');
            }
            
            // Buat Spreadsheet baru
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set judul sheet
            $sheet->setTitle('Data TopUp');
            
            // ==================== HEADER ====================
            // Buat header dengan style profesional
            $headers = [
                'A' => 'No',
                'B' => 'Kode TopUp',
                'C' => 'Customer',
                'D' => 'Username',
                'E' => 'Nominal (Rp)',
                'F' => 'Metode Pembayaran',
                'G' => 'Status',
                'H' => 'Tanggal Transaksi',
                'I' => 'Tanggal Dibayar',
                'J' => 'Diverifikasi Oleh',
                'K' => 'Catatan'
            ];
            
            // Isi header
            $col = 'A';
            foreach ($headers as $column => $header) {
                $sheet->setCellValue($column . '1', $header);
                $col = $column;
            }
            
            // Style header
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                    'name' => 'Arial'
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '2E75B6']
                    ]
                ]
            ];
            $sheet->getStyle('A1:' . $col . '1')->applyFromArray($headerStyle);
            
            // ==================== DATA ====================
            $row = 2;
            foreach ($topups as $index => $topup) {
                // Status label
                $statusLabels = [
                    'pending' => 'Pending',
                    'verified' => 'Diverifikasi',
                    'rejected' => 'Ditolak',
                    'expired' => 'Kadaluarsa'
                ];
                $statusLabel = $statusLabels[$topup->status] ?? $topup->status;
                
                // Status color (untuk conditional formatting nanti)
                $statusColors = [
                    'pending' => 'FFC000',
                    'verified' => '70AD47',
                    'rejected' => 'FF0000',
                    'expired' => '808080'
                ];
                
                // Metode label
                $metodeLabel = $topup->metode_pembayaran == 'midtrans' ? 'Midtrans' : 'Saldo';
                
                // Isi data
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $topup->kode_topup);
                $sheet->setCellValue('C' . $row, $topup->user->name ?? 'Unknown');
                $sheet->setCellValue('D' . $row, $topup->user->username ?? '-');
                $sheet->setCellValue('E' . $row, $topup->nominal);
                $sheet->setCellValue('F' . $row, $metodeLabel);
                $sheet->setCellValue('G' . $row, $statusLabel);
                $sheet->setCellValue('H' . $row, $topup->created_at->format('d-m-Y H:i:s'));
                $sheet->setCellValue('I' . $row, $topup->paid_at ? $topup->paid_at->format('d-m-Y H:i:s') : '-');
                $sheet->setCellValue('J' . $row, $topup->verifier->name ?? 'Otomatis');
                $sheet->setCellValue('K' . $row, $topup->catatan ?? '-');
                
                // Style untuk nominal (format number)
                $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
                
                // Conditional formatting untuk status
                $statusColor = $statusColors[$topup->status] ?? '000000';
                $sheet->getStyle('G' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => $statusColor]
                    ]
                ]);
                
                $row++;
            }
            
            // ==================== AUTO SIZE ====================
            foreach (range('A', $col) as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
            
            // ==================== BORDER DATA ====================
            $lastRow = $row - 1;
            $dataRange = 'A1:' . $col . $lastRow;
            
            // Border untuk semua data
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D0D0D0']
                    ]
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);
            
            // ==================== ALTERNATE ROW COLOR ====================
            for ($i = 2; $i <= $lastRow; $i++) {
                if ($i % 2 == 0) {
                    $sheet->getStyle('A' . $i . ':' . $col . $i)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F2F2F2']
                        ]
                    ]);
                }
            }
            
            // ==================== FREEZE PANE ====================
            $sheet->freezePane('A2');
            
            // ==================== SET ROW HEIGHT ====================
            $sheet->getRowDimension(1)->setRowHeight(25);
            
            // ==================== WRITER ====================
            $filename = 'topup_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            // Simpan ke temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'topup_');
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);
            
            // ==================== LOG ====================
            Log::info('Admin exported topup data', [
                'admin_id' => session('user_id'),
                'total' => $topups->count(),
                'filters' => $request->only(['status', 'date_from', 'date_to'])
            ]);
            
            // ==================== DOWNLOAD ====================
            return response()->download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Log::error('Export TopUp Excel error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat export data: ' . $e->getMessage());
        }
    }
    /**
     * Export TopUp ke PDF (Opsional - jika pakai DomPDF)
     */
    public function exportPdf(Request $request)
    {
        $query = TopUp::with(['user']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $topups = $query->get();
        $totalNominal = $topups->sum('nominal');
        $statistics = [
            'total' => $topups->count(),
            'verified' => $topups->where('status', 'verified')->count(),
            'pending' => $topups->where('status', 'pending')->count(),
            'rejected' => $topups->where('status', 'rejected')->count(),
            'expired' => $topups->where('status', 'expired')->count(),
            'total_nominal' => $totalNominal,
        ];

        // PERBAIKI: view yang benar
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.topup.export_pdf', compact('topups', 'statistics'));
        return $pdf->download('topup_' . date('Y-m-d') . '.pdf');
    }
}