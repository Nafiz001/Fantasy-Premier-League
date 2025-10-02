<?php

require 'vendor/autoload.php';

// Setup Laravel application
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Investigating Fixture Data ===\n";

// Check fixture counts per gameweek
echo "Fixtures per gameweek:\n";
$fixturesByGW = DB::table('fixtures')
    ->select('gameweek', DB::raw('count(*) as fixture_count'))
    ->groupBy('gameweek')
    ->orderBy('gameweek')
    ->get();

foreach ($fixturesByGW as $gw) {
    echo "GW{$gw->gameweek}: {$gw->fixture_count} fixtures\n";
}

// Check total fixtures
$totalFixtures = DB::table('fixtures')->count();
echo "\nTotal fixtures in database: {$totalFixtures}\n";

// Check sample fixtures for GW6
echo "\n=== Sample GW6 Fixtures ===\n";
$gw6Fixtures = DB::table('fixtures')
    ->where('gameweek', 6)
    ->limit(5)
    ->get();

foreach ($gw6Fixtures as $fixture) {
    echo "Fixture {$fixture->fixture_id}: {$fixture->home_team} vs {$fixture->away_team}\n";
}

// Check if we have the FPL API fixture data
echo "\n=== Checking FPL API Fixture Data ===\n";
$fplFixtureCount = DB::table('fixtures')
    ->join('teams as home_team', 'fixtures.home_team', '=', 'home_team.fpl_id')
    ->join('teams as away_team', 'fixtures.away_team', '=', 'away_team.fpl_id')
    ->count();
echo "Fixtures with proper team joins: {$fplFixtureCount}\n";

echo "\nInvestigation completed!\n";
