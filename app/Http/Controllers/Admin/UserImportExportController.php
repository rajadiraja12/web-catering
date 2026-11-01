<?php
// app/Http/Controllers/Admin/UserImportExportController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\Hash;

class UserImportExportController extends Controller
{
    /**
     * Export users to Excel
     */
    public function export()
    {
        // Create new Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            'username',
            'name',
            'kelas',
            'no_hp',
            'alergi',
            'role',
            'status',
            'hutang_limit',
        ];

        // Write headers
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Style header - menggunakan array dengan rgb
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => [
                    'rgb' => 'FFFFFF'
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '4472C4'
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Get data users (only customer)
        $users = User::where('role', 'customer')
            ->orderBy('kelas')
            ->orderBy('name')
            ->get();

        // Write data
        $row = 2;
        foreach ($users as $user) {
            $sheet->setCellValue('A' . $row, $user->username);
            $sheet->setCellValue('B' . $row, $user->name);
            $sheet->setCellValue('C' . $row, $user->kelas);
            $sheet->setCellValue('D' . $row, $user->no_hp);
            $sheet->setCellValue('E' . $row, $user->alergi ?? '-');
            $sheet->setCellValue('F' . $row, $user->role);
            $sheet->setCellValue('G' . $row, $user->status);
            $sheet->setCellValue('H' . $row, $user->hutang_limit ?? 0);
            $row++;
        }

        // Auto size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Apply border to all data - menggunakan array
        $lastRow = $row - 1;
        if ($lastRow >= 1) {
            $sheet->getStyle('A1:H' . $lastRow)
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->setColor(new Color('FFCCCCCC'));
        }

        // Create writer
        $writer = new Xlsx($spreadsheet);
        
        // Set filename
        $filename = 'data_user_' . date('Y-m-d') . '.xlsx';
        
        // Save to temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'export_');
        $writer->save($tempFile);
        
        // Return download response
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Download template Excel
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            'username',
            'name',
            'kelas',
            'no_hp',
            'alergi',
            'password',
            'role',
            'status',
            'hutang_limit',
        ];

