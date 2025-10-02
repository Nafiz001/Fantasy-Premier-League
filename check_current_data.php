<?php

require 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;

// Setup database connection
$capsule = new DB;
$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => 'database/database.sqlite',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "=== Checking Points Data ===\n";

// Check player_gameweek_stats
$stats = DB::table('player_gameweek_stats')
    ->select('gameweek')
    ->distinct()
    ->orderBy('gameweek')
    ->pluck('gameweek');

echo "Gameweeks with data in player_gameweek_stats: " . implode(', ', $stats->toArray()) . "\n";
echo "Total records in player_gameweek_stats: " . DB::table('player_gameweek_stats')->count() . "\n";

// Check a few sample records
echo "\nSample records:\n";
$samples = DB::table('player_gameweek_stats')
    ->join('players', 'player_gameweek_stats.player_id', '=', 'players.id')
    ->select(
        'player_gameweek_stats.gameweek',
        'players.web_name',
        'player_gameweek_stats.total_points',
        'player_gameweek_stats.minutes'
    )
    ->orderBy('player_gameweek_stats.gameweek')
    ->orderBy('player_gameweek_stats.total_points', 'desc')
    ->limit(10)
    ->get();

foreach ($samples as $sample) {
    echo "GW{$sample->gameweek}: {$sample->web_name} - {$sample->total_points} pts, {$sample->minutes} mins\n";
}

echo "\n=== Checking User Squad ===\n";

// Check if user has a squad
$userSquad = DB::table('user_squads')
    ->join('users', 'user_squads.user_id', '=', 'users.id')
    ->select('users.name', 'user_squads.*')
    ->first();

if ($userSquad) {
    echo "User: {$userSquad->name}\n";
    echo "Squad ID: {$userSquad->id}\n";

    // Check squad players
    $squadPlayers = DB::table('user_squad_players')
        ->join('players', 'user_squad_players.player_id', '=', 'players.id')
        ->where('user_squad_players.user_squad_id', $userSquad->id)
        ->select('players.web_name', 'user_squad_players.is_captain', 'user_squad_players.is_vice_captain')
        ->get();

    echo "Squad players count: " . $squadPlayers->count() . "\n";
} else {
    echo "No user squad found!\n";
}
