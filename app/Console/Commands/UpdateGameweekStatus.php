<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateGameweekStatus extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'gameweek:update-status {--finish-gw= : Mark specific gameweek as finished} {--set-next-gw= : Set specific gameweek as next}';

    /**
     * The console command description.
     */
    protected $description = 'Manually update gameweek statuses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $finishGw = $this->option('finish-gw');
        $setNextGw = $this->option('set-next-gw');

        if ($finishGw) {
            // Mark specified gameweek as finished
            DB::table('gameweeks')
                ->where('gameweek_id', $finishGw)
                ->update([
                    'finished' => 1,
                    'is_current' => 0,
                    'is_next' => 0
                ]);

            $this->info("✅ Marked GW{$finishGw} as finished");
        }

        if ($setNextGw) {
            // Clear all next flags first
            DB::table('gameweeks')->update(['is_next' => 0, 'is_current' => 0]);

            // Set specified gameweek as next
            DB::table('gameweeks')
                ->where('gameweek_id', $setNextGw)
                ->update(['is_next' => 1]);

            $this->info("⏭️ Set GW{$setNextGw} as next");
        }

        if (!$finishGw && !$setNextGw) {
            $this->info('Usage examples:');
            $this->info('php artisan gameweek:update-status --finish-gw=7 --set-next-gw=8');
            $this->info('php artisan gameweek:update-status --finish-gw=8');
        }

        // Show current status
        $latestFinished = DB::table('gameweeks')->where('finished', 1)->max('gameweek_id');
        $nextGw = DB::table('gameweeks')->where('is_next', 1)->value('gameweek_id');

        $this->info("📊 Current Status:");
        $this->info("   Latest Finished: GW{$latestFinished}");
        $this->info("   Next Gameweek: GW{$nextGw}");

        return 0;
    }
}
