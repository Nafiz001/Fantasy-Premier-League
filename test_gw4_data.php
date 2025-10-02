<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking GW4 data...\n";

$gw4Count = DB::table('player_gameweek_stats')->where('gameweek', 4)->count();
echo "GW4 stats count: $gw4Count\n";

// Check a few sample players
$samplePlayers = DB::table('player_gameweek_stats')
    ->where('gameweek', 4)
    ->limit(5)
    ->get();

echo "\nSample GW4 players:\n";
foreach ($samplePlayers as $player) {
    echo "Player {$player->player_id}: points={$player->total_points}, minutes={$player->minutes}, goals={$player->goals_scored}\n";
}

// Check if any players have more than 0 minutes
$playersWithMinutes = DB::table('player_gameweek_stats')
    ->where('gameweek', 4)
    ->where('minutes', '>', 0)
    ->count();
echo "\nPlayers with minutes > 0: $playersWithMinutes\n";

// Check the actual CSV data for comparison
echo "\nChecking raw CSV data for GW4...\n";
$csvFile = 'FPL-Elo-Insights/data/2025-2026/By Gameweek/GW4/player_gameweek_stats.csv';
if (file_exists($csvFile)) {
    $lines = file($csvFile, FILE_IGNORE_NEW_LINES);
    $headers = str_getcsv($lines[0]);

    echo "CSV headers: " . implode(', ', $headers) . "\n";

    // Show first data row
    if (isset($lines[1])) {
        $firstRow = str_getcsv($lines[1]);
        $data = array_combine($headers, $firstRow);
        echo "First CSV row - ID: {$data['id']}, total_points: {$data['total_points']}, minutes: {$data['minutes']}\n";
    }
}

// Test points calculation
echo "\nTesting points service...\n";
$service = new \App\Services\FPLPointsService();
$result = $service->getSquadPointsForGameweek(1, 4);
echo "User 1 GW4 points: " . ($result['total_points'] ?? 0) . "\n";
echo "Player count: " . ($result['count'] ?? 0) . "\n";
