<?php
// app/Http/Middleware/ContentSecurityPolicy.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Hanya untuk local/development
        if (app()->isLocal()) {
            $response->header('Content-Security-Policy', "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://app.sandbox.midtrans.com https://snap-assets.sandbox.midtrans.com https://api.sandbox.midtrans.com https://pay.google.com https://gwk.gopayapi.com https://www.googletagmanager.com https://g.alicdn.com https://cdn.jsdelivr.net; frame-src 'self' https://app.sandbox.midtrans.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:");
        }

        return $response;
    }
}