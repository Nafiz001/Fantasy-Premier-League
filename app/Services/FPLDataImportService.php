<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Team;
use App\Models\Player;
use App\Models\Gameweek;
use App\Models\Fixture;
use App\Models\FPLMatch;
use App\Models\PlayerStat;
use App\Models\PlayerMatchStat;
use App\Models\PlayerGameweekStat;
use Carbon\Carbon;

class FPLDataImportService
{
    private const BASE_URL = 'https://raw.githubusercontent.com/olbauday/FPL-Elo-Insights/main/data';
    private const CURRENT_SEASON = '2025-2026';
    
    private string $baseUrl;
    
    public function __construct()
    {
        $this->baseUrl = self::BASE_URL . '/' . self::CURRENT_SEASON;
    }

    /**
     * Import all data from FPL Elo Insights repository
     */
    public function importAllData(): array
    {
        $results = [];
        
        try {
            DB::beginTransaction();
            
            // Import in correct order due to foreign key constraints
            $results['teams'] = $this->importTeams();
            $results['gameweeks'] = $this->importGameweeks();
            $results['players'] = $this->importPlayers();
            $results['fixtures'] = $this->importFixtures();
            $results['matches'] = $this->importMatches();
            $results['player_stats'] = $this->importPlayerStats();
            $results['player_match_stats'] = $this->importPlayerMatchStats();
            $results['player_gameweek_stats'] = $this->importPlayerGameweekStats();
            
            DB::commit();
            
            Log::info('FPL data import completed successfully', $results);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('FPL data import failed: ' . $e->getMessage());
            throw $e;
        }
        
        return $results;
    }

    /**
     * Import teams data
     */
    public function importTeams(): array
    {
        $url = $this->baseUrl . '/teams.csv';
        $csvData = $this->fetchCsvData($url);
        
        $imported = 0;
        $updated = 0;
        
        foreach ($csvData as $row) {
            $team = Team::updateOrCreate(
                ['fpl_id' => (int) $row['id']],
                [
                    'fpl_code' => (int) $row['code'],
                    'name' => $row['name'],
                    'short_name' => $row['short_name'],
                    'strength' => (int) $row['strength'],
                    'strength_overall_home' => (int) $row['strength_overall_home'],
                    'strength_overall_away' => (int) $row['strength_overall_away'],
                    'strength_attack_home' => (int) $row['strength_attack_home'],
                    'strength_attack_away' => (int) $row['strength_attack_away'],
                    'strength_defence_home' => (int) $row['strength_defence_home'],
                    'strength_defence_away' => (int) $row['strength_defence_away'],
                    'pulse_id' => isset($row['pulse_id']) ? (int) $row['pulse_id'] : null,
                    'elo' => isset($row['elo']) ? (float) $row['elo'] : null,
                ]
            );
            
            $team->wasRecentlyCreated ? $imported++ : $updated++;
        }
        
        return [
            'imported' => $imported,
            'updated' => $updated,
            'total' => count($csvData)
        ];
    }

    /**
     * Import players data
     */
    public function importPlayers(): array
    {
        $url = $this->baseUrl . '/players.csv';
        $csvData = $this->fetchCsvData($url);
        
        $imported = 0;
        $updated = 0;
        
        foreach ($csvData as $row) {
            $player = Player::updateOrCreate(
                ['fpl_id' => (int) $row['player_id']],
                [
                    'fpl_code' => (int) $row['player_code'],
                    'first_name' => $row['first_name'],
                    'second_name' => $row['second_name'],
                    'web_name' => $row['web_name'],
                    'team_code' => (int) $row['team_code'],
                    'position' => $row['position'],
                    'element_type' => $this->mapPositionToElementType($row['position']),
                ]
            );
            
            $player->wasRecentlyCreated ? $imported++ : $updated++;
        }
        
        return [
            'imported' => $imported,
            'updated' => $updated,
            'total' => count($csvData)
        ];
    }

