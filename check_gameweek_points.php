<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Get user's gameweek points
$userId = 3;

echo "User's Gameweek Points:\n";
echo "======================\n\n";

// Check if there's a user_gameweek_points or similar table
$tables = DB::select("SHOW TABLES LIKE '%gameweek%'");
echo "Tables with 'gameweek' in name:\n";
foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    echo "- {$tableName}\n";
}

echo "\n";

// Check player_gameweek_stats for the user's squad
$user = DB::table('users')->where('id', $userId)->first();
echo "User: {$user->name}\n";
echo "Team: {$user->team_name}\n";
echo "Points in DB: {$user->points}\n";
echo "Starting XI: {$user->starting_xi}\n";

echo "\n";

// Get gameweeks
$gameweeks = DB::table('gameweeks')->orderBy('id')->get();
echo "Gameweeks in database:\n";
foreach ($gameweeks as $gw) {
    echo "- GW{$gw->id}: {$gw->name} (Finished: " . ($gw->finished ? 'Yes' : 'No') . ")\n";
}

echo "\n";

// Check if there's points calculation data
$pointsData = DB::table('player_gameweek_stats')
    ->where('gameweek_id', '<=', 5)
    ->selectRaw('gameweek_id, COUNT(*) as player_count, SUM(total_points) as total_points')
    ->groupBy('gameweek_id')
    ->orderBy('gameweek_id')
    ->get();

echo "Player gameweek stats summary:\n";
foreach ($pointsData as $data) {
    echo "- GW{$data->gameweek_id}: {$data->player_count} players, {$data->total_points} total points\n";
}
