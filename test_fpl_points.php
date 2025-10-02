<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\DB;
use App\Services\FPLPointsService;

// Set up database connection
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'sqlite',
    'database'  => __DIR__ . '/database/database.sqlite',
    'prefix'    => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Initialize the Points Service
$pointsService = new FPLPointsService();

echo "=== Testing FPL 2025/26 Points Calculation System ===\n\n";

// Get the latest gameweek
$latestGameweek = DB::table('gameweeks')
    ->where('finished', true)
    ->orderBy('deadline_time', 'desc')
    ->first();

if (!$latestGameweek) {
    echo "No finished gameweeks found. Using gameweek 5 as default.\n";
    $gameweekId = 5;
} else {
    $gameweekId = $latestGameweek->gameweek_id;
    echo "Latest finished gameweek: {$gameweekId}\n";
}

// Test with user ID 1
$userId = 1;
echo "Testing with User ID: {$userId}\n";

// Get squad points for the gameweek
$result = $pointsService->getSquadPointsForGameweek($userId, $gameweekId);

if ($result) {
    echo "Total Points: {$result['total_points']}\n";
    echo "Player Count: {$result['count']}\n";
    echo "Gameweek ID: {$result['gameweek_id']}\n\n";

    echo "=== Player Breakdown ===\n";
    foreach ($result['player_details'] as $playerData) {
        $player = $playerData['player'];
        $points = $playerData['points'];
        $captain = $playerData['is_captain'] ? ' (C)' : '';
        $viceCaptain = $playerData['is_vice_captain'] ? ' (VC)' : '';

        echo sprintf("%-20s %-5s %3d pts%s%s\n",
            $player->name,
            $player->position,
            $points,
            $captain,
            $viceCaptain
        );
    }

    echo "\n=== Verification ===\n";

    // Check if we have realistic points for a single gameweek
    $avgPointsPerPlayer = $result['total_points'] / $result['count'];
    echo "Average points per player: " . round($avgPointsPerPlayer, 1) . "\n";

    if ($result['total_points'] > 100) {
        echo "⚠️  WARNING: Total points seem too high for a single gameweek.\n";
        echo "   Expected range: 20-80 points for most gameweeks.\n";
    } elseif ($result['total_points'] < 10) {
        echo "⚠️  WARNING: Total points seem too low.\n";
        echo "   This might indicate missing player data.\n";
    } else {
        echo "✅ Points total looks realistic for a single gameweek.\n";
    }

} else {
    echo "❌ Failed to get squad points. Check user data and gameweek.\n";
}

// Test match stats availability
echo "\n=== Match Stats Check ===\n";
$statsCount = DB::table('player_match_stats')
    ->where('gameweek', $gameweekId)
    ->count();

echo "Player match stats available for GW{$gameweekId}: {$statsCount}\n";

if ($statsCount == 0) {
    echo "⚠️  No match stats found. Points calculation will return 0 for all players.\n";
    echo "   Make sure to populate player_match_stats table with real data.\n";
}

echo "\n=== Sample Match Stat Structure ===\n";
$sampleStat = DB::table('player_match_stats')
    ->where('gameweek', $gameweekId)
    ->first();

if ($sampleStat) {
    echo "Available columns in player_match_stats:\n";
    foreach ((array)$sampleStat as $column => $value) {
        echo "  - {$column}: {$value}\n";
    }
} else {
    echo "No sample stats available to show structure.\n";
}

echo "\nDone!\n";
