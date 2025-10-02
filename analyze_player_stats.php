<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Player Gameweek Stats Analysis:\n";
echo "================================\n\n";

// Check stats for finished gameweeks
$finishedGWs = [16, 17, 35, 36, 37];

foreach ($finishedGWs as $gw) {
    $stats = DB::table('player_gameweek_stats')
        ->where('gameweek', $gw)
        ->selectRaw('COUNT(*) as player_count, SUM(total_points) as total_points, MAX(total_points) as max_points')
        ->first();

    echo "GW{$gw}: {$stats->player_count} players, Total: {$stats->total_points} pts, Max: {$stats->max_points} pts\n";

    if ($stats->player_count > 0) {
        // Show top 5 scorers
        $topScorers = DB::table('player_gameweek_stats')
            ->join('players', 'player_gameweek_stats.player_id', '=', 'players.fpl_id')
            ->where('player_gameweek_stats.gameweek', $gw)
            ->orderBy('player_gameweek_stats.total_points', 'desc')
            ->limit(5)
            ->select('players.web_name', 'player_gameweek_stats.player_id', 'player_gameweek_stats.total_points')
            ->get();

        echo "  Top scorers:\n";
        foreach ($topScorers as $scorer) {
            echo "    {$scorer->web_name} (ID: {$scorer->player_id}): {$scorer->total_points} pts\n";
        }
        echo "\n";
    }
}

echo "\n";
echo "User's Squad Check:\n";
echo "===================\n";
$userId = 3;
$user = DB::table('users')->where('id', $userId)->first();
$startingXI = json_decode($user->starting_xi, true);

echo "User's player IDs: " . implode(', ', array_slice($startingXI, 0, 5)) . "...\n\n";

// Check if any of user's players have stats
foreach (array_slice($finishedGWs, 0, 2) as $gw) {
    $userPlayerStats = DB::table('player_gameweek_stats')
        ->join('players', 'player_gameweek_stats.player_id', '=', 'players.fpl_id')
        ->whereIn('player_gameweek_stats.player_id', $startingXI)
        ->where('player_gameweek_stats.gameweek', $gw)
        ->select('players.web_name', 'player_gameweek_stats.player_id', 'player_gameweek_stats.total_points')
        ->get();

    echo "GW{$gw} - User's players with stats: {$userPlayerStats->count()}\n";
    foreach ($userPlayerStats as $stat) {
        echo "  {$stat->web_name} (ID: {$stat->player_id}): {$stat->total_points} pts\n";
    }
    echo "\n";
}
