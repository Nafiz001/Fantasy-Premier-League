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

echo "=== Updating Gameweek Status ===\n";

// First, reset all current flags
DB::table('gameweeks')->update(['is_current' => false]);

// Mark GW1-4 as finished
DB::table('gameweeks')
    ->whereIn('gameweek_id', [1, 2, 3, 4])
    ->update(['finished' => true]);

// Mark GW5 as current (not finished yet)
DB::table('gameweeks')
    ->where('gameweek_id', 5)
    ->update([
        'finished' => false,
        'is_current' => true
    ]);

echo "Updated gameweek statuses:\n";
echo "- GW1-4: Marked as finished\n";
echo "- GW5: Marked as current (not finished)\n";

echo "\n=== New Gameweek Status ===\n";

$gameweeks = DB::table('gameweeks')
    ->select('gameweek_id', 'name', 'finished', 'is_current')
    ->orderBy('gameweek_id')
    ->limit(10)
    ->get();

foreach ($gameweeks as $gw) {
    echo "GW{$gw->gameweek_id}: {$gw->name} | Finished: " . ($gw->finished ? 'Yes' : 'No') . " | Current: " . ($gw->is_current ? 'Yes' : 'No') . "\n";
}

echo "\n=== Navigation Test ===\n";

// Test navigation for different gameweeks
$testGameweeks = [3, 4, 5];

foreach ($testGameweeks as $gwId) {
    echo "\nFor GW{$gwId}:\n";

    $previous = DB::table('gameweeks')
        ->where('finished', true)
        ->where('gameweek_id', '<', $gwId)
        ->orderBy('gameweek_id', 'desc')
        ->first();

    $next = DB::table('gameweeks')
        ->where('finished', true)
        ->where('gameweek_id', '>', $gwId)
        ->orderBy('gameweek_id', 'asc')
        ->first();

    echo "  Previous: " . ($previous ? "GW{$previous->gameweek_id}" : 'None') . "\n";
    echo "  Next: " . ($next ? "GW{$next->gameweek_id}" : 'None') . "\n";
}

echo "\nGameweek status update completed!\n";
