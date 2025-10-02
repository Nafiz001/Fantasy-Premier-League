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
echo "Squad has " . count($startingXI) . " players\n";
echo "Player IDs: " . implode(', ', $startingXI) . "\n\n";

// Calculate points for gameweeks 1-5 (the ones with data)
$gameweeks = [1, 2, 3, 4, 5];
$totalPoints = 0;

foreach ($gameweeks as $gw) {
    echo "=== Gameweek {$gw} ===\n";

    // Get player points for this gameweek
    $playerPoints = DB::table('player_gameweek_stats')
        ->join('players', 'player_gameweek_stats.player_id', '=', 'players.fpl_id')
        ->whereIn('player_gameweek_stats.player_id', $startingXI)
        ->where('player_gameweek_stats.gameweek', $gw)
        ->select('players.web_name', 'player_gameweek_stats.player_id', 'player_gameweek_stats.total_points', 'player_gameweek_stats.minutes')
        ->orderBy('player_gameweek_stats.total_points', 'desc')
        ->get();

    $gwPoints = 0;
    foreach ($playerPoints as $pp) {
        echo "  {$pp->web_name} (ID: {$pp->player_id}): {$pp->total_points} pts ({$pp->minutes} mins)\n";
        $gwPoints += $pp->total_points;
    }

    echo "  Found {$playerPoints->count()} players\n";
    echo "  GW{$gw} Total: {$gwPoints} points\n\n";
    $totalPoints += $gwPoints;
}

echo "==========================\n";
echo "CALCULATED TOTAL: {$totalPoints} points\n";
echo "STORED IN DB: {$user->points} points\n";
echo "DIFFERENCE: " . ($totalPoints - $user->points) . " points\n\n";

if ($totalPoints != $user->points) {
    echo "✅ Updating user points...\n";
    DB::table('users')
        ->where('id', $userId)
        ->update(['points' => $totalPoints]);
    echo "✅ User points updated to {$totalPoints}!\n";
} else {
    echo "✅ Points are already correct!\n";
}
