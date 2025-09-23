<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ResetSquads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'squads:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all user squads for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Resetting all user squads...');
        
        User::query()->update([
            'has_selected_squad' => false,
            'squad_completed' => false,
            'team_name' => null,
            'budget_remaining' => 100.0,
            'starting_xi' => null,
            'captain_id' => null,
            'vice_captain_id' => null,
            'formation' => '4-4-2'
        ]);
        
        $this->info('All user squads have been reset.');
        
        // Show current users
        $users = User::all(['id', 'name', 'email', 'team_name']);
        $this->table(['ID', 'Name', 'Email', 'Team Name'], $users->map(function($user) {
            return [$user->id, $user->name, $user->email, $user->team_name ?: 'No team'];
        })->toArray());
    }
}
