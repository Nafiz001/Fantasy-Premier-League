<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

use Illuminate\Support\Facades\DB;

echo "=== FPL Database Check ===\n";
echo "Teams: " . DB::table('teams')->count() . "\n";
echo "Players: " . DB::table('players')->count() . "\n";
echo "Player Stats: " . DB::table('player_stats')->count() . "\n";
echo "Gameweeks: " . DB::table('gameweeks')->count() . "\n";
echo "Finished gameweeks: " . DB::table('gameweeks')->where('finished', true)->count() . "\n";

echo "\n=== Latest Finished Gameweeks ===\n";
$finishedGameweeks = DB::table('gameweeks')
    ->where('finished', true)
    ->orderBy('deadline_time', 'desc')
    ->limit(3)
    ->get(['gameweek_id', 'name', 'finished']);

foreach ($finishedGameweeks as $gw) {
    echo "GW{$gw->gameweek_id}: {$gw->name}\n";
}

echo "\n=== Sample Player Stats (Latest Gameweek) ===\n";
$latestGW = DB::table('gameweeks')
    ->where('finished', true)
    ->orderBy('deadline_time', 'desc')
    ->first();

if ($latestGW) {
    echo "Latest finished gameweek: GW{$latestGW->gameweek_id} - {$latestGW->name}\n";

    $sampleStats = DB::table('player_stats')
        ->join('players', 'player_stats.player_id', '=', 'players.fpl_id')
        ->where('player_stats.gameweek', $latestGW->gameweek_id)
        ->where('player_stats.minutes', '>', 0)
        ->limit(5)
        ->get([
            'players.web_name',
            'players.position',
            'player_stats.minutes',
            'player_stats.goals_scored',
            'player_stats.assists',
            'player_stats.clean_sheets',
            'player_stats.bonus'
        ]);

    foreach ($sampleStats as $stat) {
        echo "- {$stat->web_name} ({$stat->position}): {$stat->minutes}min, {$stat->goals_scored}g, {$stat->assists}a, CS:{$stat->clean_sheets}, Bonus:{$stat->bonus}\n";
    }
} else {
    echo "No finished gameweeks found.\n";
}
