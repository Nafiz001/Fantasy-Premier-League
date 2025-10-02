<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "GW4 player stats for User 1 squad:\n";
$squad = DB::table('users')->where('id', 1)->first();
if ($squad && $squad->current_team) {
    $team = json_decode($squad->current_team, true);
    if (isset($team['picks'])) {
        foreach ($team['picks'] as $pick) {
            $stats = DB::table('player_gameweek_stats')
                ->where('player_id', $pick['element'])
                ->where('gameweek', 4)
                ->first();
            if ($stats) {
                echo "Player {$pick['element']}: {$stats->total_points} pts, {$stats->minutes} min\n";
            } else {
                echo "Player {$pick['element']}: NO DATA\n";
            }
        }
    }
}

echo "\nChecking all GW4 data in database:\n";
$gw4Stats = DB::table('player_gameweek_stats')
    ->where('gameweek', 4)
    ->where('total_points', '!=', 0)
    ->limit(10)
    ->get();

echo "Found " . $gw4Stats->count() . " players with non-zero points in GW4:\n";
foreach ($gw4Stats as $stat) {
    echo "Player {$stat->player_id}: {$stat->total_points} pts\n";
}
