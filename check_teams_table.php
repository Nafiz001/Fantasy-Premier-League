<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Teams Table Structure ===\n";

// Check if table exists
if (Schema::hasTable('teams')) {
    echo "Table 'teams' exists.\n\n";

    // Get table columns
    $columns = Schema::getColumnListing('teams');
    echo "Columns in teams table:\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }

    echo "\n=== Sample teams data ===\n";
    $teams = DB::table('teams')->limit(5)->get();
    foreach ($teams as $team) {
        echo "ID: {$team->id}, FPL_ID: {$team->fpl_id}, Name: {$team->name}\n";
    }

} else {
    echo "Table 'teams' does not exist.\n";
}
