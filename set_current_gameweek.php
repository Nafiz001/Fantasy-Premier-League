<?php

require 'vendor/autoload.php';

// Setup Laravel application
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Setting GW6 as Current ===\n";

// Reset all current flags
DB::table('gameweeks')->update(['is_current' => false]);

// Set GW6 as current
$updated = DB::table('gameweeks')
    ->where('gameweek_id', 6)
    ->update([
        'is_current' => true,
        'finished' => false
    ]);

echo "Rows updated: {$updated}\n";

// Verify
$current = DB::table('gameweeks')->where('is_current', true)->first();
echo "Current gameweek: " . ($current ? "GW{$current->gameweek_id}" : 'None') . "\n";

if ($current) {
    echo "Is finished: " . ($current->finished ? 'Yes' : 'No') . "\n";
}

echo "GW6 set as current gameweek!\n";