        // Write headers
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Style header - menggunakan array dengan rgb
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => [
                    'rgb' => 'FFFFFF'
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '4472C4'
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Sample data
        $sampleData = [
            [
                'username' => '12012345',
                'name' => 'John Doe',
                'kelas' => '1-Alpukat',
                'no_hp' => '08123456789',
                'alergi' => '-',
                'password' => 'password123',
                'role' => 'customer',
                'status' => 'Active',
                'hutang_limit' => 500000,
            ],
            [
                'username' => '12012346',
                'name' => 'Jane Smith',
                'kelas' => '2-Mangga',
                'no_hp' => '08123456780',
                'alergi' => 'Kacang',
                'password' => 'password123',
                'role' => 'customer',
                'status' => 'Active',
                'hutang_limit' => 300000,
            ],
        ];

        // Write sample data
        $row = 2;
        foreach ($sampleData as $data) {
            $sheet->setCellValue('A' . $row, $data['username']);
            $sheet->setCellValue('B' . $row, $data['name']);
            $sheet->setCellValue('C' . $row, $data['kelas']);
            $sheet->setCellValue('D' . $row, $data['no_hp']);
            $sheet->setCellValue('E' . $row, $data['alergi']);
            $sheet->setCellValue('F' . $row, $data['password']);
            $sheet->setCellValue('G' . $row, $data['role']);
            $sheet->setCellValue('H' . $row, $data['status']);
            $sheet->setCellValue('I' . $row, $data['hutang_limit']);
            $row++;
        }

        // Add note row
        $sheet->setCellValue('A' . $row, 'Catatan:');
        $sheet->setCellValue('B' . $row, 'Kolom wajib: username, name, kelas, no_hp');
        $sheet->setCellValue('D' . $row, 'Format kelas: kelas-nama_kelas (contoh: 1-Alpukat)');
        $sheet->setCellValue('F' . $row, 'Password default: password123');
        $sheet->mergeCells('B' . $row . ':I' . $row);
        
        // Style untuk catatan - menggunakan array dengan rgb
        $sheet->getStyle('A' . $row)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => [
                    'rgb' => 'FF0000'
                ],
            ],
        ]);
        $sheet->getStyle('B' . $row . ':I' . $row)->applyFromArray([
            'font' => [
                'size' => 10,
                'color' => [
                    'rgb' => '666666'
                ],
            ],
        ]);

        // Auto size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Apply border
        $lastRow = $row;
        $sheet->getStyle('A1:I' . $lastRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new Color('FFCCCCCC'));

        $writer = new Xlsx($spreadsheet);
        $filename = 'template_import_user.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'template_');
        $writer->save($tempFile);
        
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Show import form
     */
    public function showImportForm()
    {
        return view('admin.import_user');
    }

    /**
     * Import users from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120', // Max 5MB
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Remove header row
            $header = array_shift($rows);
            
            // Validate header
            $expectedHeaders = ['username', 'name', 'kelas', 'no_hp', 'alergi'];
            $headerValid = true;
            foreach ($expectedHeaders as $index => $expected) {
                if (!isset($header[$index]) || strtolower(trim($header[$index])) !== $expected) {
                    $headerValid = false;
                    break;
                }
            }

            if (!$headerValid) {
                return redirect()->route('admin.users')
                    ->with('error', 'Format header tidak sesuai. Gunakan template yang telah disediakan.');
            }

            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($rows as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2; // +2 because header is row 1

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Map row data
                $data = [
                    'username' => trim($row[0] ?? ''),
                    'name' => trim($row[1] ?? ''),
                    'kelas' => trim($row[2] ?? ''),
                    'no_hp' => trim($row[3] ?? ''),
                    'alergi' => trim($row[4] ?? '-'),
                    'password' => trim($row[5] ?? 'password123'),
                    'role' => trim($row[6] ?? 'customer'),
                    'status' => trim($row[7] ?? 'Active'),
                    'hutang_limit' => isset($row[8]) ? (int) $row[8] : 0,
                ];

                // Validate required fields
                if (empty($data['username']) || empty($data['name']) || empty($data['kelas']) || empty($data['no_hp'])) {
                    $failedCount++;
                    $errors[] = "Baris {$rowNumber}: Kolom wajib (username, name, kelas, no_hp) tidak boleh kosong";
                    continue;
                }

                // Check username already exists
                if (User::where('username', $data['username'])->exists()) {
                    $failedCount++;
                    $errors[] = "Baris {$rowNumber}: Username '{$data['username']}' sudah terdaftar";
                    continue;
                }


                // Validate kelas format
                $kelasParts = explode('-', $data['kelas']);
                if (count($kelasParts) != 2 || empty($kelasParts[0]) || empty($kelasParts[1])) {
                    $failedCount++;
                    $errors[] = "Baris {$rowNumber}: Format kelas '{$data['kelas']}' tidak valid. Gunakan format: kelas-nama_kelas (contoh: 1-Alpukat)";
                    continue;
                }

                // Force role to customer
                $data['role'] = 'customer';

                // Validate status
                if (!in_array($data['status'], ['Active', 'Non Aktif'])) {
                    $data['status'] = 'Active';
                }

                // Validate hutang_limit
                if ($data['hutang_limit'] < 0) {
                    $data['hutang_limit'] = 0;
                }

                // Create user
                try {
                    User::create([
                        'username' => $data['username'],
                        'name' => $data['name'],
                        'kelas' => $data['kelas'],
                        'no_hp' => $data['no_hp'],
                        'alergi' => $data['alergi'],
                        'password' => Hash::make($data['password']),
                        'role' => $data['role'],
                        'status' => $data['status'],
                        'hutang_limit' => '500000',
                        'hutang_used' => 0,
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "Baris {$rowNumber}: Gagal menyimpan data - " . $e->getMessage();
                }
            }

            if ($successCount > 0) {
                $message = "Berhasil mengimport {$successCount} user";
                if ($failedCount > 0) {
                    $message .= " (Gagal: {$failedCount})";
                }
                return redirect()->route('admin.users')
                    ->with('success', $message)
                    ->with('import_errors', $errors);
            } else {
                $errorMessage = "Gagal mengimport data. Periksa format file.";
                if (!empty($errors)) {
                    $errorMessage .= "\n\n" . implode("\n", array_slice($errors, 0, 5));
                    if (count($errors) > 5) {
                        $errorMessage .= "\n... dan " . (count($errors) - 5) . " error lainnya";
                    }
                }
                return redirect()->route('admin.users')
                    ->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.users')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}