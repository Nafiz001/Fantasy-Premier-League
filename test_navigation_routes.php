<?php

require 'vendor/autoload.php';

// Setup Laravel application
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Route;

echo "=== Testing Navigation Routes ===\n";

// Test route generation
try {
    $route1 = route('points');
    echo "Base points route: {$route1}\n";

    $route2 = route('points', ['gameweek' => 4]);
    echo "GW4 points route: {$route2}\n";

    $route3 = route('points', ['gameweek' => 5]);
    echo "GW5 points route: {$route3}\n";

    echo "\nRoutes generated successfully!\n";
} catch (Exception $e) {
    echo "Error generating routes: " . $e->getMessage() . "\n";
}

echo "\n=== Testing Navigation Logic ===\n";

use App\Models\Gameweek;

// Test the navigation logic for GW5
$gameweekId = 5;

$previousGameweek = Gameweek::where(function($query) {
        $query->where('finished', true)->orWhere('is_current', true);
    })
    ->where('gameweek_id', '<', $gameweekId)
    ->orderBy('gameweek_id', 'desc')
    ->first();

$nextGameweek = Gameweek::where(function($query) {
        $query->where('finished', true)->orWhere('is_current', true);
    })
    ->where('gameweek_id', '>', $gameweekId)
    ->orderBy('gameweek_id', 'asc')
    ->first();

echo "For GW{$gameweekId}:\n";
echo "  Previous: " . ($previousGameweek ? "GW{$previousGameweek->gameweek_id}" : 'None') . "\n";
echo "  Next: " . ($nextGameweek ? "GW{$nextGameweek->gameweek_id}" : 'None') . "\n";

if ($previousGameweek) {
    $prevRoute = route('points', ['gameweek' => $previousGameweek->gameweek_id]);
    echo "  Previous URL: {$prevRoute}\n";
}

if ($nextGameweek) {
    $nextRoute = route('points', ['gameweek' => $nextGameweek->gameweek_id]);
    echo "  Next URL: {$nextRoute}\n";
}

echo "\nNavigation test completed!\n";