    /**
     * Import gameweeks data
     */
    public function importGameweeks(): array
    {
        $url = $this->baseUrl . '/gameweeks.csv';
        
        try {
            $csvData = $this->fetchCsvData($url);
        } catch (\Exception $e) {
            // Gameweeks might not exist until GW2, create basic structure
            return $this->createBasicGameweeks();
        }
        
        $imported = 0;
        $updated = 0;
        
        foreach ($csvData as $row) {
            $gameweek = Gameweek::updateOrCreate(
                ['gameweek_id' => (int) $row['id']],
                [
                    'name' => $row['name'],
                    'deadline_time' => Carbon::parse($row['deadline_time']),
                    'deadline_time_epoch' => (int) $row['deadline_time_epoch'],
                    'deadline_time_game_offset' => (int) $row['deadline_time_game_offset'],
                    'average_entry_score' => isset($row['average_entry_score']) ? (float) $row['average_entry_score'] : null,
                    'highest_score' => isset($row['highest_score']) ? (int) $row['highest_score'] : null,
                    'finished' => $this->parseBoolean($row['finished'] ?? false),
                    'is_previous' => $this->parseBoolean($row['is_previous'] ?? false),
                    'is_current' => $this->parseBoolean($row['is_current'] ?? false),
                    'is_next' => $this->parseBoolean($row['is_next'] ?? false),
                    'chip_plays' => isset($row['chip_plays']) ? json_decode($row['chip_plays'], true) : null,
                    'most_selected' => isset($row['most_selected']) ? (int) $row['most_selected'] : null,
                    'most_transferred_in' => isset($row['most_transferred_in']) ? (int) $row['most_transferred_in'] : null,
                    'most_captained' => isset($row['most_captained']) ? (int) $row['most_captained'] : null,
                    'most_vice_captained' => isset($row['most_vice_captained']) ? (int) $row['most_vice_captained'] : null,
                    'top_element' => isset($row['top_element']) ? (int) $row['top_element'] : null,
                    'top_element_info' => isset($row['top_element_info']) ? json_decode($row['top_element_info'], true) : null,
                    'transfers_made' => isset($row['transfers_made']) ? (int) $row['transfers_made'] : null,
                ]
            );
            
            $gameweek->wasRecentlyCreated ? $imported++ : $updated++;
        }
        
        return [
            'imported' => $imported,
            'updated' => $updated,
            'total' => count($csvData)
        ];
    }

    /**
     * Import fixtures data (upcoming matches)
     */
    public function importFixtures(): array
    {
        $url = $this->baseUrl . '/fixtures.csv';
        $csvData = $this->fetchCsvData($url);
        
        $imported = 0;
        $updated = 0;
        
        foreach ($csvData as $row) {
            $fixture = Fixture::updateOrCreate(
                ['fixture_id' => (int) $row['id']],
                [
                    'gameweek' => (int) $row['event'],
                    'kickoff_time' => Carbon::parse($row['kickoff_time']),
                    'home_team' => (int) $row['team_h'],
                    'away_team' => (int) $row['team_a'],
                    'home_team_elo' => isset($row['team_h_elo']) ? (float) $row['team_h_elo'] : null,
                    'away_team_elo' => isset($row['team_a_elo']) ? (float) $row['team_a_elo'] : null,
                    'home_score' => isset($row['team_h_score']) ? (int) $row['team_h_score'] : null,
                    'away_score' => isset($row['team_a_score']) ? (int) $row['team_a_score'] : null,
                    'finished' => $this->parseBoolean($row['finished'] ?? false),
                    'tournament' => $row['tournament'] ?? 'Premier League',
                ]
            );
            
            $fixture->wasRecentlyCreated ? $imported++ : $updated++;
        }
        
        return [
            'imported' => $imported,
            'updated' => $updated,
            'total' => count($csvData)
        ];
    }

