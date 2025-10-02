<?php

// Create a test user with a sample squad for testing the points system

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Player;
use Illuminate\Support\Facades\DB;

// Create a test user if it doesn't exist
$testUser = User::where('email', 'test@example.com')->first();

if (!$testUser) {
    $testUser = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
        'team_name' => 'Test Team FC',
        'budget_remaining' => 0,
        'points' => 0,
        'free_transfers' => 1,
        'has_selected_squad' => true,
        'squad_completed' => true,
    ]);

    echo "Created test user: {$testUser->email}\n";
} else {
    echo "Test user already exists: {$testUser->email}\n";
}

// Create a sample squad with popular players
$sampleSquad = [
    // Get some goalkeepers
    ['position' => 'Goalkeeper', 'count' => 2],
    // Get some defenders
    ['position' => 'Defender', 'count' => 5],
    // Get some midfielders
    ['position' => 'Midfielder', 'count' => 5],
    // Get some forwards
    ['position' => 'Forward', 'count' => 3],
];

$squad = [];
$captainId = null;
$viceCaptainId = null;

foreach ($sampleSquad as $positionData) {
    $players = Player::where('position', $positionData['position'])
        ->limit($positionData['count'])
        ->get();

    foreach ($players as $player) {
        $squad[] = $player->fpl_id;

        // Set captain (first midfielder or forward)
        if (!$captainId && in_array($player->position, ['Midfielder', 'Forward'])) {
            $captainId = $player->fpl_id;
        }

        // Set vice captain (second midfielder or forward)
        if (!$viceCaptainId && $captainId && $captainId != $player->fpl_id && in_array($player->position, ['Midfielder', 'Forward'])) {
            $viceCaptainId = $player->fpl_id;
        }
    }
}

// Update the test user with the squad
$testUser->update([
    'starting_xi' => json_encode($squad),
    'captain_id' => $captainId,
    'vice_captain_id' => $viceCaptainId,
]);

echo "Updated test user squad:\n";
echo "- Squad size: " . count($squad) . " players\n";
echo "- Captain ID: {$captainId}\n";
echo "- Vice Captain ID: {$viceCaptainId}\n";

// Show some squad details
echo "\nSquad details:\n";
foreach ($sampleSquad as $positionData) {
    $players = Player::where('position', $positionData['position'])
        ->limit($positionData['count'])
        ->get();

    echo "{$positionData['position']}s ({$positionData['count']}):\n";
    foreach ($players as $player) {
        $isCaptain = $player->fpl_id == $captainId ? ' (C)' : '';
        $isViceCaptain = $player->fpl_id == $viceCaptainId ? ' (VC)' : '';
        echo "  - {$player->web_name}{$isCaptain}{$isViceCaptain}\n";
    }
}

echo "\nTest user setup complete! You can now test the points system.\n";
echo "Login with: test@example.com / password\n";
