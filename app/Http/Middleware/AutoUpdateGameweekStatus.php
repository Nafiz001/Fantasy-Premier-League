<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class AutoUpdateGameweekStatus
{
    /**
     * Handle an incoming request and auto-update gameweek status if needed
     */
    public function handle(Request $request, Closure $next)
    {
        // Only check on web routes, not API routes
        if ($request->is('api/*')) {
            return $next($request);
        }

        // Use cache to avoid checking on every request
        $lastStatusCheck = Cache::get('gameweek_status_check', 0);
        $now = time();

        // Only check every 10 minutes to avoid performance issues
        if ($now - $lastStatusCheck < 600) {
            return $next($request);
        }

        Cache::put('gameweek_status_check', $now, 600); // Cache for 10 minutes

        try {
            // Run gameweek status update in the background
            Artisan::call('gameweek:update-status');

            Log::info('Gameweek status automatically updated');

        } catch (\Exception $e) {
            Log::error('Auto gameweek status update failed: ' . $e->getMessage());
        }

        return $next($request);
    }
}
