<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== TESTING USER UPDATE ===\n\n";

$user = User::find(3); // Nafiz Ahmed

if ($user) {
    echo "Found user: {$user->name} ({$user->email})\n";
    echo "Current team_name: " . ($user->team_name ?? 'None') . "\n";
    echo "Current starting_xi: " . ($user->starting_xi ?? 'None') . "\n\n";
    
    echo "Testing update...\n";
    
    try {
        $testPlayerIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];
        
        $user->update([
            'has_selected_squad' => true,
            'squad_completed' => true,
            'team_name' => 'Test Auto Squad',
            'budget_remaining' => 5.5,
            'starting_xi' => json_encode($testPlayerIds),
            'formation' => '4-4-2'
        ]);
        
        echo "Update successful!\n";
        
        // Reload from database
        $user->refresh();
        
        echo "After update:\n";
        echo "Team Name: " . $user->team_name . "\n";
        echo "Has Selected Squad: " . ($user->has_selected_squad ? 'Yes' : 'No') . "\n";
        echo "Squad Completed: " . ($user->squad_completed ? 'Yes' : 'No') . "\n";
        echo "Budget Remaining: £" . $user->budget_remaining . "m\n";
        echo "Starting XI: " . $user->starting_xi . "\n";
        echo "Formation: " . $user->formation . "\n";
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo "Trace: " . $e->getTraceAsString() . "\n";
    }
    
} else {
    echo "User not found!\n";
}