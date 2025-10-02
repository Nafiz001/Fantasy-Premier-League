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

echo "=== Testing Updated Navigation Logic ===\n";

// Test navigation for different gameweeks with the new logic
$testGameweeks = [1, 2, 3, 4, 5];

foreach ($testGameweeks as $gameweekId) {
    echo "\nFor GW{$gameweekId}:\n";

    // Previous gameweek (finished OR current, and less than current)
    $previousGameweek = DB::table('gameweeks')
        ->where(function($query) {
            $query->where('finished', true)->orWhere('is_current', true);
        })
        ->where('gameweek_id', '<', $gameweekId)
        ->orderBy('gameweek_id', 'desc')
        ->first();

    // Next gameweek (finished OR current, and greater than current)
    $nextGameweek = DB::table('gameweeks')
        ->where(function($query) {
            $query->where('finished', true)->orWhere('is_current', true);
        })
        ->where('gameweek_id', '>', $gameweekId)
        ->orderBy('gameweek_id', 'asc')
        ->first();

    echo "  Previous: " . ($previousGameweek ? "GW{$previousGameweek->gameweek_id}" : 'None') . "\n";
    echo "  Next: " . ($nextGameweek ? "GW{$nextGameweek->gameweek_id}" : 'None') . "\n";

    // Check if this gameweek has points data
    $hasPoints = DB::table('player_gameweek_stats')
        ->where('gameweek', $gameweekId)
        ->exists();

    echo "  Has Points Data: " . ($hasPoints ? 'Yes' : 'No') . "\n";
}

echo "\nNavigation test completed!\n";
