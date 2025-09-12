<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FPLEloInsightsService;
use Exception;

class ImportFPLData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fpl:import 
                           {--type=all : Type of data to import (all, teams, players, gameweeks, fixtures, stats)}
                           {--gameweek= : Specific gameweek to update}
                           {--force : Force import even if data seems up to date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import FPL data from Elo Insights repository';

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
        $this->info('🚀 Starting FPL Data Import from Elo Insights Repository');
        $this->newLine();

        $type = $this->option('type');
        $gameweek = $this->option('gameweek');
        $force = $this->option('force');

        try {
            // Check if data is up to date (unless forced)
            if (!$force && $this->fplService->isDataUpToDate()) {
                $this->info('✅ Data is already up to date (updated within last 12 hours)');
                $this->line('Use --force flag to import anyway');
                return 0;
            }

            $this->info('📊 Data Import Configuration:');
            $this->line("Type: {$type}");
            if ($gameweek) {
                $this->line("Gameweek: {$gameweek}");
            }
            $this->line("Force: " . ($force ? 'Yes' : 'No'));
            $this->newLine();

            if (!$this->confirm('Do you want to proceed with the import?', true)) {
                $this->info('Import cancelled by user');
                return 0;
            }

            $startTime = microtime(true);

            // Import based on type
            switch ($type) {
                case 'all':
                    $results = $this->importAllData();
                    break;
                case 'teams':
                    $results = ['teams' => $this->fplService->importTeams()];
                    break;
                case 'players':
                    $results = ['players' => $this->fplService->importPlayers()];
                    break;
                case 'gameweeks':
                    $results = ['gameweeks' => $this->fplService->importGameweeks()];
                    break;
                case 'fixtures':
                    $results = ['fixtures' => $this->fplService->importFixtures()];
                    break;
                case 'stats':
                    $results = ['player_stats' => $this->fplService->importPlayerStats()];
                    break;
                default:
                    $this->error("Unknown import type: {$type}");
                    $this->line('Available types: all, teams, players, gameweeks, fixtures, stats');
                    return 1;
            }

            // Update specific gameweek if requested
            if ($gameweek) {
                $this->info("🔄 Updating data for gameweek {$gameweek}");
                $gwResult = $this->fplService->updateGameweekData($gameweek);
                $results['gameweek_update'] = $gwResult;
            }

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            $this->displayResults($results, $duration);

            $this->newLine();
            $this->info('✅ FPL Data Import Completed Successfully!');
            $this->line("Total execution time: {$duration} seconds");

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Import failed: ' . $e->getMessage());
            $this->line('Please check the logs for more details');
            return 1;
        }
    }

    /**
     * Import all data with progress indication
     */
    private function importAllData()
    {
        $steps = [
            'teams' => 'Importing Teams',
            'gameweeks' => 'Importing Gameweeks', 
            'players' => 'Importing Players',
            'fixtures' => 'Importing Fixtures',
            'player_stats' => 'Importing Player Stats',
            'match_stats' => 'Importing Match Stats',
            'player_match_stats' => 'Importing Player Match Stats'
        ];

        $results = [];
        $progressBar = $this->output->createProgressBar(count($steps));
        $progressBar->start();

        foreach ($steps as $key => $description) {
            $this->line("🔄 {$description}...");
            
            try {
                switch ($key) {
                    case 'teams':
                        $results[$key] = $this->fplService->importTeams();
                        break;
                    case 'gameweeks':
                        $results[$key] = $this->fplService->importGameweeks();
                        break;
                    case 'players':
                        $results[$key] = $this->fplService->importPlayers();
                        break;
                    case 'fixtures':
                        $results[$key] = $this->fplService->importFixtures();
                        break;
                    case 'player_stats':
                        $results[$key] = $this->fplService->importPlayerStats();
                        break;
                    case 'match_stats':
                        $results[$key] = $this->fplService->importMatchStats();
                        break;
                    case 'player_match_stats':
                        $results[$key] = $this->fplService->importPlayerMatchStats();
                        break;
                }
                
                $this->info("✅ {$description} completed");
                
            } catch (Exception $e) {
                $this->warn("⚠️  {$description} failed: " . $e->getMessage());
                $results[$key] = ['error' => $e->getMessage()];
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        return $results;
    }

    /**
     * Display import results
     */
    private function displayResults($results, $duration)
    {
        $this->newLine();
        $this->info('📈 Import Results Summary:');
        $this->newLine();

        $headers = ['Data Type', 'Status', 'Records', 'Notes'];
        $rows = [];

        foreach ($results as $type => $result) {
            if (isset($result['error'])) {
                $rows[] = [
                    ucfirst(str_replace('_', ' ', $type)),
                    '❌ Failed',
                    '-',
                    $result['error']
                ];
            } else {
                $imported = $result['imported'] ?? 'N/A';
                $rows[] = [
                    ucfirst(str_replace('_', ' ', $type)),
                    '✅ Success',
                    $imported,
                    $imported > 0 ? 'Data imported/updated' : 'No new data'
                ];
            }
        }

        $this->table($headers, $rows);

        // Show current gameweek info
        $currentGW = $this->fplService->getCurrentGameweek();
        $nextGW = $this->fplService->getNextGameweek();

        $this->newLine();
        $this->info('🎮 Current FPL Status:');
        if ($currentGW) {
            $this->line("Current Gameweek: {$currentGW->name}");
        }
        if ($nextGW) {
            $this->line("Next Gameweek: {$nextGW->name}");
        }
    }
}
