<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class);

use Illuminate\Support\Facades\DB;

echo "Starting price fix from CSV...\n";

$csvFile = 'FPL-Elo-Insights/data/2025-2026/By Gameweek/GW3/playerstats.csv';

if (!file_exists($csvFile)) {
    echo "CSV file not found: $csvFile\n";
    exit(1);
}

$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle);

// Find the column indices
$idIndex = array_search('id', $header);
$nowCostIndex = array_search('now_cost', $header);
$gwIndex = array_search('gw', $header);

if ($idIndex === false || $nowCostIndex === false) {
    echo "Required columns not found in CSV\n";
    exit(1);
}

$updated = 0;
$errors = 0;

while (($row = fgetcsv($handle)) !== false) {
    if (count($row) <= max($idIndex, $nowCostIndex)) {
        continue;
    }
    
    $playerId = (int)$row[$idIndex];
    $nowCost = (float)$row[$nowCostIndex];
    $gameweek = isset($row[$gwIndex]) ? (int)$row[$gwIndex] : 3;
    
    if ($playerId && $nowCost > 0) {
        try {
            // Convert to FPL format (multiply by 10)
            $fplPrice = (int)($nowCost * 10);
            
            $result = DB::table('player_stats')
                ->where('player_id', $playerId)
                ->where('gameweek', $gameweek)
                ->update(['now_cost' => $fplPrice]);
                
            if ($result > 0) {
                $updated++;
                if ($updated % 100 == 0) {
                    echo "Updated $updated records...\n";
                }
            }
        } catch (Exception $e) {
            $errors++;
            if ($errors < 10) {
                echo "Error updating player $playerId: " . $e->getMessage() . "\n";
            }
        }
    }
}

fclose($handle);

echo "Price fix completed!\n";
echo "Updated: $updated records\n";
echo "Errors: $errors\n";

// Verify the results
$minPrice = DB::table('player_stats')->where('gameweek', 3)->min('now_cost');
$maxPrice = DB::table('player_stats')->where('gameweek', 3)->max('now_cost');
$avgPrice = DB::table('player_stats')->where('gameweek', 3)->avg('now_cost');

echo "\nFinal price ranges:\n";
echo "Min: $minPrice (£" . ($minPrice/10) . "m)\n";
echo "Max: $maxPrice (£" . ($maxPrice/10) . "m)\n";
echo "Average: " . round($avgPrice, 1) . " (£" . round($avgPrice/10, 1) . "m)\n";