<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== USERS AND THEIR SQUADS ===\n\n";

$users = User::all();

foreach ($users as $user) {
    echo "User ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Team Name: " . ($user->team_name ?? 'None') . "\n";
    echo "Has Selected Squad: " . ($user->has_selected_squad ? 'Yes' : 'No') . "\n";
    echo "Squad Completed: " . ($user->squad_completed ? 'Yes' : 'No') . "\n";
    echo "Budget Remaining: £" . ($user->budget_remaining ?? 'N/A') . "m\n";
    echo "Starting XI: " . (empty($user->starting_xi) ? 'None' : count(json_decode($user->starting_xi, true)) . ' players') . "\n";
    if (!empty($user->starting_xi)) {
        $playerIds = json_decode($user->starting_xi, true);
        echo "Player IDs: " . implode(', ', $playerIds) . "\n";
    }
    echo "Formation: " . ($user->formation ?? 'N/A') . "\n";
    echo "Created: {$user->created_at}\n";
    echo "Updated: {$user->updated_at}\n";
    echo "------------------------\n\n";
}