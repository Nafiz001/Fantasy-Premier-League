<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

for($i = 1; $i <= 6; $i++) {
    DB::table('gameweeks')->updateOrInsert(
        ['gameweek_id' => $i],
        [
            'gameweek_id' => $i,
            'name' => 'Gameweek ' . $i,
            'deadline_time' => now(),
            'deadline_time_epoch' => time(),
            'deadline_time_game_offset' => 0,
            'finished' => false,
            'is_previous' => false,
            'is_current' => $i == 3,
            'is_next' => $i == 4,
            'created_at' => now(),
            'updated_at' => now()
        ]
    );
    echo "Added/updated gameweek {$i}\n";
}

echo "Done!\n";
