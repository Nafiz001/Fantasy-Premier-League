<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "User table structure:\n";
$user = DB::table('users')->first();
var_dump($user);

echo "\nChecking GW4 data availability:\n";
$gw4Count = DB::table('player_gameweek_stats')->where('gameweek', 4)->count();
echo "Total GW4 records: $gw4Count\n";

$gw4NonZero = DB::table('player_gameweek_stats')
    ->where('gameweek', 4)
    ->where('total_points', '!=', 0)
    ->count();
echo "GW4 records with non-zero points: $gw4NonZero\n";

// Show some sample GW4 data
$gw4Sample = DB::table('player_gameweek_stats')
    ->where('gameweek', 4)
    ->limit(5)
    ->get();

echo "\nSample GW4 data:\n";
foreach ($gw4Sample as $stat) {
    echo "Player {$stat->player_id}: {$stat->total_points} pts, {$stat->minutes} min\n";
}
