<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\FPLWebScrapingService;
use App\Services\FPLPointsService;

echo "🚀 Starting FPL GW1 Data Update from Official API...\n\n";

$webScrapingService = new FPLWebScrapingService();
$result = $webScrapingService->updateGW1DataFromAPI();

if ($result['success']) {
    echo "✅ {$result['message']}\n";
    echo "📊 Players updated: {$result['players_updated']}\n\n";

    // Test the updated data
    echo "🧪 Testing updated GW1 points...\n";
    $pointsService = new FPLPointsService();

    for ($gw = 1; $gw <= 4; $gw++) {
        $gwResult = $pointsService->getSquadPointsForGameweek(1, $gw);
        $points = $gwResult['total_points'] ?? 0;
        $count = $gwResult['count'] ?? 0;
        echo "User 1 GW{$gw}: {$points} points (from {$count} players)\n";
    }

} else {
    echo "❌ {$result['message']}\n";
    if (!empty($result['errors'])) {
        echo "Errors:\n";
        foreach ($result['errors'] as $error) {
            echo "  - $error\n";
        }
    }
}
