<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\FPLPointsService;
use App\Models\User;

$userId = 3;
$pointsService = app(FPLPointsService::class);
$gameweekId = $pointsService->getLatestFinishedGameweekId();

echo "Latest finished gameweek: " . $gameweekId . "\n";

$squadPoints = $pointsService->getSquadPointsForGameweek($userId, $gameweekId);
echo "Total points: " . ($squadPoints['total_points'] ?? 'null') . "\n";
echo "Player count: " . count($squadPoints['player_details'] ?? []) . "\n";

// Show individual player points
if (isset($squadPoints['player_details'])) {
    foreach ($squadPoints['player_details'] as $player) {
        echo $player['player']->web_name . ": " . $player['points'] . " pts (orig: " . $player['original_points'] . ", mult: " . $player['multiplier'] . ")\n";
    }
}

$user = User::find($userId);
echo "User starting XI: " . $user->starting_xi . "\n";
echo "Captain ID: " . $user->captain_id . "\n";
echo "Vice Captain ID: " . $user->vice_captain_id . "\n";
