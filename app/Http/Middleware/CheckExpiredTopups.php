<?php
// app/Http/Middleware/CheckExpiredTopups.php

namespace App\Http\Middleware;

use App\Models\TopUp;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckExpiredTopups
{
    public function handle(Request $request, Closure $next)
    {
        // Cek dan expire topup yang pending > 24 jam
        $expiredCount = TopUp::where('status', 'pending')
            ->where('created_at', '<', now()->subHours(24))
            ->update(['status' => 'expired']);

        if ($expiredCount > 0) {
            Log::info('Auto expired topups via middleware', ['count' => $expiredCount]);
        }

        return $next($request);
    }
}