    /**
     * Import matches data (completed matches)
     */
    public function importMatches(): array
    {
        $url = $this->baseUrl . '/matches.csv';
        $csvData = $this->fetchCsvData($url);
        
        $imported = 0;
        $updated = 0;
        
        foreach ($csvData as $row) {
            $match = FPLMatch::updateOrCreate(
                ['match_id' => (int) $row['match_id']],
                [
                    'gameweek' => (int) $row['gameweek'],
                    'kickoff_time' => Carbon::parse($row['kickoff_time']),
                    'home_team' => (int) $row['home_team'],
                    'away_team' => (int) $row['away_team'],
                    'home_team_elo' => (float) $row['home_team_elo'],
                    'away_team_elo' => (float) $row['away_team_elo'],
                    'home_score' => (int) $row['home_score'],
                    'away_score' => (int) $row['away_score'],
                    'finished' => $this->parseBoolean($row['finished'] ?? true),
                    'tournament' => $row['tournament'] ?? 'Premier League',
                    
                    // Match statistics
                    'home_possession' => isset($row['home_possession']) ? (float) $row['home_possession'] : null,
                    'away_possession' => isset($row['away_possession']) ? (float) $row['away_possession'] : null,
                    'home_expected_goals_xg' => isset($row['home_expected_goals_xg']) ? (float) $row['home_expected_goals_xg'] : null,
                    'away_expected_goals_xg' => isset($row['away_expected_goals_xg']) ? (float) $row['away_expected_goals_xg'] : null,
                    'home_total_shots' => isset($row['home_total_shots']) ? (int) $row['home_total_shots'] : null,
                    'away_total_shots' => isset($row['away_total_shots']) ? (int) $row['away_total_shots'] : null,
                    'home_shots_on_target' => isset($row['home_shots_on_target']) ? (int) $row['home_shots_on_target'] : null,
                    'away_shots_on_target' => isset($row['away_shots_on_target']) ? (int) $row['away_shots_on_target'] : null,
                    'home_big_chances' => isset($row['home_big_chances']) ? (int) $row['home_big_chances'] : null,
                    'away_big_chances' => isset($row['away_big_chances']) ? (int) $row['away_big_chances'] : null,
                    'home_accurate_passes' => isset($row['home_accurate_passes']) ? (int) $row['home_accurate_passes'] : null,
                    'away_accurate_passes' => isset($row['away_accurate_passes']) ? (int) $row['away_accurate_passes'] : null,
                    'home_fouls_committed' => isset($row['home_fouls_committed']) ? (int) $row['home_fouls_committed'] : null,
                    'away_fouls_committed' => isset($row['away_fouls_committed']) ? (int) $row['away_fouls_committed'] : null,
                    'home_corners' => isset($row['home_corners']) ? (int) $row['home_corners'] : null,
                    'away_corners' => isset($row['away_corners']) ? (int) $row['away_corners'] : null,
                    'home_yellow_cards' => isset($row['home_yellow_cards']) ? (int) $row['home_yellow_cards'] : null,
                    'away_yellow_cards' => isset($row['away_yellow_cards']) ? (int) $row['away_yellow_cards'] : null,
                    'home_red_cards' => isset($row['home_red_cards']) ? (int) $row['home_red_cards'] : null,
                    'away_red_cards' => isset($row['away_red_cards']) ? (int) $row['away_red_cards'] : null,
                    'home_tackles_won' => isset($row['home_tackles_won']) ? (int) $row['home_tackles_won'] : null,
                    'away_tackles_won' => isset($row['away_tackles_won']) ? (int) $row['away_tackles_won'] : null,
                    'home_interceptions' => isset($row['home_interceptions']) ? (int) $row['home_interceptions'] : null,
                    'away_interceptions' => isset($row['away_interceptions']) ? (int) $row['away_interceptions'] : null,
                    'home_blocks' => isset($row['home_blocks']) ? (int) $row['home_blocks'] : null,
                    'away_blocks' => isset($row['away_blocks']) ? (int) $row['away_blocks'] : null,
                    'home_clearances' => isset($row['home_clearances']) ? (int) $row['home_clearances'] : null,
                    'away_clearances' => isset($row['away_clearances']) ? (int) $row['away_clearances'] : null,
                    'stats_processed' => $this->parseBoolean($row['stats_processed'] ?? false),
                    'player_stats_processed' => $this->parseBoolean($row['player_stats_processed'] ?? false),
                ]
            );
            
            $match->wasRecentlyCreated ? $imported++ : $updated++;
        }
        
        return [
            'imported' => $imported,
            'updated' => $updated,
            'total' => count($csvData)
        ];
    }

