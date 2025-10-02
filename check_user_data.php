<?php

require 'vendor/autoload.php';

// Setup Laravel application
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking User Data ===\n";

$userCount = DB::table('users')->count();
echo "Users count: {$userCount}\n";

$user = DB::table('users')->first();
if ($user) {
    echo "User found: {$user->name} (ID: {$user->id})\n";
    echo "Email: {$user->email}\n";
    echo "Team name: {$user->team_name}\n";
} else {
    echo "No users found\n";

    // Create a test user
    echo "Creating test user...\n";
    DB::table('users')->insert([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'email_verified_at' => now(),
        'password' => bcrypt('password'),
        'team_name' => 'Test Team FC',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Test user created!\n";
}

echo "\nUser check completed!\n";
