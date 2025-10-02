<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\League;

// Get the first user
$user = User::first();

if (!$user) {
    echo "No users found in the database.\n";
    exit;
}

// Check if test league already exists
$existingLeague = League::where('league_code', 'TEST01')->first();
if ($existingLeague) {
    echo "Test league already exists: {$existingLeague->name} (Code: {$existingLeague->league_code})\n";
    exit;
}

// Create a test league
$league = League::create([
    'name' => 'Test League',
    'description' => 'A test league for verification',
    'admin_id' => $user->id,
    'league_code' => 'TEST01',
    'is_public' => true
]);

echo "League created successfully!\n";
echo "Name: {$league->name}\n";
echo "Code: {$league->league_code}\n";
echo "Admin: {$user->name}\n";
echo "You can now test the league functionality in the browser.\n";
