<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Testing simplified points calculation...\n";

// Test different gameweeks to see if points work
$service = new \App\Services\FPLPointsService();
for ($gw = 1; $gw <= 4; $gw++) {
    $result = $service->getSquadPointsForGameweek(1, $gw);
    echo "User 1 GW{$gw}: " . ($result['total_points'] ?? 0) . " points (from " . ($result['count'] ?? 0) . " players)\n";
}

// Check specific players in GW2 since it has the most positive points
echo "\nChecking GW2 player data (which has most positive points)...\n";
$gw2Players = DB::table('player_gameweek_stats')
    ->where('gameweek', 2)
    ->where('total_points', '>', 0)
    ->limit(5)
    ->get();

foreach ($gw2Players as $player) {
    echo "Player {$player->player_id}: {$player->total_points} points, {$player->minutes} minutes\n";
}

// Test with a specific player from GW2
if ($gw2Players->count() > 0) {
    $testPlayer = $gw2Players->first();
    echo "\nTesting points calculation for player {$testPlayer->player_id} in GW2...\n";

    $playerModel = DB::table('players')->where('id', $testPlayer->player_id)->first();
    if ($playerModel) {
        echo "Player found: {$playerModel->first_name} {$playerModel->second_name}\n";
        echo "Database total_points: {$testPlayer->total_points}\n";
    }
}
