<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

// Get the user
$user = User::find(3);

if (!$user) {
    echo "User not found.\n";
    exit;
}

echo "User Information:\n";
echo "================\n";
echo "ID: {$user->id}\n";
echo "Name: {$user->name}\n";
echo "Team Name: {$user->team_name}\n";
echo "Points: {$user->points}\n";
echo "Gameweek: {$user->gameweek}\n";
echo "Budget Remaining: {$user->budget_remaining}\n";
echo "\n";

// Check if user has any player gameweek stats
$stats = DB::table('player_gameweek_stats')
    ->join('users', function($join) use ($user) {
        $join->whereRaw("JSON_CONTAINS(users.starting_xi, JSON_QUOTE(CAST(player_gameweek_stats.player_id AS CHAR)))");
    })
    ->where('users.id', $user->id)
    ->count();

echo "Player gameweek stats matching user's squad: {$stats}\n";
