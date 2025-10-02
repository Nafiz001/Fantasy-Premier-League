<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\FPLFixtureService;

echo "🚀 Starting FPL Fixtures Update from Official API...\n\n";

$fixtureService = new FPLFixtureService();

// First, update table structure if needed
$fixtureService->updateFixtureTableStructure();

echo "\n";

// Then fetch and update fixtures
$result = $fixtureService->updateFixturesFromAPI();

if ($result['success']) {
    echo "\n✅ {$result['message']}\n";
    echo "📊 Fixtures updated: {$result['fixtures_updated']}\n";

} else {
    echo "\n❌ {$result['message']}\n";
    if (!empty($result['errors'])) {
        echo "Errors:\n";
        foreach ($result['errors'] as $error) {
            echo "  - $error\n";
        }
    }
}
