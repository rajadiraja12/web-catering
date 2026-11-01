<?php
// app/Exports/UsersExport.php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $isTemplate;

    public function __construct($isTemplate = false)
    {
        $this->isTemplate = $isTemplate;
    }

    public function collection()
    {
        if ($this->isTemplate) {
            // Return empty collection with sample data for template
            return collect([
                (object) [
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
                (object) [
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
            ]);
        }

        return User::where('role', 'customer')
            ->select('username', 'name', 'kelas', 'no_hp', 'alergi', 'role', 'status', 'hutang_limit')
            ->orderBy('kelas')
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
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
    }

    public function map($user): array
    {
        // For template, use object properties directly
        if ($this->isTemplate) {
            return [
                $user->username,
                $user->name,
                $user->kelas,
                $user->no_hp,
                $user->alergi,
                $user->password,
                $user->role,
                $user->status,
                $user->hutang_limit ?? 0,
            ];
        }

        return [
            $user->username,
            $user->name,
            $user->kelas,
            $user->no_hp,
            $user->alergi ?? '-',
            '', // Password tidak diexport untuk keamanan
            $user->role,
            $user->status,
            $user->hutang_limit ?? 0,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style untuk header (baris 1)
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Set tinggi baris header
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Style untuk semua cell
        $sheet->getStyle('A1:I' . $sheet->getHighestRow())
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Border untuk semua data
        $sheet->getStyle('A1:I' . $sheet->getHighestRow())
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
            ->setColor(['rgb' => 'CCCCCC']);

        // Color untuk data (zebra)
        $highestRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':I' . $row)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('F2F2F2');
            }
        }

        return [
            // Style tambahan jika diperlukan
        ];
    }
}