<?php

require 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;

// Setup database connection
$capsule = new DB;
$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => 'database/database.sqlite',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "=== Fixing Gameweek Status Properly ===\n";

// Current state
echo "Before fix:\n";
$current = DB::table('gameweeks')->where('is_current', true)->first();
echo "Current: " . ($current ? "GW{$current->gameweek_id} (finished: " . ($current->finished ? 'yes' : 'no') . ")" : 'None') . "\n";

// Reset all current flags
DB::table('gameweeks')->update(['is_current' => false]);

// Mark GW1-5 as finished (we have data for these)
DB::table('gameweeks')
    ->whereIn('gameweek_id', [1, 2, 3, 4, 5])
    ->update(['finished' => true]);

// Mark GW6 as current and NOT finished (this is the upcoming gameweek)
DB::table('gameweeks')
    ->where('gameweek_id', 6)
    ->update([
        'finished' => false,
        'is_current' => true
    ]);

echo "\nAfter fix:\n";
echo "- GW1-5: Finished (have data)\n";
echo "- GW6: Current and not finished (upcoming)\n";

// Verify the fix
$finished = DB::table('gameweeks')->where('finished', true)->pluck('gameweek_id')->toArray();
$current = DB::table('gameweeks')->where('is_current', true)->first();

echo "\nVerification:\n";
echo "Finished gameweeks: " . implode(', ', array_map(fn($gw) => "GW{$gw}", $finished)) . "\n";
echo "Current gameweek: " . ($current ? "GW{$current->gameweek_id}" : 'None') . "\n";
echo "Current is finished: " . ($current && $current->finished ? 'Yes' : 'No') . "\n";

echo "\nGameweek status fixed!\n";
