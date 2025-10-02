<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Fixtures Table Structure ===\n";

// Check if table exists
if (Schema::hasTable('fixtures')) {
    echo "Table 'fixtures' exists.\n\n";

    // Get table columns
    $columns = Schema::getColumnListing('fixtures');
    echo "Columns in fixtures table:\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }

    echo "\n=== Sample fixtures data ===\n";
    $fixtures = DB::table('fixtures')->limit(5)->get();
    foreach ($fixtures as $fixture) {
        echo "ID: {$fixture->id}, Home: {$fixture->home_team}, Away: {$fixture->away_team}, GW: {$fixture->gameweek}\n";
    }

    echo "\n=== Total fixtures count ===\n";
    $total = DB::table('fixtures')->count();
    echo "Total fixtures: $total\n";

} else {
    echo "Table 'fixtures' does not exist.\n";
}
