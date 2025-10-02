<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class);

use Illuminate\Support\Facades\DB;

echo "Testing auto-pick logic...\n";

// Test basic player counts
$positions = ['Goalkeeper', 'Defender', 'Midfielder', 'Forward'];
foreach ($positions as $pos) {
    $count = DB::table('players')
        ->join('player_stats', 'players.fpl_id', '=', 'player_stats.player_id')
        ->where('player_stats.gameweek', 3)
        ->where('players.position', $pos)
        ->count();
    echo "$pos: $count players\n";
}

// Test simple selection
echo "\nTesting simple selection...\n";
try {
    $gks = DB::table('players')
        ->join('teams', 'players.team_code', '=', 'teams.fpl_code')
        ->leftJoin('player_stats', function($join) {
            $join->on('players.fpl_id', '=', 'player_stats.player_id')
                 ->where('player_stats.gameweek', '=', 3);
        })
        ->select('players.*', 'teams.name as team_name', 'teams.short_name as team_short', 'player_stats.now_cost')
        ->where('players.position', 'Goalkeeper')
        ->whereNotNull('player_stats.now_cost')
        ->orderBy('player_stats.now_cost')
        ->take(5)
        ->get();
    
    echo "Sample goalkeepers:\n";
    foreach ($gks as $gk) {
        echo "- {$gk->web_name}: £" . ($gk->now_cost/10) . "m\n";
    }
    
    $totalCost = $gks->take(2)->sum('now_cost');
    echo "Cost of 2 cheapest GKs: £" . ($totalCost/10) . "m\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}