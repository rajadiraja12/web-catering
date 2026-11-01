<?php

namespace App\Http\Middleware;

use App\Exports\BackupExport;
use App\Models\Backup;
use App\Traits\CheckBackupTrait;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class CheckBackup
{
    use CheckBackupTrait;
    public function handle($request, Closure $next)
    {
        // Cek apakah hari ini tanggal 1 (awal bulan)
        if (Carbon::now()->day == 1) {
            // Cek apakah backup bulan ini sudah ada
            $monthName = Carbon::now()->subMonth()->format('F_Y');
            $backupExists = Backup::where('nama_backup', 'LIKE', '%' . $monthName . '%')->exists();
            
            if (!$backupExists) {
                // Buat backup bulan lalu
                $this->createMonthlyBackup();
            }
        }

        $this->checkAndCreateBackup();
        
        return $next($request);
    }

    private function createMonthlyBackup()
    {
        try {
            $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
            $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
            $monthName = Carbon::now()->subMonth()->format('F_Y');

            $backupName = 'Backup_Keuangan_' . $monthName . '_' . Carbon::now()->format('d-m-Y');
            $filename = $backupName . '.xlsx';
            $path = 'backups/' . $filename;

            Excel::store(new BackupExport($lastMonthStart, $lastMonthEnd, 'otomatis'), $path, 'public');

            Backup::create([
                'nama_backup' => $backupName,
                'lokasi_file' => $path,
                'type' => 'otomatis',
                'keterangan_data' => "Backup data keuangan bulan " . $monthName,
                'size' => Storage::disk('public')->size($path) / 1024,
                'created_by' => 1,
                'backup_date' => now(),
            ]);

            Log::info('Backup otomatis bulan ' . $monthName . ' berhasil dibuat');

        } catch (\Exception $e) {
            Log::error('Gagal membuat backup otomatis: ' . $e->getMessage());
        }
    }
}