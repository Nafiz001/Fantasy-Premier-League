<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use App\Services\FPLGameweek1Service;

class AutoSeedFPLData
{
    /**
     * Handle an incoming request and auto-seed FPL data if needed
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Only check on web routes, not API routes
        if ($request->is('api/*')) {
            return $next($request);
        }

        // Use cache to avoid checking database on every request
        $lastSeedCheck = Cache::get('fpl_seed_check', 0);
        $now = time();

        // Only check every 5 minutes to avoid performance issues
        if ($now - $lastSeedCheck < 300) {
            return $next($request);
        }

        Cache::put('fpl_seed_check', $now, 300); // Cache for 5 minutes

        try {
            // Check if FPL data exists
            $hasTeams = DB::table('teams')->count() > 0;
            $hasPlayers = DB::table('players')->count() > 0;
            $hasGameweeks = DB::table('gameweeks')->count() > 0;

            // If any core data is missing, trigger seeding
            if (!$hasTeams || !$hasPlayers || !$hasGameweeks) {
                \Log::info('Auto-seeding FPL data due to missing core data');

                // Run GitHub data seeding (GW2-38)
                Artisan::call('db:seed', [
                    '--class' => 'FPLDataSeeder',
                    '--force' => true
                ]);

                \Log::info('GitHub FPL data seeding completed');

                // Import GW1 data from FPL API
                try {
                    $gw1Service = new FPLGameweek1Service();
                    $gw1Service->importGameweek1Data();
                    \Log::info('GW1 API import completed');
                } catch (\Exception $e) {
                    \Log::error('GW1 API import failed: ' . $e->getMessage());
                }

                \Log::info('Full FPL data seeding completed automatically');
            }

        } catch (\Exception $e) {
            \Log::error('Auto-seed check failed: ' . $e->getMessage());
        }

        return $next($request);
    }
}
