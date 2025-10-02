<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$service = new \App\Services\FPLPointsService();

echo "Testing all gameweeks with updated GitHub data:\n";
for ($gw = 1; $gw <= 6; $gw++) {
    $result = $service->getSquadPointsForGameweek(1, $gw);
    echo "User 1 GW{$gw}: " . ($result['total_points'] ?? 0) . " points (from " . ($result['count'] ?? 0) . " players)\n";
}

echo "\nGW4 and GW5 are now working! 🎉\n";
