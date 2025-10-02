<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== RESETTING USER SQUAD ===\n\n";

$user = User::find(3); // Nafiz Ahmed

if ($user) {
    echo "Resetting squad for user: {$user->name}\n";
    
    $user->update([
        'has_selected_squad' => false,
        'squad_completed' => false,
        'team_name' => null,
        'budget_remaining' => 100.0,
        'starting_xi' => null,
        'formation' => '4-4-2'
    ]);
    
    echo "User squad reset successfully!\n";
    
} else {
    echo "User not found!\n";
}