<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\League;
use App\Models\LeagueMember;
use App\Models\User;

class LeagueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user or create one
        $user = User::first();
        if (!$user) {
            $user = User::factory()->create();
        }

        // Create a public league
        $league = League::create([
            'name' => 'Test Public League',
            'league_code' => 'TEST01',
            'description' => 'A test league for joining',
            'type' => 'classic',
            'privacy' => 'public',
            'admin_id' => $user->id,
            'max_entries' => 10,
            'current_entries' => 1,
            'is_active' => true
        ]);

        // Add the admin as a member
        LeagueMember::create([
            'league_id' => $league->id,
            'user_id' => $user->id,
            'joined_at' => now(),
            'is_admin' => true
        ]);

        // Create another public league
        $league2 = League::create([
            'name' => 'Another Test League',
            'league_code' => 'TEST02',
            'description' => 'Another test league',
            'type' => 'classic',
            'privacy' => 'public',
            'admin_id' => $user->id,
            'max_entries' => 5,
            'current_entries' => 1,
            'is_active' => true
        ]);

        LeagueMember::create([
            'league_id' => $league2->id,
            'user_id' => $user->id,
            'joined_at' => now(),
            'is_admin' => true
        ]);
    }
}
