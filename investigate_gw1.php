<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Investigating GW1 negative points issue...\n\n";

// Check what data we have for GW1
$gw1Stats = DB::table('player_gameweek_stats')
    ->where('gameweek', 1)
    ->orderBy('total_points', 'desc')
    ->limit(10)
    ->get();

echo "Top 10 GW1 players by total_points:\n";
foreach ($gw1Stats as $stat) {
    echo "Player {$stat->player_id}: {$stat->total_points} points, {$stat->minutes} minutes\n";
}

echo "\nBottom 10 GW1 players by total_points:\n";
$gw1StatsBottom = DB::table('player_gameweek_stats')
    ->where('gameweek', 1)
    ->orderBy('total_points', 'asc')
    ->limit(10)
    ->get();

foreach ($gw1StatsBottom as $stat) {
    echo "Player {$stat->player_id}: {$stat->total_points} points, {$stat->minutes} minutes\n";
}

// Check User 1's squad for GW1
echo "\nUser 1's squad for GW1:\n";
$user = DB::table('users')->where('id', 1)->first();
if ($user && $user->starting_xi) {
    $squad = json_decode($user->starting_xi, true);
    if (is_array($squad)) {
        $totalUserPoints = 0;
        foreach ($squad as $playerId) {
            $playerStats = DB::table('player_gameweek_stats')
                ->where('player_id', $playerId)
                ->where('gameweek', 1)
                ->first();

            if ($playerStats) {
                echo "Player {$playerId}: {$playerStats->total_points} points\n";
                $totalUserPoints += $playerStats->total_points;
            } else {
                echo "Player {$playerId}: NO GW1 DATA\n";
            }
        }
        echo "Manual calculation total: {$totalUserPoints} points\n";
    }
}

// Let's also check what the GitHub data actually contains for GW1
echo "\nChecking GitHub GW1 data sample...\n";
