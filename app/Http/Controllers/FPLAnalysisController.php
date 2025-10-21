<?php

namespace App\Http\Controllers;

use App\Services\FPLQueryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class FPLAnalysisController extends Controller
{
    protected FPLQueryService $queryService;

    public function __construct(FPLQueryService $queryService)
    {
        $this->queryService = $queryService;
    }

    /**
     * Dashboard showing all SQL operations
     */
    public function dashboard(Request $request)
    {
        try {
            // Get current gameweek for context
            $currentGameweek = \App\Models\Gameweek::where('is_current', true)->first()?->id ?? 1;

            $data = [
                'current_gameweek' => $currentGameweek,
                'top_performers' => $this->queryService->getTopPerformersWithTeamInfo($currentGameweek, 10),
                'form_table' => $this->queryService->getFormTable(5),
                'high_possession_teams' => $this->queryService->getHighPossessionTeams(50.0),
                'team_attacking_stats' => collect($this->queryService->getTeamAttackingStats())->take(10),
                'team_defensive_stats' => collect($this->queryService->getTeamDefensiveStats())->take(10),
            ];

            return view('fpl.dashboard', $data);
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            return view('fpl.dashboard', ['error' => 'Unable to load dashboard data']);
        }
    }

    /**
     * CRUD Operations Demo
     */
    public function crudDemo(): JsonResponse
    {
        try {
            $results = [];

            // CREATE - Add a transfer record
            $transferData = [
                'player_id' => 1,
                'from_team' => 1,
                'to_team' => 2,
                'transfer_date' => now(),
                'fee' => 50000000
            ];
            $results['create_transfer'] = $this->queryService->createPlayerTransfer($transferData);

            // UPDATE - Update team metrics
            $teamMetrics = [
                'form' => 4.2,
                'strength_overall_home' => 85,
                'pulse_id' => 12345
            ];
            $results['update_team'] = $this->queryService->updateTeamMetrics(1, $teamMetrics);

            // DELETE (Archive) - Archive old gameweek data
            $results['archive_gameweek'] = $this->queryService->archiveGameweekData(1);

            return response()->json([
                'success' => true,
                'message' => 'CRUD operations completed',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complex JOIN queries
     */
    public function joinQueries(Request $request): JsonResponse
    {
        try {
            $gameweek = $request->get('gameweek');
            $limit = $request->get('limit', 20);

            $data = [
                'top_performers' => $this->queryService->getTopPerformersWithTeamInfo($gameweek, $limit),
                'head_to_head' => $this->queryService->getHeadToHeadRecord(1, 2), // Example: Team 1 vs Team 2
                'player_vs_team' => $this->queryService->getPlayerVsTeamStats(100, 1) // Player 100 vs Team 1
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subqueries and Window Functions
     */
    public function subqueriesAndWindows(): JsonResponse
    {
        try {
            $data = [
                'positional_rankings' => $this->queryService->getPositionalRankingsWithTrends(),
                'form_table' => $this->queryService->getFormTable(5)
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GROUP BY and HAVING clauses
     */
    public function groupByHaving(Request $request): JsonResponse
    {
        try {
            $minPossession = $request->get('min_possession', 50.0);
            $minGames = $request->get('min_games', 3);

            $data = [
                'high_possession_teams' => $this->queryService->getHighPossessionTeams($minPossession),
                'consistent_scorers' => $this->queryService->getConsistentGoalScorers($minGames)
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aggregate Functions
     */
    public function aggregateFunctions(): JsonResponse
    {
        try {
            $data = [
                'team_attacking_stats' => $this->queryService->getTeamAttackingStats(),
                'team_defensive_stats' => $this->queryService->getTeamDefensiveStats()
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Views Management
     */
    public function manageViews(): JsonResponse
    {
        try {
            $results = $this->queryService->initializeViews();

            return response()->json([
                'success' => true,
                'message' => 'Database views initialized',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * SQL Query Explorer - Execute custom queries safely
     */
    public function queryExplorer(Request $request): JsonResponse
    {
        try {
            $queryType = $request->get('query_type');
            $parameters = $request->get('parameters', []);

            switch ($queryType) {
                case 'player_search':
                    $searchTerm = $parameters['search'] ?? '';
                    $data = \App\Models\Player::with('team')
                        ->where('web_name', 'LIKE', "%{$searchTerm}%")
                        ->limit(20)
                        ->get();
                    break;

                case 'team_form':
                    $teamId = $parameters['team_id'] ?? 1;
                    $gameweeks = $parameters['gameweeks'] ?? 5;
                    $data = $this->queryService->getFormTable($gameweeks);
                    break;

                case 'position_analysis':
                    $position = $parameters['position'] ?? 1;
                    $data = \App\Models\Player::with(['currentStats', 'team'])
                        ->where('element_type', $position)
                        ->whereHas('currentStats', function($q) {
                            $q->where('minutes', '>', 500);
                        })
                        ->get()
                        ->map(function($player) {
                            return [
                                'name' => $player->web_name,
                                'team' => $player->team->short_name ?? 'Unknown',
                                'total_points' => $player->currentStats->total_points ?? 0,
                                'cost' => ($player->currentStats->now_cost ?? 0) / 10,
                                'points_per_million' => $player->currentStats
                                    ? round($player->currentStats->total_points / (($player->currentStats->now_cost / 10) ?: 1), 2)
                                    : 0
                            ];
                        });
                    break;

                default:
                    throw new \InvalidArgumentException('Invalid query type');
            }

            return response()->json([
                'success' => true,
                'query_type' => $queryType,
                'data' => $data,
                'count' => is_countable($data) ? count($data) : 1
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Database Schema Information
     */
    public function schemaInfo(): JsonResponse
    {
        try {
            $tables = \Illuminate\Support\Facades\DB::select("
                SELECT table_name,
                       (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = t.table_name AND table_schema = DATABASE()) as column_count
                FROM information_schema.tables t
                WHERE table_schema = DATABASE()
                AND table_type = 'BASE TABLE'
                ORDER BY table_name
            ");

            $relationships = [
                'players' => ['team_code -> teams.fpl_code'],
                'player_stats' => ['player_id -> players.fpl_id'],
                'player_match_stats' => ['player_id -> players.fpl_id', 'match_id -> matches.match_id'],
                'player_gameweek_stats' => ['player_id -> players.fpl_id', 'gameweek -> gameweeks.id'],
                'matches' => ['home_team -> teams.fpl_id', 'away_team -> teams.fpl_id', 'gameweek -> gameweeks.id'],
                'fixtures' => ['home_team -> teams.fpl_id', 'away_team -> teams.fpl_id', 'gameweek -> gameweeks.id']
            ];

            return response()->json([
                'success' => true,
                'tables' => $tables,
                'relationships' => $relationships,
                'total_tables' => count($tables)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
