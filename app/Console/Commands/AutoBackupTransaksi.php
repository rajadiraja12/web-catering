<?php

namespace App\Console\Commands;

use App\Models\Backup;
use App\Exports\OrderBackupExport;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AutoBackupTransaksi extends Command
{
    protected $signature = 'backup:transaksi-auto';
    protected $description = 'Backup transaksi otomatis akhir bulan';

    public function handle()
    {
        $this->info('Memulai backup transaksi otomatis...');

        try {
            // Periode bulan lalu
            $startDate = Carbon::now()->subMonth()->startOfMonth();
            $endDate = Carbon::now()->subMonth()->endOfMonth();

            // Cek apakah backup bulan ini sudah ada
            $monthName = $startDate->format('F_Y');
            $exists = Backup::where('nama_backup', 'LIKE', "%{$monthName}%")
                ->where('type', 'otomatis')
                ->exists();

            if ($exists) {
                $this->info("Backup untuk bulan {$monthName} sudah ada, skip...");
                return Command::SUCCESS;
            }

            $this->info("Membuat backup transaksi bulan: {$monthName}");

            // Generate backup
            $result = OrderBackupExport::generateAndSave($startDate, $endDate, 'otomatis');

            // Simpan ke database
            $backup = Backup::create([
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

            $this->info('✅ Backup transaksi berhasil dibuat: ' . $backup->nama_backup);
            $this->info('📁 Lokasi: ' . $result['path']);
            $this->info('📊 Ukuran: ' . number_format($result['size'], 2) . ' KB');

            // Hapus backup lama (lebih dari 12 bulan)
            $this->deleteOldBackups();

            Log::info('Auto backup transaksi success: ' . $backup->nama_backup);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Gagal membuat backup: ' . $e->getMessage());
            Log::error('Auto backup transaksi failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function deleteOldBackups()
    {
        $oldBackups = Backup::where('type', 'otomatis')
            ->where('created_at', '<', Carbon::now()->subMonths(12))
            ->get();

        foreach ($oldBackups as $backup) {
            if (Storage::disk('public')->exists($backup->lokasi_file)) {
                Storage::disk('public')->delete($backup->lokasi_file);
            }
            $backup->delete();
        }

        $this->info("🗑️ Menghapus " . $oldBackups->count() . " backup lama (lebih dari 12 bulan)");
    }
}