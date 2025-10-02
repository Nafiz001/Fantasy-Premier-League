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

echo "=== Gameweek Status ===\n";

$gameweeks = DB::table('gameweeks')
    ->select('gameweek_id', 'name', 'finished', 'is_current')
    ->orderBy('gameweek_id')
    ->get();

foreach ($gameweeks as $gw) {
    echo "GW{$gw->gameweek_id}: {$gw->name} | Finished: " . ($gw->finished ? 'Yes' : 'No') . " | Current: " . ($gw->is_current ? 'Yes' : 'No') . "\n";
}

echo "\n=== Points Navigation Issue Analysis ===\n";

// Check which gameweeks have points data
$pointsGameweeks = DB::table('player_gameweek_stats')
    ->select('gameweek')
    ->distinct()
    ->orderBy('gameweek')
    ->pluck('gameweek');

echo "Gameweeks with points data: " . implode(', ', $pointsGameweeks->toArray()) . "\n";

// Check latest finished gameweek
$latestFinished = DB::table('gameweeks')
    ->where('finished', true)
    ->orderBy('gameweek_id', 'desc')
    ->first();

echo "Latest finished gameweek: " . ($latestFinished ? "GW{$latestFinished->gameweek_id}" : 'None') . "\n";

// Check current gameweek
$current = DB::table('gameweeks')
    ->where('is_current', true)
    ->first();

echo "Current gameweek: " . ($current ? "GW{$current->gameweek_id}" : 'None') . "\n";
