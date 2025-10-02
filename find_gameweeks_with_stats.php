<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Finding Gameweeks with Player Stats:\n";
echo "====================================\n\n";

// Get all unique gameweeks from player_gameweek_stats
$gwsWithStats = DB::table('player_gameweek_stats')
    ->selectRaw('gameweek, COUNT(*) as player_count, SUM(total_points) as total_points')
    ->groupBy('gameweek')
    ->orderBy('gameweek')
    ->get();

echo "Gameweeks with player stats:\n";
foreach ($gwsWithStats as $gw) {
    echo "GW{$gw->gameweek}: {$gw->player_count} players, {$gw->total_points} total points\n";
}

echo "\n";
echo "Gameweeks table mapping:\n";
echo "========================\n";
$gameweeks = DB::table('gameweeks')
    ->orderBy('id')
    ->limit(10)
    ->get();

foreach ($gameweeks as $gw) {
    echo "ID {$gw->id}: {$gw->name} (Finished: " . ($gw->finished ? 'Yes' : 'No') . ")\n";
}
