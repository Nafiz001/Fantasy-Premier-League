<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== CHECKING PLAYER NAMES ===\n\n";

$playerIds = [670, 67, 226, 257, 476, 258, 477, 449, 579, 413, 382, 16, 596, 97, 681];

echo "Player IDs from Nafiz Ahmed's squad: " . implode(', ', $playerIds) . "\n\n";

$players = \DB::table('players')
    ->join('teams', 'players.team_code', '=', 'teams.fpl_code')
    ->select(
        'players.fpl_id',
        'players.web_name',
        'players.position',
        'teams.short_name as team_short'
    )
    ->whereIn('players.fpl_id', $playerIds)
    ->get();

echo "Players found in database:\n";
foreach ($players as $player) {
    echo "- {$player->web_name} ({$player->position}) - {$player->team_short} - ID: {$player->fpl_id}\n";
}

echo "\n=== COMPARING WITH SHOWN SQUAD ===\n";
echo "Shown players: Chalobah, Lacroix, Mitchell, Burn, Saka, Wirtz, Marmoush, B.Fernandes, Petrovic, Evanilson, Sesko\n";
echo "These do NOT match the auto-picked player IDs!\n";