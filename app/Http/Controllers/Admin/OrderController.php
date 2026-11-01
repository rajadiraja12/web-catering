<?php
// app/Http/Controllers/Admin/OrderController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogActivity;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'menu']);

        // ===== FILTER: TAMPILKAN PESANAN BERDASARKAN TANGGAL MENU HARI INI =====
        // Default: tampilkan pesanan dengan tanggal menu hari ini
        // $today = date('Y-m-d');
        
        // Jika tidak ada filter tanggal, tampilkan berdasarkan tanggal menu hari ini
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        
        // if (!$dateFrom && !$dateTo) {
        //     // Default: hanya tampilkan tanggal menu hari ini
        //     $query->whereDate('tanggal_menu', $today);
        // }

        // Filter status
        $status = $request->input('status', 'all');
        if ($status === 'unpaid') {
            $query->where('payment_status', 'unpaid');
        } elseif ($status === 'paid') {
            $query->where('payment_status', 'paid');
        }

        // Search
        $search = $request->input('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('kode_order', 'like', "%{$search}%")
                ->orWhereHas('customer', function($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                })
                ->orWhereHas('menu', function($q2) use ($search) {
                    $q2->where('nama_menu', 'like', "%{$search}%");
                });
            });
        }

        // Filter tanggal (berdasarkan tanggal_menu)
        if ($dateFrom && $dateTo) {
            $query->whereBetween('tanggal_menu', [$dateFrom, $dateTo]);
        } elseif ($dateFrom) {
            $query->whereDate('tanggal_menu', '>=', $dateFrom);
        } elseif ($dateTo) {
            $query->whereDate('tanggal_menu', '<=', $dateTo);
        }

        // Order by
        $query->orderBy('tanggal_order', 'desc')->orderBy('id', 'desc');

        // Pagination
        $orders = $query->paginate(5);

        // Ambil semua customer untuk dropdown
        $customers = User::where('role', 'customer')
            ->where('status', 'Active')
            ->orderBy('kelas')
            ->orderBy('name')
            ->get(['id', 'name', 'kelas']);
        
        // Parse kelas menjadi array (kelas dan nama_kelas)
        $customersWithParsed = $customers->map(function($customer) {
            $kelasParts = explode('-', $customer->kelas);
            return (object) [
                'id' => $customer->id,
                'name' => $customer->name,
                'kelas' => $kelasParts[0] ?? '',
                'nama_kelas' => $kelasParts[1] ?? '',
                'kelas_full' => $customer->kelas
            ];
        });
        
        // Ambil list kelas unik (angka saja)
        $kelas_list = $customersWithParsed->pluck('kelas')
            ->filter()
            ->unique()
            ->sort()
            ->values();
        
        // Ambil list nama_kelas berdasarkan kelas (untuk filter dinamis di JS)
        $nama_kelas_by_kelas = [];
        foreach ($kelas_list as $kelas) {
            $nama_kelas_by_kelas[$kelas] = $customersWithParsed
                ->where('kelas', $kelas)
                ->pluck('nama_kelas')
                ->filter()
                ->unique()
                ->values();
        }

        return view('admin.manage_order', compact('orders', 'customersWithParsed', 'kelas_list', 'nama_kelas_by_kelas'));
    }

    /**
     * Ganti Hari Order
     */
    public function gantiHari(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'exists:orders,id',
            'new_tanggal_menu' => 'required|date',
            'alasan' => 'required|in:sakit,tidak_masuk,izin,lainnya',
            'catatan' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $updatedOrders = [];
            $alasanLabels = [
                'sakit' => 'Sakit',
                'tidak_masuk' => 'Tidak Masuk Sekolah',
                'izin' => 'Izin',
                'lainnya' => 'Lainnya'
            ];

            foreach ($request->order_ids as $orderId) {
                $order = Order::findOrFail($orderId);
                
                $oldDate = $order->tanggal_menu;
                $order->tanggal_menu = $request->new_tanggal_menu;
                
                // Tambahkan catatan ganti hari
                $oldNote = $order->catatan ?? '';
                $newNote = $oldNote . "\n[GANTI HARI] " . date('d-m-Y H:i') . 
                        " - Dari: " . date('d-m-Y', strtotime($oldDate)) . 
                        " ke: " . date('d-m-Y', strtotime($request->new_tanggal_menu)) .
                        " - Alasan: " . $alasanLabels[$request->alasan] .
                        ($request->catatan ? " (" . $request->catatan . ")" : "");
                $order->catatan = trim($newNote);
                
                $order->save();
                $updatedOrders[] = $order->id;

                // Log aktivitas
                LogActivity::create([
                    'user_id' => session('user_id'),
                    'aksi' => 'Ganti Hari Order',
                    'module' => 'Orders',
                    'keterangan' => 'Ganti hari order ' . $order->kode_order . ' dari ' . date('d-m-Y', strtotime($oldDate)) . ' ke ' . date('d-m-Y', strtotime($request->new_tanggal_menu)),
                    'new_data' => json_encode([
                        'order_id' => $order->id,
                        'kode_order' => $order->kode_order,
                        'old_date' => $oldDate,
                        'new_date' => $request->new_tanggal_menu,
                        'alasan' => $request->alasan
                    ])
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengganti hari untuk ' . count($updatedOrders) . ' order ke ' . date('d-m-Y', strtotime($request->new_tanggal_menu)),
                'data' => [
                    'updated' => count($updatedOrders),
                    'orders' => $updatedOrders,
                    'new_date' => $request->new_tanggal_menu
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ganti hari error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengganti hari: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transfer order ke customer lain
     */
    public function transferOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'exists:orders,id',
            'from_customer' => 'required|exists:users,id',
            'to_customer' => 'required|exists:users,id|different:from_customer',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $fromCustomer = User::find($request->from_customer);
        $toCustomer = User::find($request->to_customer);

        if (!$fromCustomer || !$toCustomer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer tidak ditemukan!'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $updatedOrders = [];
            $totalTransferred = 0;

            foreach ($request->order_ids as $orderId) {
                $order = Order::where('id', $orderId)
                    ->where('id_customer', $request->from_customer)
                    ->first();

                if (!$order) {
                    continue; // Skip order yang tidak cocok
                }

                // Update customer
                $order->id_customer = $request->to_customer;
                
                // Tambahkan catatan transfer
                $oldNote = $order->catatan ?? '';
                $newNote = $oldNote . "\n[TRANSFER] " . date('d-m-Y H:i') . 
                        " - Dari: " . $fromCustomer->name . 
                        " ke: " . $toCustomer->name . 
                        ($request->note ? " (" . $request->note . ")" : "");
                $order->catatan = trim($newNote);
                
                $order->save();

                $updatedOrders[] = $order->id;
                $totalTransferred += $order->total_harga;

                // Log aktivitas
                LogActivity::create([
                    'user_id' => session('user_id'),
                    'aksi' => 'Transfer Order',
                    'module' => 'Orders',
                    'keterangan' => 'Transfer order ' . $order->kode_order . ' dari ' . $fromCustomer->name . ' ke ' . $toCustomer->name,
                    'new_data' => json_encode([
                        'order_id' => $order->id,
                        'kode_order' => $order->kode_order,
                        'from_customer' => $fromCustomer->id,
                        'to_customer' => $toCustomer->id,
                        'note' => $request->note
                    ])
                ]);
            }

            DB::commit();

            Log::info('Order transfer completed', [
                'admin_id' => session('user_id'),
                'from_customer' => $fromCustomer->id,
                'to_customer' => $toCustomer->id,
                'total_orders' => count($updatedOrders),
                'total_nominal' => $totalTransferred
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mentransfer ' . count($updatedOrders) . ' order dari ' . $fromCustomer->name . ' ke ' . $toCustomer->name,
                'data' => [
                    'transferred' => count($updatedOrders),
                    'total' => $totalTransferred,
                    'orders' => $updatedOrders
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer order error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mentransfer order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get bulk order info
     */
    public function bulkOrderInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $orders = Order::whereIn('id', $request->ids)
            ->with(['customer', 'menu'])
            ->get();

        return response()->json([
            'success' => true,
            'orders' => $orders->map(function($order) {
                return [
                    'id' => $order->id,
                    'kode_order' => $order->kode_order,
                    'total_harga' => 'Rp ' . number_format($order->total_harga, 0, ',', '.'),
                    'total_harga_raw' => $order->total_harga,
                    'customer' => $order->customer->name ?? 'Unknown',
                    'menu' => $order->menu->nama_menu ?? 'Menu'
                ];
            })
        ]);
    }

    public function detail($id)
    {
        $order = Order::with(['menu', 'payment', 'customer'])
            ->where('id_customer', Auth::id())
            ->findOrFail($id);
            
        return view('customer.order_detail', compact('order'));
    }
    
    // ============================================
    // CETAK EXCEL HARIAN
    // ============================================
    
    public function exportExcelDaily(Request $request)
    {
        try {
            $date = $request->input('date', date('Y-m-d'));
            
            $orders = Order::with(['customer', 'menu'])
                ->whereDate('tanggal_menu', $date)
                ->orderBy('tanggal_menu', 'desc')
                ->get();

            if ($orders->isEmpty()) {
                return back()->with('error', 'Tidak ada data untuk tanggal ' . Carbon::parse($date)->format('d/m/Y'));
            }

            // Buat spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header
            $sheet->setCellValue('A1', 'LAPORAN ORDER HARIAN');
            $sheet->setCellValue('A2', 'Tanggal: ' . Carbon::parse($date)->format('d/m/Y'));
            $sheet->setCellValue('A3', 'Total Order: ' . $orders->count() . ' order');
            $sheet->setCellValue('A4', 'Total Pendapatan: Rp ' . number_format($orders->sum('total_harga'), 0, ',', '.'));

            // Merge header
            $sheet->mergeCells('A1:K1');
            $sheet->mergeCells('A2:K2');
            $sheet->mergeCells('A3:K3');
            $sheet->mergeCells('A4:K4');

            // Style header
            $sheet->getStyle('A1:A4')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle('A1:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Header tabel (baris 6)
            $headers = ['No', 'Kode Order', 'Tgl Order', 'Tgl Menu', 'Menu', 'Paket', 'Harga Satuan', 'Total Harga', 'Customer', 'Kelas', 'Status'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '6', $header);
                $col++;
            }

            // Style header tabel - PERBAIKAN
            $sheet->getStyle('A6:K6')->getFont()->setBold(true);
            $sheet->getStyle('A6:K6')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
            $sheet->getStyle('A6:K6')->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle('A6:K6')->getFill()->getStartColor()->setARGB('FF2563EB');
            $sheet->getStyle('A6:K6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A6:K6')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            // Data
            $row = 7;
            $no = 1;
            foreach ($orders as $order) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $order->kode_order);
                $sheet->setCellValue('C' . $row, Carbon::parse($order->tanggal_order)->format('d/m/Y'));
                $sheet->setCellValue('D' . $row, Carbon::parse($order->tanggal_menu)->format('d/m/Y'));
                $sheet->setCellValue('E' . $row, $order->menu ? $order->menu->nama_menu : '-');
                $sheet->setCellValue('F' . $row, ucfirst($order->paket ?? '-'));
                $sheet->setCellValue('G' . $row, $order->harga_satuan);
                $sheet->setCellValue('H' . $row, $order->total_harga);
                $sheet->setCellValue('I' . $row, $order->customer ? $order->customer->name : '-');
                $sheet->setCellValue('J' . $row, $order->customer ? $order->customer->kelas : '-');
                $sheet->setCellValue('K' . $row, $order->payment_status == 'paid' ? 'Lunas' : 'Hutang');

                // Style data
                $sheet->getStyle('A' . $row . ':K' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                // Format harga
                $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
                
                // Warna status - PERBAIKAN
                if ($order->payment_status == 'paid') {
                    $sheet->getStyle('K' . $row)->getFont()->getColor()->setARGB('FF16A34A');
                } else {
                    $sheet->getStyle('K' . $row)->getFont()->getColor()->setARGB('FFDC2626');
                }
                
                $row++;
            }

            // Total di baris terakhir
            $sheet->setCellValue('G' . $row, 'TOTAL');
            $sheet->setCellValue('H' . $row, $orders->sum('total_harga'));
            $sheet->getStyle('G' . $row . ':H' . $row)->getFont()->setBold(true);
            $sheet->getStyle('G' . $row . ':H' . $row)->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle('G' . $row . ':H' . $row)->getFill()->getStartColor()->setARGB('FFE5E7EB');
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');

            // Auto size kolom
            foreach (range('A', 'K') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Set judul file
            $filename = 'Laporan_Order_Harian_' . date('Ymd') . '.xlsx';

            // Header untuk download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            Log::error('Export Excel Daily Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal export Excel: ' . $e->getMessage());
        }
    }

    // ============================================
    // CETAK EXCEL BULANAN
    // ============================================
    
    public function exportExcelMonthly(Request $request)
    {
        try {
            $month = $request->input('month', date('m'));
            $year = $request->input('year', date('Y'));
            
            $startDate = $year . '-' . $month . '-01';
            $endDate = date('Y-m-t', strtotime($startDate));

            $orders = Order::with(['customer', 'menu'])
                ->whereBetween('tanggal_order', [$startDate, $endDate])
                ->orderBy('tanggal_order', 'desc')
                ->get();

            if ($orders->isEmpty()) {
                $bulanNama = Carbon::createFromDate($year, $month, 1)->format('F Y');
                return back()->with('error', 'Tidak ada data untuk bulan ' . $bulanNama);
            }

            // Buat spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header
            $bulanNama = Carbon::createFromDate($year, $month, 1)->format('F Y');
            $sheet->setCellValue('A1', 'LAPORAN ORDER BULANAN');
            $sheet->setCellValue('A2', 'Bulan: ' . $bulanNama);
            $sheet->setCellValue('A3', 'Total Order: ' . $orders->count() . ' order');
            $sheet->setCellValue('A4', 'Total Pendapatan: Rp ' . number_format($orders->sum('total_harga'), 0, ',', '.'));
            
            // Hitung statistik tambahan
            $totalPaid = $orders->where('payment_status', 'paid')->count();
            $totalUnpaid = $orders->where('payment_status', 'unpaid')->count();
            $totalMenu = $orders->groupBy('id_menu')->count();
            
            $sheet->setCellValue('A5', 'Total Lunas: ' . $totalPaid . ' order');
            $sheet->setCellValue('A6', 'Total Belum Bayar: ' . $totalUnpaid . ' order');
            $sheet->setCellValue('A7', 'Total Menu: ' . $totalMenu . ' jenis');

            // Merge header
            $sheet->mergeCells('A1:K1');
            $sheet->mergeCells('A2:K2');
            $sheet->mergeCells('A3:K3');
            $sheet->mergeCells('A4:K4');
            $sheet->mergeCells('A5:K5');
            $sheet->mergeCells('A6:K6');
            $sheet->mergeCells('A7:K7');

            // Style header
            $sheet->getStyle('A1:A7')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle('A1:A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Header tabel (baris 9)
            $headers = ['No', 'Kode Order', 'Tgl Order', 'Tgl Menu', 'Menu', 'Paket', 'Harga Satuan', 'Total Harga', 'Customer', 'Kelas', 'Status'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '9', $header);
                $col++;
            }

            // Style header tabel - PERBAIKAN
            $sheet->getStyle('A9:K9')->getFont()->setBold(true);
            $sheet->getStyle('A9:K9')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
            $sheet->getStyle('A9:K9')->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle('A9:K9')->getFill()->getStartColor()->setARGB('FF2563EB');
            $sheet->getStyle('A9:K9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A9:K9')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            // Data
            $row = 10;
            $no = 1;
            foreach ($orders as $order) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $order->kode_order);
                $sheet->setCellValue('C' . $row, Carbon::parse($order->tanggal_order)->format('d/m/Y'));
                $sheet->setCellValue('D' . $row, Carbon::parse($order->tanggal_menu)->format('d/m/Y'));
                $sheet->setCellValue('E' . $row, $order->menu ? $order->menu->nama_menu : '-');
                $sheet->setCellValue('F' . $row, ucfirst($order->paket ?? '-'));
                $sheet->setCellValue('G' . $row, $order->harga_satuan);
                $sheet->setCellValue('H' . $row, $order->total_harga);
                $sheet->setCellValue('I' . $row, $order->customer ? $order->customer->name : '-');
                $sheet->setCellValue('J' . $row, $order->customer ? $order->customer->kelas : '-');
                $sheet->setCellValue('K' . $row, $order->payment_status == 'paid' ? 'Lunas' : 'Belum Bayar');

                // Style data
                $sheet->getStyle('A' . $row . ':K' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                // Format harga
                $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
                
                // Warna status - PERBAIKAN
                if ($order->payment_status == 'paid') {
                    $sheet->getStyle('K' . $row)->getFont()->getColor()->setARGB('FF16A34A');
                } else {
                    $sheet->getStyle('K' . $row)->getFont()->getColor()->setARGB('FFDC2626');
                }
                
                $row++;
            }

            // Total di baris terakhir
            $sheet->setCellValue('G' . $row, 'TOTAL');
            $sheet->setCellValue('H' . $row, $orders->sum('total_harga'));
            $sheet->getStyle('G' . $row . ':H' . $row)->getFont()->setBold(true);
            $sheet->getStyle('G' . $row . ':H' . $row)->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle('G' . $row . ':H' . $row)->getFill()->getStartColor()->setARGB('FFE5E7EB');
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');

            // Auto size kolom
            foreach (range('A', 'K') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Set judul file
            $filename = 'Laporan_Order_Bulanan_' . date('Ym') . '.xlsx';

            // Header untuk download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            Log::error('Export Excel Monthly Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal export Excel: ' . $e->getMessage());
        }
    }

    // ============================================
    // CETAK EXCEL DENGAN FILTER
    // ============================================
    
    public function exportExcelFiltered(Request $request)
    {
        try {
            $query = Order::with(['customer', 'menu']);

            // Filter status
            $status = $request->input('status', 'all');
            if ($status === 'unpaid') {
                $query->where('payment_status', 'unpaid');
            } elseif ($status === 'paid') {
                $query->where('payment_status', 'paid');
            }

            // Search
            $search = $request->input('search');
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('kode_order', 'like', "%{$search}%")
                      ->orWhereHas('customer', function($q2) use ($search) {
                          $q2->where('name', 'like', "%{$search}%")
                             ->orWhere('username', 'like', "%{$search}%");
                      })
                      ->orWhereHas('menu', function($q2) use ($search) {
                          $q2->where('nama_menu', 'like', "%{$search}%");
                      });
                });
            }

            // Filter tanggal
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            if ($dateFrom && $dateTo) {
                $query->whereBetween('tanggal_order', [$dateFrom, $dateTo]);
            } elseif ($dateFrom) {
                $query->where('tanggal_order', '>=', $dateFrom);
            } elseif ($dateTo) {
                $query->where('tanggal_order', '<=', $dateTo);
            }

            $orders = $query->orderBy('tanggal_order', 'desc')->get();

            if ($orders->isEmpty()) {
                return back()->with('error', 'Tidak ada data untuk diexport');
            }

            // Buat spreadsheet (sama seperti di atas)
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header
            $sheet->setCellValue('A1', 'LAPORAN ORDER (FILTER)');
            $sheet->setCellValue('A2', 'Tanggal Export: ' . date('d/m/Y H:i:s'));
            $sheet->setCellValue('A3', 'Total Order: ' . $orders->count() . ' order');
            $sheet->setCellValue('A4', 'Total Pendapatan: Rp ' . number_format($orders->sum('total_harga'), 0, ',', '.'));

            $sheet->mergeCells('A1:K1');
            $sheet->mergeCells('A2:K2');
            $sheet->mergeCells('A3:K3');
            $sheet->mergeCells('A4:K4');

            $sheet->getStyle('A1:A4')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle('A1:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Header tabel
            $headers = ['No', 'Kode Order', 'Tgl Order', 'Tgl Menu', 'Menu', 'Paket', 'Harga Satuan', 'Total Harga', 'Customer', 'Kelas', 'Status'];
            $col = 'A';
            $rowHeader = 6;
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $rowHeader, $header);
                $col++;
            }

            $sheet->getStyle('A6:K6')->getFont()->setBold(true);
            $sheet->getStyle('A6:K6')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
            $sheet->getStyle('A6:K6')->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle('A6:K6')->getFill()->getStartColor()->setARGB('FF2563EB');
            $sheet->getStyle('A6:K6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A6:K6')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            // Data
            $row = 7;
            $no = 1;
            foreach ($orders as $order) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $order->kode_order);
                $sheet->setCellValue('C' . $row, Carbon::parse($order->tanggal_order)->format('d/m/Y'));
                $sheet->setCellValue('D' . $row, Carbon::parse($order->tanggal_menu)->format('d/m/Y'));
                $sheet->setCellValue('E' . $row, $order->menu ? $order->menu->nama_menu : '-');
                $sheet->setCellValue('F' . $row, ucfirst($order->paket ?? '-'));
                $sheet->setCellValue('G' . $row, $order->harga_satuan);
                $sheet->setCellValue('H' . $row, $order->total_harga);
                $sheet->setCellValue('I' . $row, $order->customer ? $order->customer->name : '-');
                $sheet->setCellValue('J' . $row, $order->customer ? $order->customer->kelas : '-');
                $sheet->setCellValue('K' . $row, $order->payment_status == 'paid' ? 'Lunas' : 'Belum Bayar');

                $sheet->getStyle('A' . $row . ':K' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
                
                if ($order->payment_status == 'paid') {
                    $sheet->getStyle('K' . $row)->getFont()->getColor()->setARGB('FF16A34A');
                } else {
                    $sheet->getStyle('K' . $row)->getFont()->getColor()->setARGB('FFDC2626');
                }
                
                $row++;
            }

            // Total
            $sheet->setCellValue('G' . $row, 'TOTAL');
            $sheet->setCellValue('H' . $row, $orders->sum('total_harga'));
            $sheet->getStyle('G' . $row . ':H' . $row)->getFont()->setBold(true);
            $sheet->getStyle('G' . $row . ':H' . $row)->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle('G' . $row . ':H' . $row)->getFill()->getStartColor()->setARGB('FFE5E7EB');
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');

            foreach (range('A', 'K') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $filename = 'Laporan_Order_Filter_' . date('Ymd_His') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            Log::error('Export Excel Filtered Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal export Excel: ' . $e->getMessage());
        }
    }

    public function print($id)
    {
        try {
            $order = Order::with(['customer', 'menu'])->findOrFail($id);
            return view('admin.invoice', compact('order'));

        } catch (\Exception $e) {
            return back()->with('error', 'Order tidak ditemukan');
        }
    }

    public function destroy($id)
    {
        try {
            $order = Order::findOrFail($id);
            Report::where(function($query) use ($order) {
                            $query->where('keterangan', 'LIKE', '%Pembayaran Order: ' . $order->kode_order . '%')
                                  ->orWhere('keterangan', 'LIKE', '%' . $order->kode_order . '%');
                        })
                        // ->where('order_id', $order->id) // Tambahkan ini jika ada kolom order_id
                        ->delete();
            $order->delete();

            return back()->with('success', 'Order berhasil diarsipkan/dihapus');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus order');
        }
    }
}