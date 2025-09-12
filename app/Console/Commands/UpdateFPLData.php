<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FPLEloInsightsService;
use Illuminate\Support\Facades\Log;

class UpdateFPLData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fpl:update 
                           {--check-only : Only check if update is needed}
                           {--silent : Run silently without output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update FPL data automatically (runs twice daily)';

    private $fplService;

    public function __construct(FPLEloInsightsService $fplService)
    {
        parent::__construct();
        $this->fplService = $fplService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $checkOnly = $this->option('check-only');
        $silent = $this->option('silent');

        if (!$silent) {
            $this->info('🔍 Checking FPL data status...');
        }

        try {
            $isUpToDate = $this->fplService->isDataUpToDate();
            
            if ($checkOnly) {
                if (!$silent) {
                    $status = $isUpToDate ? 'Up to date' : 'Needs update';
                    $this->info("Data status: {$status}");
                }
                return $isUpToDate ? 0 : 1;
            }

            if ($isUpToDate) {
                if (!$silent) {
                    $this->info('✅ Data is already up to date');
                }
                Log::info('FPL data check: Data is up to date');
                return 0;
            }

            if (!$silent) {
                $this->info('🔄 Data needs updating, starting automatic update...');
            }
            
            Log::info('FPL data update: Starting automatic update');
            
            $result = $this->fplService->scheduleUpdate();
            
            if (!$silent) {
                $this->info('✅ FPL data updated successfully');
            }
            
            Log::info('FPL data update: Completed successfully', $result);
            
            return 0;
            
        } catch (\Exception $e) {
            $message = 'FPL data update failed: ' . $e->getMessage();
            
            if (!$silent) {
                $this->error('❌ ' . $message);
            }
            
            Log::error($message);
            return 1;
        }
    }
}
