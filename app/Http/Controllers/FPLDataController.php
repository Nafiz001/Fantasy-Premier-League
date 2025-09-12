<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FPLEloInsightsService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Exception;

class FPLDataController extends Controller
{
    private $fplService;

    public function __construct(FPLEloInsightsService $fplService)
    {
        $this->fplService = $fplService;
    }

    /**
     * Show the data management dashboard
     */
    public function index()
    {
        try {
            $currentGameweek = $this->fplService->getCurrentGameweek();
            $nextGameweek = $this->fplService->getNextGameweek();
            $isDataUpToDate = $this->fplService->isDataUpToDate();
            $availableFiles = $this->fplService->getAvailableFiles();

            // Get data summary
            $dataSummary = [
                'teams' => \DB::table('teams')->count(),
                'players' => \DB::table('players')->count(),
                'gameweeks' => \DB::table('gameweeks')->count(),
                'fixtures' => \DB::table('fixtures')->count(),
                'player_stats' => \DB::table('player_stats')->count(),
                'matches' => \DB::table('matches')->count(),
            ];

            $lastUpdate = \DB::table('player_stats')->max('updated_at');

            return view('fpl.data-dashboard', compact(
                'currentGameweek',
                'nextGameweek', 
                'isDataUpToDate',
                'availableFiles',
                'dataSummary',
                'lastUpdate'
            ));

        } catch (Exception $e) {
            return back()->with('error', 'Failed to load dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Import all FPL data
     */
    public function importAll(Request $request)
    {
        try {
            set_time_limit(300); // 5 minutes timeout
            
            $force = $request->boolean('force', false);
            
            if (!$force && $this->fplService->isDataUpToDate()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data is already up to date',
                    'data' => []
                ]);
            }

            $results = $this->fplService->importAllData();

            return response()->json([
                'success' => true,
                'message' => 'FPL data imported successfully',
                'data' => $results
            ]);

        } catch (Exception $e) {
            Log::error('FPL data import failed via web: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import specific data type
     */
    public function importSpecific(Request $request)
    {
        $request->validate([
            'type' => 'required|in:teams,gameweeks,players,fixtures,player_stats,match_stats,player_match_stats'
        ]);

        try {
            set_time_limit(120); // 2 minutes timeout
            
            $type = $request->input('type');
            $result = null;

            switch ($type) {
                case 'teams':
                    $result = $this->fplService->importTeams();
                    break;
                case 'gameweeks':
                    $result = $this->fplService->importGameweeks();
                    break;
                case 'players':
                    $result = $this->fplService->importPlayers();
                    break;
                case 'fixtures':
                    $result = $this->fplService->importFixtures();
                    break;
                case 'player_stats':
                    $result = $this->fplService->importPlayerStats();
                    break;
                case 'match_stats':
                    $result = $this->fplService->importMatchStats();
                    break;
                case 'player_match_stats':
                    $result = $this->fplService->importPlayerMatchStats();
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $type)) . ' imported successfully',
                'data' => $result
            ]);

        } catch (Exception $e) {
            Log::error("FPL {$type} import failed via web: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update specific gameweek data
     */
    public function updateGameweek(Request $request)
    {
        $request->validate([
            'gameweek' => 'required|integer|min:1|max:38'
        ]);

        try {
            $gameweek = $request->input('gameweek');
            $result = $this->fplService->updateGameweekData($gameweek);

            return response()->json([
                'success' => true,
                'message' => "Gameweek {$gameweek} data updated successfully",
                'data' => $result
            ]);

        } catch (Exception $e) {
            Log::error("Gameweek update failed via web: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check data status
     */
    public function checkStatus()
    {
        try {
            $isUpToDate = $this->fplService->isDataUpToDate();
            $currentGameweek = $this->fplService->getCurrentGameweek();
            $nextGameweek = $this->fplService->getNextGameweek();
            $lastUpdate = \DB::table('player_stats')->max('updated_at');

            return response()->json([
                'success' => true,
                'data' => [
                    'is_up_to_date' => $isUpToDate,
                    'current_gameweek' => $currentGameweek,
                    'next_gameweek' => $nextGameweek,
                    'last_update' => $lastUpdate,
                    'data_age_hours' => $lastUpdate ? round((time() - strtotime($lastUpdate)) / 3600, 1) : null
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Status check failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run artisan command via web
     */
    public function runCommand(Request $request)
    {
        $request->validate([
            'command' => 'required|in:fpl:import,fpl:update'
        ]);

        try {
            $command = $request->input('command');
            $options = $request->input('options', []);
            
            $exitCode = Artisan::call($command, $options);
            $output = Artisan::output();

            return response()->json([
                'success' => $exitCode === 0,
                'message' => $exitCode === 0 ? 'Command executed successfully' : 'Command failed',
                'output' => $output,
                'exit_code' => $exitCode
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Command execution failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get database statistics
     */
    public function getStats()
    {
        try {
            $stats = [
                'teams' => \DB::table('teams')->count(),
                'players' => \DB::table('players')->count(),
                'gameweeks' => \DB::table('gameweeks')->count(),
                'fixtures' => \DB::table('fixtures')->count(),
                'player_stats' => \DB::table('player_stats')->count(),
                'matches' => \DB::table('matches')->count(),
                'player_match_stats' => \DB::table('player_match_stats')->count(),
            ];

            // Additional statistics
            $additionalStats = [
                'total_points_all_players' => \DB::table('player_stats')->sum('total_points'),
                'average_player_cost' => \DB::table('player_stats')->avg('now_cost') / 10,
                'most_expensive_player' => \DB::table('player_stats')
                    ->join('players', 'player_stats.player_id', '=', 'players.fpl_id')
                    ->orderBy('now_cost', 'desc')
                    ->first(['players.web_name', 'player_stats.now_cost']),
                'highest_scoring_player' => \DB::table('player_stats')
                    ->join('players', 'player_stats.player_id', '=', 'players.fpl_id')
                    ->orderBy('total_points', 'desc')
                    ->first(['players.web_name', 'player_stats.total_points']),
                'finished_gameweeks' => \DB::table('gameweeks')->where('finished', true)->count(),
                'upcoming_fixtures' => \DB::table('fixtures')->where('finished', false)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'basic_counts' => $stats,
                    'additional_stats' => $additionalStats
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}