    /**
     * Import player statistics data
     */
    public function importPlayerStats(): array
    {
        // Try to get current gameweek data first
        $currentGameweek = $this->getCurrentGameweek();
        
        $url = $this->baseUrl . '/playerstats.csv';
        $csvData = $this->fetchCsvData($url);
        
        $imported = 0;
        $updated = 0;
        
        foreach ($csvData as $row) {
            $playerStat = PlayerStat::updateOrCreate(
                [
                    'player_id' => (int) $row['id'],
                    'gameweek' => $currentGameweek
                ],
                [
                    'first_name' => $row['first_name'],
                    'second_name' => $row['second_name'],
                    'web_name' => $row['web_name'],
                    'status' => $row['status'] ?? 'a',
                    'news' => $row['news'] ?? null,
                    'news_added' => isset($row['news_added']) ? Carbon::parse($row['news_added']) : null,
                    'chance_of_playing_next_round' => isset($row['chance_of_playing_next_round']) ? (int) $row['chance_of_playing_next_round'] : null,
                    'chance_of_playing_this_round' => isset($row['chance_of_playing_this_round']) ? (int) $row['chance_of_playing_this_round'] : null,
                    
                    // Pricing and selection
                    'now_cost' => (int) $row['now_cost'],
                    'selected_by_percent' => (float) $row['selected_by_percent'],
                    'total_points' => (int) $row['total_points'],
                    'event_points' => (int) ($row['event_points'] ?? 0),
                    'points_per_game' => (float) ($row['points_per_game'] ?? 0),
                    
                    // Performance statistics
                    'minutes' => (int) ($row['minutes'] ?? 0),
                    'goals_scored' => (int) ($row['goals_scored'] ?? 0),
                    'assists' => (int) ($row['assists'] ?? 0),
                    'clean_sheets' => (int) ($row['clean_sheets'] ?? 0),
                    'goals_conceded' => (int) ($row['goals_conceded'] ?? 0),
                    'own_goals' => (int) ($row['own_goals'] ?? 0),
                    'penalties_saved' => (int) ($row['penalties_saved'] ?? 0),
                    'penalties_missed' => (int) ($row['penalties_missed'] ?? 0),
                    'yellow_cards' => (int) ($row['yellow_cards'] ?? 0),
                    'red_cards' => (int) ($row['red_cards'] ?? 0),
                    'saves' => (int) ($row['saves'] ?? 0),
                    'starts' => (int) ($row['starts'] ?? 0),
                    'bonus' => (int) ($row['bonus'] ?? 0),
                    'bps' => (int) ($row['bps'] ?? 0),
                    
                    // Form and value
                    'form' => (float) ($row['form'] ?? 0),
                    'value_season' => (float) ($row['value_season'] ?? 0),
                    'transfers_in' => (int) ($row['transfers_in'] ?? 0),
                    'transfers_out' => (int) ($row['transfers_out'] ?? 0),
                    
                    // Expected stats
                    'expected_goals' => (float) ($row['expected_goals'] ?? 0),
                    'expected_assists' => (float) ($row['expected_assists'] ?? 0),
                    'expected_goal_involvements' => (float) ($row['expected_goal_involvements'] ?? 0),
                    'expected_goals_conceded' => (float) ($row['expected_goals_conceded'] ?? 0),
                    
                    // ICT Index
                    'influence' => (float) ($row['influence'] ?? 0),
                    'creativity' => (float) ($row['creativity'] ?? 0),
                    'threat' => (float) ($row['threat'] ?? 0),
                    'ict_index' => (float) ($row['ict_index'] ?? 0),
                ]
            );
            
            $playerStat->wasRecentlyCreated ? $imported++ : $updated++;
        }
        
        return [
            'imported' => $imported,
            'updated' => $updated,
            'total' => count($csvData)
        ];
    }

    /**
     * Import player match statistics
     */
    public function importPlayerMatchStats(): array
    {
        $url = $this->baseUrl . '/playermatchstats.csv';
        $csvData = $this->fetchCsvData($url);
        
        $imported = 0;
        $updated = 0;
        
        foreach ($csvData as $row) {
            $playerMatchStat = PlayerMatchStat::updateOrCreate(
                [
                    'player_id' => (int) $row['player_id'],
                    'match_id' => (int) $row['match_id']
                ],
                [
                    'start_min' => (int) ($row['start_min'] ?? 0),
                    'finish_min' => isset($row['finish_min']) ? (int) $row['finish_min'] : null,
                    'minutes_played' => (int) ($row['minutes_played'] ?? 0),
                    'goals' => (int) ($row['goals'] ?? 0),
                    'assists' => (int) ($row['assists'] ?? 0),
                    'penalties_scored' => (int) ($row['penalties_scored'] ?? 0),
                    'penalties_missed' => (int) ($row['penalties_missed'] ?? 0),
                    'total_shots' => (int) ($row['total_shots'] ?? 0),
                    'shots_on_target' => (int) ($row['shots_on_target'] ?? 0),
                    'xg' => (float) ($row['xg'] ?? 0),
                    'xa' => (float) ($row['xa'] ?? 0),
                    'xgot' => (float) ($row['xgot'] ?? 0),
                    'touches' => (int) ($row['touches'] ?? 0),
                    'accurate_passes' => (int) ($row['accurate_passes'] ?? 0),
                    'chances_created' => (int) ($row['chances_created'] ?? 0),
                    'successful_dribbles' => (int) ($row['successful_dribbles'] ?? 0),
                    'tackles' => (int) ($row['tackles'] ?? 0),
                    'tackles_won' => (int) ($row['tackles_won'] ?? 0),
                    'interceptions' => (int) ($row['interceptions'] ?? 0),
                    'blocks' => (int) ($row['blocks'] ?? 0),
                    'clearances' => (int) ($row['clearances'] ?? 0),
                    'duels_won' => (int) ($row['duels_won'] ?? 0),
                    'fouls_committed' => (int) ($row['fouls_committed'] ?? 0),
                    'yellow_cards' => (int) ($row['yellow_cards'] ?? 0),
                    'red_cards' => (int) ($row['red_cards'] ?? 0),
                    'saves' => (int) ($row['saves'] ?? 0),
                    'goals_conceded' => (int) ($row['goals_conceded'] ?? 0),
                    'bonus_points' => (int) ($row['bonus'] ?? 0),
                    'bps' => (int) ($row['bps'] ?? 0),
                ]
            );
            
            $playerMatchStat->wasRecentlyCreated ? $imported++ : $updated++;
        }
        
        return [
            'imported' => $imported,
            'updated' => $updated,
            'total' => count($csvData)
        ];
    }

