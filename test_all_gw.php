<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Testing GW1 data...\n";

// Check GW1 data in database
$gw1Players = DB::table('player_gameweek_stats')
    ->where('gameweek', 1)
    ->whereNotIn('total_points', [0])
    ->limit(5)
    ->get();

if ($gw1Players->count() > 0) {
    echo "Found players with non-zero points in GW1:\n";
    foreach ($gw1Players as $player) {
        echo "Player {$player->player_id}: points={$player->total_points}, minutes={$player->minutes}\n";
    }
} else {
    echo "No players with non-zero points in GW1\n";

    // Check if we have any data at all
    $allGw1 = DB::table('player_gameweek_stats')->where('gameweek', 1)->limit(3)->get();
    echo "\nSample GW1 data:\n";
    foreach ($allGw1 as $player) {
        echo "Player {$player->player_id}: points={$player->total_points}, minutes={$player->minutes}\n";
    }
}

// Check what gameweeks actually have positive points
echo "\nChecking all gameweeks for positive points...\n";
for ($gw = 1; $gw <= 4; $gw++) {
    $positivePoints = DB::table('player_gameweek_stats')
        ->where('gameweek', $gw)
        ->where('total_points', '>', 0)
        ->count();
    echo "GW{$gw}: {$positivePoints} players with positive points\n";
}

// Test the points calculation for user 1 in different gameweeks
echo "\nTesting points service for different gameweeks...\n";
$service = new \App\Services\FPLPointsService();
for ($gw = 1; $gw <= 4; $gw++) {
    $result = $service->getSquadPointsForGameweek(1, $gw);
    echo "User 1 GW{$gw}: " . ($result['total_points'] ?? 0) . " points\n";
}
