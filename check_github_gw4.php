<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

echo "Fetching latest GW4 data from GitHub repository...\n";

$url = 'https://raw.githubusercontent.com/olbauday/FPL-Elo-Insights/main/data/2025-2026/By%20Gameweek/GW4/player_gameweek_stats.csv';

try {
    $response = Http::get($url);

    if ($response->successful()) {
        $csvContent = $response->body();
        $lines = explode("\n", $csvContent);

        echo "Successfully fetched " . count($lines) . " lines from GitHub\n";
        echo "Header: " . $lines[0] . "\n\n";

        echo "First 10 data rows:\n";
        for ($i = 1; $i <= min(10, count($lines) - 1); $i++) {
            if (!empty(trim($lines[$i]))) {
                $data = str_getcsv($lines[$i]);
                // total_points is column 17 (index 17)
                echo "Player {$data[0]}: {$data[17]} total_points, {$data[18]} minutes\n";
            }
        }

        // Check if there are any non-zero points
        $nonZeroCount = 0;
        for ($i = 1; $i < count($lines); $i++) {
            if (!empty(trim($lines[$i]))) {
                $data = str_getcsv($lines[$i]);
                if (isset($data[17]) && floatval($data[17]) > 0) {
                    $nonZeroCount++;
                }
            }
        }

        echo "\nPlayers with non-zero points in GW4: $nonZeroCount\n";

    } else {
        echo "Failed to fetch data. Status: " . $response->status() . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