    /**
     * Import player gameweek statistics (discrete weekly data)
     */
    public function importPlayerGameweekStats(): array
    {
        $gameweeks = Gameweek::orderBy('gameweek_id')->get();
        $totalImported = 0;
        $totalUpdated = 0;
        $totalProcessed = 0;
        
        foreach ($gameweeks as $gameweek) {
            try {
                $url = $this->baseUrl . '/By Gameweek/GW' . $gameweek->gameweek_id . '/player_gameweek_stats.csv';
                $csvData = $this->fetchCsvData($url);
                
                $imported = 0;
                $updated = 0;
                
                foreach ($csvData as $row) {
                    $playerGameweekStat = PlayerGameweekStat::updateOrCreate(
                        [
                            'player_id' => (int) $row['id'],
                            'gameweek' => $gameweek->gameweek_id
                        ],
                        [
                            'fixture_id' => isset($row['fixture_id']) ? (int) $row['fixture_id'] : null,
                            'minutes' => (int) ($row['minutes'] ?? 0),
                            'goals_scored' => (int) ($row['goals_scored'] ?? 0),
                            'assists' => (int) ($row['assists'] ?? 0),
                            'clean_sheets' => (int) ($row['clean_sheets'] ?? 0),
                            'goals_conceded' => (int) ($row['goals_conceded'] ?? 0),
                            'own_goals' => (int) ($row['own_goals'] ?? 0),
                            'penalties_saved' => (int) ($row['penalties_saved'] ?? 0),
                            'penalties_missed' => (int) ($row['penalties_missed'] ?? 0),
                            'yellow_cards' => (int) ($row['yellow_cards'] ?? 0),
                            'red_cards' => (int) ($row['red_cards'] ?? 0),
                            'saves' => (int) ($row['saves'] ?? 0),
                            'bonus' => (int) ($row['bonus'] ?? 0),
                            'bps' => (int) ($row['bps'] ?? 0),
                            'total_points' => (int) ($row['total_points'] ?? 0),
                        ]
                    );
                    
                    $playerGameweekStat->wasRecentlyCreated ? $imported++ : $updated++;
                }
                
                $totalImported += $imported;
                $totalUpdated += $updated;
                $totalProcessed += count($csvData);
                
            } catch (\Exception $e) {
                Log::warning("Could not import gameweek {$gameweek->gameweek_id} stats: " . $e->getMessage());
                continue;
            }
        }
        
        return [
            'imported' => $totalImported,
            'updated' => $totalUpdated,
            'total' => $totalProcessed
        ];
    }

    /**
     * Fetch CSV data from URL
     */
    private function fetchCsvData(string $url): array
    {
        $response = Http::timeout(30)->get($url);
        
        if (!$response->successful()) {
            throw new \Exception("Failed to fetch data from {$url}: " . $response->status());
        }
        
        $csvContent = $response->body();
        $lines = explode("\n", trim($csvContent));
        $headers = str_getcsv(array_shift($lines));
        
        $data = [];
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            $row = str_getcsv($line);
            $data[] = array_combine($headers, $row);
        }
        
