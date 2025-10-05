<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Gameweek;
use App\Services\FPLPointsService;

class UpdateAllUserPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fpl:update-user-points {--gameweek= : Specific gameweek ID to calculate} {--all : Update all gameweeks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all users gameweek points and total points in the database';

    protected $pointsService;

    public function __construct(FPLPointsService $pointsService)
    {
        parent::__construct();
        $this->pointsService = $pointsService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting user points update...');

        // Get gameweek to process
        $gameweekId = $this->option('gameweek');
        $updateAll = $this->option('all');

        if (!$gameweekId && !$updateAll) {
            // Get latest finished gameweek
            $gameweekId = $this->pointsService->getLatestFinishedGameweekId();
            if (!$gameweekId) {
                $this->error('No finished gameweek found!');
                return 1;
            }
            $this->info("Using latest finished gameweek: {$gameweekId}");
        }

        // Get all users with selected squads
        $users = User::where('squad_completed', true)
            ->whereNotNull('starting_xi')
            ->get();

        if ($users->isEmpty()) {
            $this->warn('No users with completed squads found.');
            return 0;
        }

        $this->info("Found {$users->count()} users with completed squads.");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $updated = 0;
        $errors = 0;

        foreach ($users as $user) {
            try {
                if ($updateAll) {
                    // Update all finished gameweeks
                    $gameweeks = Gameweek::where('finished', true)
                        ->orderBy('gameweek_id')
                        ->pluck('gameweek_id');

                    $totalPoints = 0;
                    foreach ($gameweeks as $gwId) {
                        $result = $this->pointsService->getSquadPointsForGameweek($user->id, $gwId);
                        if ($result && isset($result['total_points'])) {
                            $totalPoints += $result['total_points'];
                        }
                    }

                    // Update user with final totals
                    $user->points = $totalPoints;
                    $user->current_gameweek = $gameweeks->last();
                    $user->save();
                } else {
                    // Update specific gameweek
                    $result = $this->pointsService->getSquadPointsForGameweek($user->id, $gameweekId);
                    // Points are auto-updated by the service now
                }

                $updated++;
            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("Error updating user {$user->id}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✓ Successfully updated {$updated} users");
        if ($errors > 0) {
            $this->warn("✗ {$errors} errors encountered");
        }

        return 0;
    }
}

