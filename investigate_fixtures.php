<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 Investigating current fixtures data...\n\n";

// Check current fixtures
echo "Current fixtures in database:\n";
$fixtures = DB::table('fixtures')
    ->orderBy('gameweek')
    ->orderBy('kickoff_time')
    ->get();

echo "Total fixtures: " . $fixtures->count() . "\n\n";

// Group by gameweek
$fixturesByGW = $fixtures->groupBy('gameweek');

foreach ($fixturesByGW as $gw => $gwFixtures) {
    echo "GW{$gw}: " . $gwFixtures->count() . " fixtures\n";

    if ($gw >= 5) { // Show details for GW5+
        foreach ($gwFixtures->take(3) as $fixture) {
            echo "  - {$fixture->home_team_name} vs {$fixture->away_team_name} (ID: {$fixture->id})\n";
        }
        if ($gwFixtures->count() > 3) {
            echo "  - ... and " . ($gwFixtures->count() - 3) . " more\n";
        }
    }
}

echo "\n";

// Check what the current gameweek should be
echo "🕐 Checking current gameweek status...\n";
$currentDate = now();
echo "Current date: " . $currentDate->format('Y-m-d H:i:s') . "\n";

// Find the current gameweek based on fixture dates
$currentGW = DB::table('fixtures')
    ->where('kickoff_time', '>=', $currentDate)
    ->orderBy('kickoff_time')
    ->first();

if ($currentGW) {
    echo "Next fixture is in GW{$currentGW->gameweek}: {$currentGW->home_team_name} vs {$currentGW->away_team_name}\n";
    echo "Kickoff: {$currentGW->kickoff_time}\n";
} else {
    echo "No upcoming fixtures found\n";
}

// Check if GW5 is completed
$gw5Fixtures = DB::table('fixtures')->where('gameweek', 5)->get();
$gw5Finished = $gw5Fixtures->where('finished', true)->count();
echo "\nGW5 status: {$gw5Finished}/{$gw5Fixtures->count()} matches finished\n";

// Check if GW6 fixtures exist
$gw6Count = DB::table('fixtures')->where('gameweek', 6)->count();
echo "GW6 fixtures in database: {$gw6Count}\n";
