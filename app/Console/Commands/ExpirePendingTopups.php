<?php
// app/Console/Commands/ExpirePendingTopups.php

namespace App\Console\Commands;

use App\Models\TopUp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpirePendingTopups extends Command
{
    protected $signature = 'topup:expire';
    protected $description = 'Expire pending topups that are older than 24 hours';

    public function handle()
    {
        $expired = TopUp::where('status', 'pending')
            ->where('created_at', '<', now()->subHours(24))
            ->update(['status' => 'expired']);

        Log::info('Expired pending topups', ['count' => $expired]);
        $this->info("Expired {$expired} pending topups.");

        return Command::SUCCESS;
    }
}