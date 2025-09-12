<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test data relationships
$topPlayers = DB::table('players')
    ->join('teams', 'players.team_code', '=', 'teams.fpl_code')
    ->join('player_stats', 'players.fpl_id', '=', 'player_stats.player_id')
    ->select('players.web_name', 'teams.name as team_name', 'player_stats.total_points', 'players.position')
    ->where('player_stats.gameweek', 3)
    ->orderBy('player_stats.total_points', 'desc')
    ->limit(5)
    ->get();

echo "Top 5 Players (GW3):\n";
foreach($topPlayers as $player) {
    echo $player->web_name . ' (' . $player->team_name . ', ' . $player->position . ') - ' . $player->total_points . " points\n";
}

echo "\nTeam stats:\n";
$teamStats = DB::table('teams')
    ->select('name', 'elo', 'strength')
    ->orderBy('elo', 'desc')
    ->limit(5)
    ->get();

foreach($teamStats as $team) {
    echo $team->name . ' - Elo: ' . $team->elo . ', Strength: ' . $team->strength . "\n";
}
