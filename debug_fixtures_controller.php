<?php

require 'vendor/autoload.php';

// Setup Laravel application
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Debugging FixturesController Logic ===\n";

// Test the current logic
$currentGameweek = DB::table('gameweeks')
    ->where('is_current', true)
    ->orWhere('finished', false)
    ->orderBy('deadline_time', 'asc')
    ->value('gameweek_id');

echo "Current gameweek from flawed logic: " . ($currentGameweek ?? 'null') . "\n";

// Check what this query actually returns
$queryResults = DB::table('gameweeks')
    ->where('is_current', true)
    ->orWhere('finished', false)
    ->orderBy('deadline_time', 'asc')
    ->select('gameweek_id', 'name', 'finished', 'is_current')
    ->get();

echo "\nQuery results:\n";
foreach ($queryResults as $gw) {
    echo "GW{$gw->gameweek_id}: {$gw->name} | Finished: " . ($gw->finished ? 'Yes' : 'No') . " | Current: " . ($gw->is_current ? 'Yes' : 'No') . "\n";
}

// Correct logic - get current gameweek
$correctCurrentGW = DB::table('gameweeks')
    ->where('is_current', true)
    ->value('gameweek_id');

echo "\nCorrect current gameweek: " . ($correctCurrentGW ?? 'null') . "\n";

// If no current, get next unfinished
if (!$correctCurrentGW) {
    $nextUnfinished = DB::table('gameweeks')
        ->where('finished', false)
        ->orderBy('gameweek_id', 'asc')
        ->value('gameweek_id');
    echo "Next unfinished gameweek: " . ($nextUnfinished ?? 'null') . "\n";
}

echo "\nDebug completed!\n";
