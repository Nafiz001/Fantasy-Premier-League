<?php

require 'vendor/autoload.php';

// Setup Laravel application
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\FPLWebScrapingService;

echo "=== Updating GW1 Data from FPL API ===\n";

$service = new FPLWebScrapingService();
$result = $service->updateGW1DataFromAPI();

echo "GW1 data updated: " . $result['updated_count'] . " players updated\n";
echo "Players with 0 points: " . $result['zero_points_count'] . "\n";
echo "Total points distributed: " . $result['total_points_distributed'] . "\n";

echo "\nGW1 data update completed!\n";
