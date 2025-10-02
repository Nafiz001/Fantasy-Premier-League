<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Gameweek;
use App\Models\PlayerStat;
use App\Models\User;
use App\Services\FPLPointsService;

echo "=== FPL Data Check ===\n";

// Check data counts
echo "Teams: " . DB::table('teams')->count() . "\n";
echo "Players: " . DB::table('players')->count() . "\n";
echo "Gameweeks: " . DB::table('gameweeks')->count() . "\n";
echo "Player Stats: " . DB::table('player_stats')->count() . "\n";
echo "Fixtures: " . DB::table('fixtures')->count() . "\n";
echo "Matches: " . DB::table('matches')->count() . "\n";

// Check gameweeks
$gameweeks = DB::table('gameweeks')->select('gameweek_id', 'name', 'finished')->orderBy('gameweek_id')->get();
echo "\n=== Gameweeks Status ===\n";
foreach ($gameweeks->take(10) as $gw) {
    echo "GW{$gw->gameweek_id}: {$gw->name} - " . ($gw->finished ? 'Finished' : 'Not Finished') . "\n";
}

// Get first few player stats to see data structure
echo "\n=== Sample Player Stats ===\n";
$sampleStats = DB::table('player_stats')
    ->join('players', 'player_stats.player_id', '=', 'players.fpl_id')
    ->select('players.web_name', 'players.position', 'player_stats.gameweek', 'player_stats.total_points', 'player_stats.minutes', 'player_stats.goals_scored', 'player_stats.assists')
    ->where('player_stats.gameweek', 1)
    ->orderBy('player_stats.total_points', 'desc')
    ->limit(5)
    ->get();

foreach ($sampleStats as $stat) {
    echo "{$stat->web_name} ({$stat->position}) - GW{$stat->gameweek}: {$stat->total_points} pts, {$stat->minutes} mins, {$stat->goals_scored} goals, {$stat->assists} assists\n";
}

// Test Points Service
echo "\n=== Testing Points Service ===\n";
$pointsService = new FPLPointsService();

$latestGameweek = $pointsService->getLatestFinishedGameweekId();
echo "Latest finished gameweek: " . ($latestGameweek ?? 'None') . "\n";

$currentGameweek = $pointsService->getCurrentGameweek();
if ($currentGameweek) {
    echo "Current gameweek: {$currentGameweek->name} (ID: {$currentGameweek->gameweek_id})\n";
}

// Let's mark some gameweeks as finished for testing
echo "\n=== Marking first 5 gameweeks as finished for testing ===\n";
DB::table('gameweeks')->whereIn('gameweek_id', [1, 2, 3, 4, 5])->update(['finished' => true]);

$latestGameweek = $pointsService->getLatestFinishedGameweekId();
echo "Latest finished gameweek after update: " . ($latestGameweek ?? 'None') . "\n";

// Test calculating points for a player
if ($latestGameweek) {
    echo "\n=== Testing Points Calculation ===\n";
    $topPlayer = DB::table('player_stats')
        ->where('gameweek', $latestGameweek)
        ->orderBy('total_points', 'desc')
        ->first();

    if ($topPlayer) {
        $calculatedPoints = $pointsService->calculatePlayerPoints($topPlayer->player_id, $latestGameweek);
        echo "Player ID {$topPlayer->player_id} in GW{$latestGameweek}:\n";
        echo "  - Original total_points: {$topPlayer->total_points}\n";
        echo "  - Calculated points: {$calculatedPoints}\n";
        echo "  - Minutes: {$topPlayer->minutes}\n";
        echo "  - Goals: {$topPlayer->goals_scored}\n";
        echo "  - Assists: {$topPlayer->assists}\n";
    }
}

// Create a test user if one doesn't exist
echo "\n=== Creating Test User ===\n";
$testUser = User::where('email', 'test@example.com')->first();
if (!$testUser) {
    $testUser = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'team_name' => 'Test Team',
        'budget_remaining' => 0,
        'points' => 0,
        'free_transfers' => 1,
        'has_selected_squad' => true,
        'squad_completed' => true,
    ]);
    echo "Created test user with ID: {$testUser->id}\n";
} else {
    echo "Test user already exists with ID: {$testUser->id}\n";
}

// Create a sample squad for the test user
if (!$testUser->starting_xi) {
    echo "\n=== Creating Sample Squad ===\n";

    // Get some players for each position
    $goalkeeper = DB::table('players')->where('position', 'Goalkeeper')->first();
    $defenders = DB::table('players')->where('position', 'Defender')->limit(4)->get();
    $midfielders = DB::table('players')->where('position', 'Midfielder')->limit(4)->get();
    $forwards = DB::table('players')->where('position', 'Forward')->limit(2)->get();

    $squad = [];
    if ($goalkeeper) $squad[] = $goalkeeper->fpl_id;
    foreach ($defenders as $player) $squad[] = $player->fpl_id;
    foreach ($midfielders as $player) $squad[] = $player->fpl_id;
    foreach ($forwards as $player) $squad[] = $player->fpl_id;

    $testUser->update([
        'starting_xi' => json_encode($squad),
        'captain_id' => $squad[5] ?? null, // First midfielder as captain
        'vice_captain_id' => $squad[1] ?? null, // First defender as vice captain
    ]);

    echo "Created squad with " . count($squad) . " players\n";
    echo "Captain ID: " . ($testUser->captain_id ?? 'None') . "\n";
    echo "Vice Captain ID: " . ($testUser->vice_captain_id ?? 'None') . "\n";
}

// Test squad points calculation
if ($latestGameweek && $testUser->starting_xi) {
    echo "\n=== Testing Squad Points Calculation ===\n";
    $squadPoints = $pointsService->getSquadPoints($testUser->id);

    if ($squadPoints) {
        echo "Squad points for {$squadPoints['gameweek_name']}: {$squadPoints['total_points']}\n";
        echo "Position totals:\n";
        foreach ($squadPoints['position_totals'] as $position => $points) {
            echo "  - {$position}: {$points}\n";
        }

        echo "\nTop 5 players:\n";
        $topPlayers = array_slice($squadPoints['player_breakdown'], 0, 5);
        foreach ($topPlayers as $player) {
            $captainStr = $player['is_captain'] ? ' (C)' : ($player['is_vice_captain'] ? ' (V)' : '');
            echo "  - {$player['name']} ({$player['position']}): {$player['points']} points{$captainStr}\n";
        }
    } else {
        echo "No squad points data available\n";
    }
}

echo "\n=== Test Complete ===\n";
