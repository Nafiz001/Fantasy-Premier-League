<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 Checking fixtures table structure...\n\n";

// Check table structure
$fixture = DB::table('fixtures')->first();
if ($fixture) {
    echo "Fixture table columns:\n";
    foreach (get_object_vars($fixture) as $column => $value) {
        echo "  - $column: $value\n";
    }
} else {
    echo "No fixtures found in database\n";
}

echo "\n";

// Count fixtures by gameweek
echo "Fixtures count by gameweek:\n";
$fixturesByGW = DB::table('fixtures')
    ->selectRaw('gameweek, COUNT(*) as count')
    ->groupBy('gameweek')
    ->orderBy('gameweek')
    ->get();

foreach ($fixturesByGW as $gw) {
    echo "GW{$gw->gameweek}: {$gw->count} fixtures\n";
}

echo "\nTotal fixtures: " . DB::table('fixtures')->count() . "\n";
echo "Expected: 380 fixtures (38 gameweeks × 10 matches per gameweek)\n";
