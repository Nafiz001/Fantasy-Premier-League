<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$userId = 3;
$user = DB::table('users')->where('id', $userId)->first();

echo "User: {$user->name}\n";
echo "Current points in DB: {$user->points}\n";
echo "\n";

// Parse starting XI
$startingXI = json_decode($user->starting_xi, true);
echo "Squad player IDs: " . implode(', ', $startingXI) . "\n\n";

// Get finished gameweeks
$finishedGameweeks = DB::table('gameweeks')
    ->where('finished', true)
    ->orderBy('id')
    ->get();

echo "Finished Gameweeks: " . $finishedGameweeks->pluck('id')->implode(', ') . "\n\n";

// Calculate points for each finished gameweek
$totalPoints = 0;

foreach ($finishedGameweeks as $gw) {
    echo "=== Gameweek {$gw->id} ({$gw->name}) ===\n";

    // Get player points for this gameweek
    $playerPoints = DB::table('player_gameweek_stats')
        ->whereIn('player_id', $startingXI)
        ->where('gameweek', $gw->id)
        ->select('player_id', 'total_points')
        ->get();

    $gwPoints = 0;
    foreach ($playerPoints as $pp) {
        echo "  Player {$pp->player_id}: {$pp->total_points} points\n";
        $gwPoints += $pp->total_points;
    }

    echo "  GW{$gw->id} Total: {$gwPoints} points\n\n";
    $totalPoints += $gwPoints;
}

echo "==========================\n";
echo "CALCULATED TOTAL: {$totalPoints} points\n";
echo "STORED IN DB: {$user->points} points\n";
echo "DIFFERENCE: " . ($totalPoints - $user->points) . " points\n\n";

if ($totalPoints != $user->points) {
    echo "⚠️ USER POINTS NEED TO BE UPDATED!\n";
    echo "Run this to update:\n";
    echo "UPDATE users SET points = {$totalPoints} WHERE id = {$userId};\n";
}