        return $data;
    }

    /**
     * Map position string to element type number
     */
    private function mapPositionToElementType(string $position): int
    {
        return match($position) {
            'GKP' => 1,
            'DEF' => 2,
            'MID' => 3,
            'FWD' => 4,
            default => 4
        };
    }

    /**
     * Parse boolean values from CSV
     */
    private function parseBoolean($value): bool
    {
        if (is_bool($value)) return $value;
        if (is_string($value)) {
            return in_array(strtolower($value), ['true', '1', 'yes', 'on']);
        }
        return (bool) $value;
    }

    /**
     * Get current gameweek
     */
    private function getCurrentGameweek(): int
    {
        $currentGameweek = Gameweek::where('is_current', true)->first();
        return $currentGameweek ? $currentGameweek->gameweek_id : 1;
    }

    /**
     * Create basic gameweeks structure if CSV doesn't exist
     */
    private function createBasicGameweeks(): array
    {
        $created = 0;
        
        for ($i = 1; $i <= 38; $i++) {
            $deadline = now()->addWeeks($i - 1)->startOfWeek()->addDays(5)->addHours(11); // Saturday 11 AM
            
            Gameweek::updateOrCreate(
                ['gameweek_id' => $i],
                [
                    'name' => "Gameweek {$i}",
                    'deadline_time' => $deadline,
                    'deadline_time_epoch' => $deadline->timestamp,
                    'deadline_time_game_offset' => 0,
                    'finished' => false,
                    'is_current' => $i === 1,
                    'is_next' => $i === 2,
                    'is_previous' => false,
                ]
            );
            $created++;
        }
        
        return [
            'imported' => $created,
            'updated' => 0,
            'total' => 38
        ];
    }

    /**
     * Update specific gameweek data
     */
    public function importGameweekData(int $gameweek): array
    {
        $results = [];
        
        try {
            DB::beginTransaction();
            
            // Import gameweek-specific data
            $results['matches'] = $this->importGameweekMatches($gameweek);
            $results['fixtures'] = $this->importGameweekFixtures($gameweek);
            $results['player_stats'] = $this->importGameweekPlayerStats($gameweek);
            $results['player_gameweek_stats'] = $this->importSingleGameweekStats($gameweek);
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        
        return $results;
    }

    /**
     * Import matches for specific gameweek
     */
    private function importGameweekMatches(int $gameweek): array
    {
        $url = $this->baseUrl . '/By Gameweek/GW' . $gameweek . '/matches.csv';
        
        try {
            $csvData = $this->fetchCsvData($url);
            
            $imported = 0;
            $updated = 0;
            
            foreach ($csvData as $row) {
                $match = FPLMatch::updateOrCreate(
                    ['match_id' => (int) $row['match_id']],
                    [
                        'gameweek' => $gameweek,
                        'kickoff_time' => Carbon::parse($row['kickoff_time']),
                        'home_team' => (int) $row['home_team'],
                        'away_team' => (int) $row['away_team'],
                        'home_score' => (int) $row['home_score'],
                        'away_score' => (int) $row['away_score'],
                        'finished' => $this->parseBoolean($row['finished'] ?? true),
                    ]
                );
                
                $match->wasRecentlyCreated ? $imported++ : $updated++;
            }
            
            return ['imported' => $imported, 'updated' => $updated, 'total' => count($csvData)];
            
        } catch (\Exception $e) {
            return ['imported' => 0, 'updated' => 0, 'total' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Import single gameweek player stats
     */
    private function importSingleGameweekStats(int $gameweek): array
    {
        $url = $this->baseUrl . '/By Gameweek/GW' . $gameweek . '/player_gameweek_stats.csv';
        
        try {
            $csvData = $this->fetchCsvData($url);
            
            $imported = 0;
            $updated = 0;
            
            foreach ($csvData as $row) {
                $stat = PlayerGameweekStat::updateOrCreate(
                    [
                        'player_id' => (int) $row['id'],
                        'gameweek' => $gameweek
                    ],
                    [
                        'total_points' => (int) ($row['total_points'] ?? 0),
                        'minutes' => (int) ($row['minutes'] ?? 0),
                        'goals_scored' => (int) ($row['goals_scored'] ?? 0),
                        'assists' => (int) ($row['assists'] ?? 0),
                        'bonus' => (int) ($row['bonus'] ?? 0),
                        'bps' => (int) ($row['bps'] ?? 0),
                    ]
                );
                
                $stat->wasRecentlyCreated ? $imported++ : $updated++;
            }
            
            return ['imported' => $imported, 'updated' => $updated, 'total' => count($csvData)];
            
        } catch (\Exception $e) {
            return ['imported' => 0, 'updated' => 0, 'total' => 0, 'error' => $e->getMessage()];
        }
    }
}
