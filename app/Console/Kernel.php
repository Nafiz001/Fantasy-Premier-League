<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ImportFPLData::class,
        Commands\UpdateFPLData::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Update FPL data twice daily at 5:00 AM and 5:00 PM UTC
        // This matches the FPL Elo Insights repository update schedule
        $schedule->command('fpl:update --silent')
                 ->twiceDaily(5, 17)
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/fpl-updates.log'));

        // Additional backup check every 6 hours in case of failures
        $schedule->command('fpl:update --check-only --silent')
                 ->everySixHours()
                 ->appendOutputTo(storage_path('logs/fpl-checks.log'));

        // Weekly full data refresh (Sundays at 2 AM)
        $schedule->command('fpl:import --type=all --force --silent')
                 ->weeklyOn(0, '02:00')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/fpl-weekly-refresh.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
