<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixPlayerPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fpl:fix-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix player prices from CSV data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting price fix from CSV...');
        
        $csvFile = base_path('FPL-Elo-Insights/data/2025-2026/By Gameweek/GW3/playerstats.csv');
        
        if (!file_exists($csvFile)) {
            $this->error("CSV file not found: $csvFile");
            return 1;
        }

        $handle = fopen($csvFile, 'r');
        $header = fgetcsv($handle);

        // Find the column indices
        $idIndex = array_search('id', $header);
        $nowCostIndex = array_search('now_cost', $header);

        if ($idIndex === false || $nowCostIndex === false) {
            $this->error('Required columns not found in CSV');
            fclose($handle);
            return 1;
        }

        $updated = 0;
        $errors = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) <= max($idIndex, $nowCostIndex)) {
                continue;
            }
            
            $playerId = (int)$row[$idIndex];
            $nowCost = (float)$row[$nowCostIndex];
            
            if ($playerId && $nowCost > 0) {
                try {
                    // Convert to FPL format (multiply by 10)
                    $fplPrice = (int)($nowCost * 10);
                    
                    $result = DB::table('player_stats')
                        ->where('player_id', $playerId)
                        ->where('gameweek', 3)
                        ->update(['now_cost' => $fplPrice]);
                        
                    if ($result > 0) {
                        $updated++;
                        if ($updated % 100 == 0) {
                            $this->info("Updated $updated records...");
                        }
                    }
                } catch (\Exception $e) {
                    $errors++;
                    if ($errors < 10) {
                        $this->error("Error updating player $playerId: " . $e->getMessage());
                    }
                }
            }
        }

        fclose($handle);

        $this->info("Price fix completed!");
        $this->info("Updated: $updated records");
        $this->info("Errors: $errors");

        // Verify the results
        $minPrice = DB::table('player_stats')->where('gameweek', 3)->min('now_cost');
        $maxPrice = DB::table('player_stats')->where('gameweek', 3)->max('now_cost');
        $avgPrice = DB::table('player_stats')->where('gameweek', 3)->avg('now_cost');

        $this->info("\nFinal price ranges:");
        $this->info("Min: $minPrice (£" . ($minPrice/10) . "m)");
        $this->info("Max: $maxPrice (£" . ($maxPrice/10) . "m)");
        $this->info("Average: " . round($avgPrice, 1) . " (£" . round($avgPrice/10, 1) . "m)");

        return 0;
    }
}
