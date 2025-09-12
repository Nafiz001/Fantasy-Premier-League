<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class FPLEloInsightsService
{
    private const BASE_URL = 'https://raw.githubusercontent.com/olbauday/FPL-Elo-Insights/main/data/2025-2026';
    private const CURRENT_SEASON = '2025-26';
    
    /**
     * Available data files from FPL Elo Insights repository
     */
    private const DATA_FILES = [
        'teams' => 'teams.csv',
        'players' => 'players.csv', 
        'gameweeks' => 'gameweek_summaries.csv',
        'fixtures' => 'fixtures.csv',
        'player_stats' => 'playerstats.csv',
        'match_stats' => 'match_stats.csv',
        'player_match_stats' => 'player_match_stats.csv',
        'elo_ratings' => 'team_elo_ratings.csv'
    ];

    /**
     * Fetch and import all FPL data
     */
    public function importAllData()
    {
        Log::info('Starting FPL Elo Insights data import');
        
        $results = [];
        
        try {
            // Import in correct order to respect foreign key constraints
            $results['teams'] = $this->importTeams();
            $results['gameweeks'] = $this->importGameweeks();
            $results['players'] = $this->importPlayers();
            $results['fixtures'] = $this->importFixtures();
            $results['player_stats'] = $this->importPlayerStats();
            $results['match_stats'] = $this->importMatchStats();
            $results['player_match_stats'] = $this->importPlayerMatchStats();
            
            Log::info('FPL Elo Insights data import completed successfully');
            return $results;
            
        } catch (Exception $e) {
            Log::error('FPL data import failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Import teams data
     */
    public function importTeams()
    {
        $url = self::BASE_URL . '/' . self::DATA_FILES['teams'];
        $csvData = $this->fetchCsvData($url);
        
        if (empty($csvData)) {
            throw new Exception('Failed to fetch teams data');
        }

        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing teams first for clean import
        DB::table('teams')->truncate();

        $imported = 0;
        $header = array_shift($csvData); // Remove header row
        
        foreach ($csvData as $row) {
            $data = array_combine($header, $row);
            
            DB::table('teams')->insert([
                'fpl_id' => $data['id'],
                'name' => $data['name'],
                'short_name' => $data['short_name'],
                'fpl_code' => $data['code'],
                'elo' => $data['elo'] ?? 1500,
                'strength' => $data['strength'] ?? 3,
                'strength_overall_home' => $data['strength_overall_home'] ?? 3,
                'strength_overall_away' => $data['strength_overall_away'] ?? 3,
                'strength_attack_home' => $data['strength_attack_home'] ?? 3,
                'strength_attack_away' => $data['strength_attack_away'] ?? 3,
                'strength_defence_home' => $data['strength_defence_home'] ?? 3,
                'strength_defence_away' => $data['strength_defence_away'] ?? 3,
                'pulse_id' => $data['pulse_id'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $imported++;
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        Log::info("Imported {$imported} teams");
        return ['imported' => $imported];
    }

    /**
     * Import gameweeks data
     */
    public function importGameweeks()
    {
        $url = self::BASE_URL . '/' . self::DATA_FILES['gameweeks'];
        $csvData = $this->fetchCsvData($url);
        
        if (empty($csvData)) {
            throw new Exception('Failed to fetch gameweeks data');
        }

        $imported = 0;
        $header = array_shift($csvData);
        
        foreach ($csvData as $row) {
            if (empty($row) || count($row) < 5) {
                continue;
            }
            
            $data = array_combine($header, $row);
            
            if (!isset($data['id']) || !$data['id']) {
                continue;
            }
            
            // Helper function to safely parse JSON
            $parseJson = function($value) {
                if (empty($value) || $value === '[]') {
                    return [];
                }
                try {
                    return json_decode($value, true) ?? [];
                } catch (Exception $e) {
                    return [];
                }
            };
            
            DB::table('gameweeks')->updateOrInsert(
                ['gameweek_id' => (int)$data['id']],
                [
                    'gameweek_id' => (int)$data['id'],
                    'name' => $data['name'] ?? 'Gameweek ' . $data['id'],
                    'deadline_time' => !empty($data['deadline_time']) ? date('Y-m-d H:i:s', strtotime($data['deadline_time'])) : now(),
                    'deadline_time_epoch' => (int)($data['deadline_time_epoch'] ?? time()),
                    'deadline_time_game_offset' => (int)($data['deadline_time_game_offset'] ?? 0),
                    'average_entry_score' => !empty($data['average_entry_score']) ? (float)$data['average_entry_score'] : null,
                    'highest_score' => !empty($data['highest_score']) ? (int)$data['highest_score'] : null,
                    'finished' => isset($data['finished']) ? ($data['finished'] === 'True' || $data['finished'] === true) : false,
                    'is_previous' => isset($data['is_previous']) ? ($data['is_previous'] === 'True' || $data['is_previous'] === true) : false,
                    'is_current' => isset($data['is_current']) ? ($data['is_current'] === 'True' || $data['is_current'] === true) : false,
                    'is_next' => isset($data['is_next']) ? ($data['is_next'] === 'True' || $data['is_next'] === true) : false,
                    'chip_plays' => json_encode($parseJson($data['chip_plays'] ?? '[]')),
                    'most_selected' => !empty($data['most_selected']) ? (int)$data['most_selected'] : null,
                    'most_transferred_in' => !empty($data['most_transferred_in']) ? (int)$data['most_transferred_in'] : null,
                    'most_captained' => !empty($data['most_captained']) ? (int)$data['most_captained'] : null,
                    'most_vice_captained' => !empty($data['most_vice_captained']) ? (int)$data['most_vice_captained'] : null,
                    'top_element' => !empty($data['top_element']) ? (int)$data['top_element'] : null,
                    'top_element_info' => json_encode($parseJson($data['top_element_info'] ?? '[]')),
                    'transfers_made' => !empty($data['transfers_made']) ? (int)$data['transfers_made'] : null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
            $imported++;
        }
        
        Log::info("Imported {$imported} gameweeks");
        return ['imported' => $imported];
    }

    /**
     * Import players data
     */
    public function importPlayers()
    {
        $url = self::BASE_URL . '/' . self::DATA_FILES['players'];
        $csvData = $this->fetchCsvData($url);
        
        if (empty($csvData)) {
            throw new Exception('Failed to fetch players data');
        }

        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing players first for clean import
        DB::table('players')->truncate();

        $imported = 0;
        $header = array_shift($csvData); // Remove header row
        
        foreach ($csvData as $row) {
            $data = array_combine($header, $row);
            
            // Map position text to element_type number
            $elementType = match($data['position']) {
                'Goalkeeper' => 1,
                'Defender' => 2,
                'Midfielder' => 3,
                'Forward' => 4,
                default => 3 // Default to midfielder if unknown
            };
            
            DB::table('players')->insert([
                'fpl_id' => $data['player_id'],
                'first_name' => $data['first_name'],
                'second_name' => $data['second_name'],
                'web_name' => $data['web_name'],
                'position' => $data['position'],
                'element_type' => $elementType,
                'team_code' => $data['team_code'],
                'fpl_code' => $data['player_code'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $imported++;
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        Log::info("Imported {$imported} players");
        return ['imported' => $imported];
    }

    /**
     * Import fixtures data
     */
    public function importFixtures()
    {
        $url = self::BASE_URL . '/' . self::DATA_FILES['fixtures'];
        $csvData = $this->fetchCsvData($url);
        
        if (empty($csvData)) {
            throw new Exception('Failed to fetch fixtures data');
        }

        $imported = 0;
        $header = array_shift($csvData);
        
        foreach ($csvData as $row) {
            $data = array_combine($header, $row);
            
            DB::table('fixtures')->updateOrInsert(
                ['fixture_id' => $data['id']],
                [
                    'fixture_id' => $data['id'],
                    'gameweek' => $data['event'],
                    'home_team' => $data['team_h'],
                    'away_team' => $data['team_a'],
                    'home_score' => $data['team_h_score'] ?? null,
                    'away_score' => $data['team_a_score'] ?? null,
                    'kickoff_time' => $data['kickoff_time'] ?? null,
                    'finished' => $data['finished'] ?? false,
                    'home_team_elo' => $data['team_h_elo'] ?? 1500,
                    'away_team_elo' => $data['team_a_elo'] ?? 1500,
                    'home_difficulty' => $data['team_h_difficulty'] ?? 3,
                    'away_difficulty' => $data['team_a_difficulty'] ?? 3,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
            $imported++;
        }
        
        Log::info("Imported {$imported} fixtures");
        return ['imported' => $imported];
    }

    /**
     * Import player stats data (merged gameweek data)
     */
    public function importPlayerStats()
    {
        $url = self::BASE_URL . '/' . self::DATA_FILES['player_stats'];
        $csvData = $this->fetchCsvData($url);
        
        if (empty($csvData)) {
            throw new Exception('Failed to fetch player stats data');
        }

        $imported = 0;
        $header = array_shift($csvData); // This CSV has headers
        
        foreach ($csvData as $row) {
            // Skip empty rows
            if (empty($row) || count($row) < 10) {
                continue;
            }
            
            // Combine header with row data
            $data = array_combine($header, $row);
            
            // Skip if no valid player ID
            if (!isset($data['id']) || !$data['id']) {
                continue;
            }
            
            // Helper function to safely get numeric values
            $getNumeric = function($value, $default = 0) {
                return is_numeric($value) ? (float)$value : $default;
            };
            
            $getInteger = function($value, $default = 0) {
                return is_numeric($value) ? (int)$value : $default;
            };
            
            // Import complete player stats using actual CSV column mapping
            $gameweek = $getInteger($data['gw'] ?? 1);
            if ($gameweek <= 0) {
                $gameweek = 1; // Default to gameweek 1 if invalid
            }
            
            DB::table('player_stats')->updateOrInsert(
                [
                    'player_id' => $getInteger($data['id']),
                    'gameweek' => $gameweek
                ],
                [
                    'player_id' => $getInteger($data['id']),
                    'gameweek' => $gameweek,
                    'first_name' => $data['first_name'] ?? '',
                    'second_name' => $data['second_name'] ?? '',
                    'web_name' => $data['web_name'] ?? '',
                    'status' => $data['status'] ?? 'a',
                    'news' => $data['news'] ?? null,
                    'news_added' => !empty($data['news_added']) ? date('Y-m-d H:i:s', strtotime($data['news_added'])) : null,
                    'chance_of_playing_next_round' => $getInteger($data['chance_of_playing_next_round']),
                    'chance_of_playing_this_round' => $getInteger($data['chance_of_playing_this_round']),
                    'now_cost' => $getInteger($data['now_cost']),
                    'now_cost_rank' => $getInteger($data['now_cost_rank']),
                    'now_cost_rank_type' => $getInteger($data['now_cost_rank_type']),
                    'cost_change_event' => $getInteger($data['cost_change_event']),
                    'cost_change_event_fall' => $getInteger($data['cost_change_event_fall']),
                    'cost_change_start' => $getInteger($data['cost_change_start']),
                    'cost_change_start_fall' => $getInteger($data['cost_change_start_fall']),
                    'selected_by_percent' => $getNumeric($data['selected_by_percent']),
                    'selected_rank' => $getInteger($data['selected_rank']),
                    'selected_rank_type' => $getInteger($data['selected_rank_type']),
                    'total_points' => $getInteger($data['total_points']),
                    'event_points' => $getInteger($data['event_points']),
                    'points_per_game' => $getNumeric($data['points_per_game']),
                    'points_per_game_rank' => $getInteger($data['points_per_game_rank']),
                    'points_per_game_rank_type' => $getInteger($data['points_per_game_rank_type']),
                    'minutes' => $getInteger($data['minutes']),
                    'goals_scored' => $getInteger($data['goals_scored']),
                    'assists' => $getInteger($data['assists']),
                    'clean_sheets' => $getInteger($data['clean_sheets']),
                    'goals_conceded' => $getInteger($data['goals_conceded']),
                    'own_goals' => $getInteger($data['own_goals']),
                    'penalties_saved' => $getInteger($data['penalties_saved']),
                    'penalties_missed' => $getInteger($data['penalties_missed']),
                    'yellow_cards' => $getInteger($data['yellow_cards']),
                    'red_cards' => $getInteger($data['red_cards']),
                    'saves' => $getInteger($data['saves']),
                    'starts' => $getInteger($data['starts']),
                    'bonus' => $getInteger($data['bonus']),
                    'bps' => $getInteger($data['bps']),
                    'form' => $getNumeric($data['form']),
                    'form_rank' => $getInteger($data['form_rank']),
                    'form_rank_type' => $getInteger($data['form_rank_type']),
                    'value_form' => $getNumeric($data['value_form']),
                    'value_season' => $getNumeric($data['value_season']),
                    'dreamteam_count' => $getInteger($data['dreamteam_count']),
                    'transfers_in' => $getInteger($data['transfers_in']),
                    'transfers_in_event' => $getInteger($data['transfers_in_event']),
                    'transfers_out' => $getInteger($data['transfers_out']),
                    'transfers_out_event' => $getInteger($data['transfers_out_event']),
                    'ep_next' => $getNumeric($data['ep_next']),
                    'ep_this' => $getNumeric($data['ep_this']),
                    'expected_goals' => $getNumeric($data['expected_goals']),
                    'expected_assists' => $getNumeric($data['expected_assists']),
                    'expected_goal_involvements' => $getNumeric($data['expected_goal_involvements']),
                    'expected_goals_conceded' => $getNumeric($data['expected_goals_conceded']),
                    'expected_goals_per_90' => $getNumeric($data['expected_goals_per_90']),
                    'expected_assists_per_90' => $getNumeric($data['expected_assists_per_90']),
                    'expected_goal_involvements_per_90' => $getNumeric($data['expected_goal_involvements_per_90']),
                    'expected_goals_conceded_per_90' => $getNumeric($data['expected_goals_conceded_per_90']),
                    'influence' => $getNumeric($data['influence']),
                    'influence_rank' => $getInteger($data['influence_rank']),
                    'influence_rank_type' => $getInteger($data['influence_rank_type']),
                    'creativity' => $getNumeric($data['creativity']),
                    'creativity_rank' => $getInteger($data['creativity_rank']),
                    'creativity_rank_type' => $getInteger($data['creativity_rank_type']),
                    'threat' => $getNumeric($data['threat']),
                    'threat_rank' => $getInteger($data['threat_rank']),
                    'threat_rank_type' => $getInteger($data['threat_rank_type']),
                    'ict_index' => $getNumeric($data['ict_index']),
                    'ict_index_rank' => $getInteger($data['ict_index_rank']),
                    'ict_index_rank_type' => $getInteger($data['ict_index_rank_type']),
                    'corners_and_indirect_freekicks_order' => $getInteger($data['corners_and_indirect_freekicks_order']),
                    'direct_freekicks_order' => $getInteger($data['direct_freekicks_order']),
                    'penalties_order' => $getInteger($data['penalties_order']),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
            $imported++;
        }
        
        Log::info("Imported {$imported} player stats records");
        return ['imported' => $imported];
    }

    /**
     * Import match stats data
     */
    public function importMatchStats()
    {
        $url = self::BASE_URL . '/' . self::DATA_FILES['match_stats'];
        $csvData = $this->fetchCsvData($url);
        
        if (empty($csvData)) {
            Log::warning('Match stats data not available or empty');
            return ['imported' => 0];
        }

        $imported = 0;
        $header = array_shift($csvData);
        
        foreach ($csvData as $row) {
            $data = array_combine($header, $row);
            
            DB::table('matches')->updateOrInsert(
                ['match_id' => $data['fixture_id']],
                [
                    'match_id' => $data['fixture_id'],
                    'gameweek' => $data['GW'],
                    'home_team' => $data['team_h'],
                    'away_team' => $data['team_a'],
                    'home_score' => $data['team_h_score'] ?? 0,
                    'away_score' => $data['team_a_score'] ?? 0,
                    'kickoff_time' => $data['kickoff_time'] ?? null,
                    'finished' => $data['finished'] ?? false,
                    'home_possession' => $data['home_possession'] ?? 50,
                    'away_possession' => $data['away_possession'] ?? 50,
                    'home_total_shots' => $data['home_shots'] ?? 0,
                    'away_total_shots' => $data['away_shots'] ?? 0,
                    'home_shots_on_target' => $data['home_shots_on_target'] ?? 0,
                    'away_shots_on_target' => $data['away_shots_on_target'] ?? 0,
                    'home_expected_goals_xg' => $data['home_xG'] ?? 0,
                    'away_expected_goals_xg' => $data['away_xG'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
            $imported++;
        }
        
        Log::info("Imported {$imported} match stats records");
        return ['imported' => $imported];
    }

    /**
     * Import player match stats data
     */
    public function importPlayerMatchStats()
    {
        $url = self::BASE_URL . '/' . self::DATA_FILES['player_match_stats'];
        $csvData = $this->fetchCsvData($url);
        
        if (empty($csvData)) {
            Log::warning('Player match stats data not available or empty');
            return ['imported' => 0];
        }

        $imported = 0;
        $header = array_shift($csvData);
        
        foreach ($csvData as $row) {
            $data = array_combine($header, $row);
            
            DB::table('player_match_stats')->updateOrInsert(
                [
                    'player_id' => $data['element'],
                    'fixture_id' => $data['fixture_id']
                ],
                [
                    'player_id' => $data['element'],
                    'fixture_id' => $data['fixture_id'],
                    'minutes' => $data['minutes'] ?? 0,
                    'goals_scored' => $data['goals_scored'] ?? 0,
                    'assists' => $data['assists'] ?? 0,
                    'clean_sheets' => $data['clean_sheets'] ?? 0,
                    'yellow_cards' => $data['yellow_cards'] ?? 0,
                    'red_cards' => $data['red_cards'] ?? 0,
                    'saves' => $data['saves'] ?? 0,
                    'bonus' => $data['bonus'] ?? 0,
                    'bps' => $data['bps'] ?? 0,
                    'shots' => $data['shots'] ?? 0,
                    'key_passes' => $data['key_passes'] ?? 0,
                    'passes_completed' => $data['passes_completed'] ?? 0,
                    'passes_attempted' => $data['passes_attempted'] ?? 0,
                    'tackles' => $data['tackles'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
            $imported++;
        }
        
        Log::info("Imported {$imported} player match stats records");
        return ['imported' => $imported];
    }

    /**
     * Fetch CSV data from URL
     */
    private function fetchCsvData($url)
    {
        try {
            $response = Http::timeout(60)->get($url);
            
            if (!$response->successful()) {
                throw new Exception("Failed to fetch data from: {$url}. Status: {$response->status()}");
            }
            
            $csvContent = $response->body();
            $lines = explode("\n", $csvContent);
            $data = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $data[] = str_getcsv($line);
                }
            }
            
            return $data;
            
        } catch (Exception $e) {
            Log::error("Error fetching CSV data from {$url}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get position name from element type
     */
    private function getPositionName($elementType)
    {
        return match($elementType) {
            1 => 'GK',
            2 => 'DEF', 
            3 => 'MID',
            4 => 'FWD',
            default => 'Unknown'
        };
    }

    /**
     * Get current gameweek
     */
    public function getCurrentGameweek()
    {
        return DB::table('gameweeks')
            ->where('is_current', true)
            ->first();
    }

    /**
     * Get next gameweek
     */
    public function getNextGameweek()
    {
        return DB::table('gameweeks')
            ->where('is_next', true)
            ->first();
    }

    /**
     * Update data for specific gameweek
     */
    public function updateGameweekData($gameweek)
    {
        Log::info("Updating data for gameweek {$gameweek}");
        
        // Update player stats for specific gameweek
        $this->importPlayerStats();
        
        // Update fixtures if needed
        $this->importFixtures();
        
        Log::info("Gameweek {$gameweek} data updated successfully");
        
        return ['status' => 'success', 'gameweek' => $gameweek];
    }

    /**
     * Get available CSV files from repository
     */
    public function getAvailableFiles()
    {
        return self::DATA_FILES;
    }

    /**
     * Check if data is up to date
     */
    public function isDataUpToDate()
    {
        $lastUpdate = DB::table('player_stats')
            ->max('updated_at');
            
        if (!$lastUpdate) {
            return false;
        }
        
        // Data should be updated twice daily (5 AM and 5 PM UTC)
        $lastUpdateTime = strtotime($lastUpdate);
        $currentTime = time();
        
        // If last update was more than 12 hours ago, data might be stale
        return ($currentTime - $lastUpdateTime) < (12 * 60 * 60);
    }

    /**
     * Schedule automatic updates
     */
    public function scheduleUpdate()
    {
        if (!$this->isDataUpToDate()) {
            Log::info('Data is stale, triggering update');
            return $this->importAllData();
        }
        
        Log::info('Data is up to date');
        return ['status' => 'up_to_date'];
    }
}
