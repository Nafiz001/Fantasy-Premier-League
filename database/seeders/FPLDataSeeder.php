<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FPLDataSeeder extends Seeder
{
    private $githubBaseUrl = 'https://raw.githubusercontent.com/olbauday/FPL-Elo-Insights/main/data/2025-2026/';

    public function run(): void
    {
        echo "🚀 Starting FPL Data Seeding from GitHub...\n";

        try {
            // Clear existing data
            $this->clearExistingData();

            // Seed in proper order due to foreign key constraints
            $this->seedTeams();
            $this->seedGameweeks();
            $this->seedPlayers();
            $this->seedPlayerStats();
            $this->seedPlayerGameweekStats();
            $this->seedFixtures();
            $this->seedMatches();

            echo "✅ FPL Data seeding completed successfully!\n";

        } catch (\Exception $e) {
            echo "❌ Error during seeding: " . $e->getMessage() . "\n";
            Log::error('FPL Data Seeder Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function clearExistingData(): void
    {
        echo "🧹 Clearing existing FPL data...\n";

        // Delete in reverse order due to foreign keys
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('player_gameweek_stats')->truncate();
        DB::table('player_match_stats')->truncate();
        DB::table('player_stats')->truncate();
        DB::table('matches')->truncate();
        DB::table('fixtures')->truncate();
        DB::table('players')->truncate();
        DB::table('gameweeks')->truncate();
        DB::table('teams')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo "   → Cleared all existing FPL data\n";
    }

    private function seedTeams(): void
    {
        echo "🏟️  Seeding teams...\n";

        $csvData = $this->fetchCsvFromGitHub('teams.csv');
        $rows = $this->parseCsv($csvData);

        $teams = [];
        foreach ($rows as $row) {
            $teams[] = [
                'fpl_code' => (int)$row['code'],
                'fpl_id' => (int)$row['id'],
                'name' => $row['name'],
                'short_name' => $row['short_name'],
                'strength' => (int)$row['strength'],
                'strength_overall_home' => (int)$row['strength_overall_home'],
                'strength_overall_away' => (int)$row['strength_overall_away'],
                'strength_attack_home' => (int)$row['strength_attack_home'],
                'strength_attack_away' => (int)$row['strength_attack_away'],
                'strength_defence_home' => (int)$row['strength_defence_home'],
                'strength_defence_away' => (int)$row['strength_defence_away'],
                'pulse_id' => !empty($row['pulse_id']) ? (int)$row['pulse_id'] : null,
                'elo' => !empty($row['elo']) ? (float)$row['elo'] : null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Insert in chunks to avoid memory issues
        $chunks = array_chunk($teams, 50);
        foreach ($chunks as $chunk) {
            DB::table('teams')->insert($chunk);
        }

        echo "   → Seeded " . count($teams) . " teams\n";
    }

    private function seedGameweeks(): void
    {
        echo "📅 Seeding gameweeks...\n";

        $csvData = $this->fetchCsvFromGitHub('gameweek_summaries.csv');
        $rows = $this->parseCsv($csvData);

        $gameweeks = [];
        foreach ($rows as $row) {
            $deadlineTime = Carbon::parse($row['deadline_time']);

            $gameweeks[] = [
                'gameweek_id' => (int)$row['id'],
                'name' => $row['name'],
                'deadline_time' => $deadlineTime,
                'deadline_time_epoch' => !empty($row['deadline_time_epoch']) ? (int)$row['deadline_time_epoch'] : $deadlineTime->timestamp,
                'deadline_time_game_offset' => !empty($row['deadline_time_game_offset']) ? (int)$row['deadline_time_game_offset'] : 0,
                'average_entry_score' => !empty($row['average_entry_score']) ? (float)$row['average_entry_score'] : null,
                'highest_score' => !empty($row['highest_score']) ? (int)$row['highest_score'] : null,
                'finished' => $this->parseBool($row['finished']),
                'is_previous' => $this->parseBool($row['is_previous']),
                'is_current' => $this->parseBool($row['is_current']),
                'is_next' => $this->parseBool($row['is_next']),
                'chip_plays' => $this->parseChipPlays($row['chip_plays']),
                'most_selected' => !empty($row['most_selected']) ? (int)$row['most_selected'] : null,
                'most_transferred_in' => !empty($row['most_transferred_in']) ? (int)$row['most_transferred_in'] : null,
                'most_captained' => !empty($row['most_captained']) ? (int)$row['most_captained'] : null,
                'most_vice_captained' => !empty($row['most_vice_captained']) ? (int)$row['most_vice_captained'] : null,
                'top_element' => !empty($row['top_element']) ? (int)$row['top_element'] : null,
                'top_element_info' => $this->parseTopElementInfo($row['top_element_info']),
                'transfers_made' => !empty($row['transfers_made']) ? (int)$row['transfers_made'] : null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Insert in chunks
        $chunks = array_chunk($gameweeks, 50);
        foreach ($chunks as $chunk) {
            DB::table('gameweeks')->insert($chunk);
        }

        echo "   → Seeded " . count($gameweeks) . " gameweeks\n";
    }

    private function seedPlayers(): void
    {
        echo "👥 Seeding players...\n";

        $csvData = $this->fetchCsvFromGitHub('players.csv');
        $rows = $this->parseCsv($csvData);

        $players = [];
        foreach ($rows as $row) {
            $players[] = [
                'fpl_code' => (int)$row['player_code'],
                'fpl_id' => (int)$row['player_id'],
                'first_name' => $row['first_name'],
                'second_name' => $row['second_name'],
                'web_name' => $row['web_name'],
                'team_code' => (int)$row['team_code'],
                'position' => $row['position'],
                'element_type' => $this->getElementType($row['position']),
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Insert in chunks
        $chunks = array_chunk($players, 100);
        foreach ($chunks as $chunk) {
            DB::table('players')->insert($chunk);
        }

        echo "   → Seeded " . count($players) . " players\n";
    }

    private function seedPlayerStats(): void
    {
        echo "📊 Seeding player statistics...\n";

        $csvData = $this->fetchCsvFromGitHub('playerstats.csv');
        $rows = $this->parseCsv($csvData);

        // Get existing player IDs and gameweek IDs for validation
        $existingPlayerIds = DB::table('players')->pluck('fpl_id')->toArray();
        $existingGameweekIds = DB::table('gameweeks')->pluck('gameweek_id')->toArray();

        echo "   → Found " . count($existingPlayerIds) . " players and " . count($existingGameweekIds) . " gameweeks for validation\n";

        $playerStats = [];
        $count = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $playerId = (int)$row['id'];
            $gameweek = (int)$row['gw'];

            // Skip if player or gameweek doesn't exist
            if (!in_array($playerId, $existingPlayerIds)) {
                $skipped++;
                continue;
            }

            if (!in_array($gameweek, $existingGameweekIds)) {
                $skipped++;
                continue;
            }

            $newsAdded = null;
            if (!empty($row['news_added'])) {
                try {
                    $newsAdded = Carbon::parse($row['news_added']);
                } catch (\Exception $e) {
                    $newsAdded = null;
                }
            }

            $playerStats[] = [
                'player_id' => $playerId,
                'gameweek' => $gameweek,
                'first_name' => $row['first_name'],
                'second_name' => $row['second_name'],
                'web_name' => $row['web_name'],
                'status' => $row['status'],
                'news' => !empty($row['news']) ? $row['news'] : null,
                'news_added' => $newsAdded,
                'chance_of_playing_next_round' => $this->parseNullableInt($row['chance_of_playing_next_round']),
                'chance_of_playing_this_round' => $this->parseNullableInt($row['chance_of_playing_this_round']),
                'now_cost' => (int)($row['now_cost'] * 10), // Convert to integer (FPL stores in tenths)
                'now_cost_rank' => $this->parseNullableInt($row['now_cost_rank']),
                'now_cost_rank_type' => $this->parseNullableInt($row['now_cost_rank_type']),
                'cost_change_event' => (int)$row['cost_change_event'],
                'cost_change_event_fall' => (int)$row['cost_change_event_fall'],
                'cost_change_start' => (int)$row['cost_change_start'],
                'cost_change_start_fall' => (int)$row['cost_change_start_fall'],
                'selected_by_percent' => (float)$row['selected_by_percent'],
                'selected_rank' => $this->parseNullableInt($row['selected_rank']),
                'selected_rank_type' => $this->parseNullableInt($row['selected_rank_type']),
                'total_points' => (int)$row['total_points'],
                'event_points' => (int)$row['event_points'],
                'points_per_game' => (float)$row['points_per_game'],
                'points_per_game_rank' => $this->parseNullableInt($row['points_per_game_rank']),
                'points_per_game_rank_type' => $this->parseNullableInt($row['points_per_game_rank_type']),
                'minutes' => (int)$row['minutes'],
                'goals_scored' => (int)$row['goals_scored'],
                'assists' => (int)$row['assists'],
                'clean_sheets' => (int)$row['clean_sheets'],
                'goals_conceded' => (int)$row['goals_conceded'],
                'own_goals' => (int)$row['own_goals'],
                'penalties_saved' => (int)$row['penalties_saved'],
                'penalties_missed' => (int)$row['penalties_missed'],
                'yellow_cards' => (int)$row['yellow_cards'],
                'red_cards' => (int)$row['red_cards'],
                'saves' => (int)$row['saves'],
                'starts' => (int)$row['starts'],
                'bonus' => (int)$row['bonus'],
                'bps' => (int)$row['bps'],
                'form' => (float)$row['form'],
                'form_rank' => $this->parseNullableInt($row['form_rank']),
                'form_rank_type' => $this->parseNullableInt($row['form_rank_type']),
                'value_form' => (float)$row['value_form'],
                'value_season' => (float)$row['value_season'],
                'dreamteam_count' => (int)$row['dreamteam_count'],
                'transfers_in' => (int)$row['transfers_in'],
                'transfers_in_event' => (int)$row['transfers_in_event'],
                'transfers_out' => (int)$row['transfers_out'],
                'transfers_out_event' => (int)$row['transfers_out_event'],
                'ep_next' => $this->parseNullableFloat($row['ep_next']),
                'ep_this' => $this->parseNullableFloat($row['ep_this']),
                'expected_goals' => (float)$row['expected_goals'],
                'expected_assists' => (float)$row['expected_assists'],
                'expected_goal_involvements' => (float)$row['expected_goal_involvements'],
                'expected_goals_conceded' => (float)$row['expected_goals_conceded'],
                'expected_goals_per_90' => (float)$row['expected_goals_per_90'],
                'expected_assists_per_90' => (float)$row['expected_assists_per_90'],
                'expected_goal_involvements_per_90' => (float)$row['expected_goal_involvements_per_90'],
                'expected_goals_conceded_per_90' => (float)$row['expected_goals_conceded_per_90'],
                'influence' => (float)$row['influence'],
                'influence_rank' => $this->parseNullableInt($row['influence_rank']),
                'influence_rank_type' => $this->parseNullableInt($row['influence_rank_type']),
                'creativity' => (float)$row['creativity'],
                'creativity_rank' => $this->parseNullableInt($row['creativity_rank']),
                'creativity_rank_type' => $this->parseNullableInt($row['creativity_rank_type']),
                'threat' => (float)$row['threat'],
                'threat_rank' => $this->parseNullableInt($row['threat_rank']),
                'threat_rank_type' => $this->parseNullableInt($row['threat_rank_type']),
                'ict_index' => (float)$row['ict_index'],
                'ict_index_rank' => $this->parseNullableInt($row['ict_index_rank']),
                'ict_index_rank_type' => $this->parseNullableInt($row['ict_index_rank_type']),
                'corners_and_indirect_freekicks_order' => $this->parseNullableInt($row['corners_and_indirect_freekicks_order']),
                'direct_freekicks_order' => $this->parseNullableInt($row['direct_freekicks_order']),
                'penalties_order' => $this->parseNullableInt($row['penalties_order']),
                'corners_and_indirect_freekicks_text' => !empty($row['corners_and_indirect_freekicks_text']) ? $row['corners_and_indirect_freekicks_text'] : null,
                'direct_freekicks_text' => !empty($row['direct_freekicks_text']) ? $row['direct_freekicks_text'] : null,
                'penalties_text' => !empty($row['penalties_text']) ? $row['penalties_text'] : null,
                'defensive_contribution' => (float)$row['defensive_contribution'],
                'defensive_contribution_per_90' => (float)$row['defensive_contribution_per_90'],
                'saves_per_90' => (float)$row['saves_per_90'],
                'clean_sheets_per_90' => (float)$row['clean_sheets_per_90'],
                'goals_conceded_per_90' => (float)$row['goals_conceded_per_90'],
                'starts_per_90' => (float)$row['starts_per_90'],
                'created_at' => now(),
                'updated_at' => now()
            ];

            $count++;

            // Insert in chunks to avoid memory issues
            if (count($playerStats) >= 100) {
                DB::table('player_stats')->insert($playerStats);
                $playerStats = [];
                echo "   → Processed $count player stats so far...\n";
            }
        }

        // Insert remaining records
        if (!empty($playerStats)) {
            DB::table('player_stats')->insert($playerStats);
        }

        echo "   → Seeded $count player statistics records\n";
    }

    private function seedPlayerGameweekStats(): void
    {
        echo "📊 Seeding player gameweek statistics...\n";

        $totalInserted = 0;
        $totalSkipped = 0;

        // Get existing player IDs and gameweek IDs for validation
        $existingPlayerIds = DB::table('players')->pluck('fpl_id')->toArray();
        $existingGameweekIds = DB::table('gameweeks')->pluck('gameweek_id')->toArray();

        echo "   → Found " . count($existingPlayerIds) . " players and " . count($existingGameweekIds) . " gameweeks for validation\n";

        // Check if local FPL-Elo-Insights data exists
        $localDataPath = base_path('FPL-Elo-Insights/data/2025-2026/By Gameweek');
        $useLocalData = false; // Force GitHub data to get latest updates

        if ($useLocalData) {
            echo "   → Using local FPL-Elo-Insights data\n";
        } else {
            echo "   → Using GitHub data (remote fetch)\n";
        }

        // Loop through each gameweek to get player gameweek stats
        foreach ($existingGameweekIds as $gw) {
            try {
                echo "   → Processing gameweek {$gw}...\n";

                if ($useLocalData) {
                    $filePath = $localDataPath . "/GW{$gw}/player_gameweek_stats.csv";
                    if (!file_exists($filePath)) {
                        echo "     → GW{$gw}: Local file not found, skipping\n";
                        continue;
                    }
                    $csvData = file_get_contents($filePath);
                } else {
                    $csvData = $this->fetchCsvFromGitHub("By%20Gameweek/GW{$gw}/player_gameweek_stats.csv");
                }

                $rows = $this->parseCsv($csvData);

                if (empty($rows)) {
                    echo "     → GW{$gw}: No data found in CSV\n";
                    continue;
                }

                // Debug: show first row keys for the first few gameweeks
                if ($gw <= 5) {
                    echo "     → GW{$gw}: Available columns: " . implode(', ', array_keys($rows[0])) . "\n";
                }

                $playerGameweekStats = [];
                $inserted = 0;
                $skipped = 0;

                foreach ($rows as $row) {
                    // Use 'id' column instead of 'player_id'
                    $playerId = (int)($row['id'] ?? 0);

                    if ($playerId <= 0) {
                        $skipped++;
                        continue;
                    }

                    // Skip if player doesn't exist
                    if (!in_array($playerId, $existingPlayerIds)) {
                        $skipped++;
                        continue;
                    }

                    // Get the actual database player_id from our players table
                    $dbPlayer = DB::table('players')->where('fpl_id', $playerId)->first();
                    if (!$dbPlayer) {
                        $skipped++;
                        continue;
                    }

                    $playerGameweekStats[] = [
                        'player_id' => $dbPlayer->id,
                        'gameweek' => $gw,
                        'minutes' => (int)($row['minutes'] ?? 0),
                        'goals_scored' => (int)($row['goals_scored'] ?? 0),
                        'assists' => (int)($row['assists'] ?? 0),
                        'clean_sheets' => (int)($row['clean_sheets'] ?? 0),
                        'goals_conceded' => (int)($row['goals_conceded'] ?? 0),
                        'own_goals' => (int)($row['own_goals'] ?? 0),
                        'penalties_saved' => (int)($row['penalties_saved'] ?? 0),
                        'penalties_missed' => (int)($row['penalties_missed'] ?? 0),
                        'yellow_cards' => (int)($row['yellow_cards'] ?? 0),
                        'red_cards' => (int)($row['red_cards'] ?? 0),
                        'saves' => (int)($row['saves'] ?? 0),
                        'bonus' => (int)($row['bonus'] ?? 0),
                        'bps' => (int)($row['bps'] ?? 0),
                        'total_points' => (int)($row['total_points'] ?? 0),
                        // ICT Index components (added in migration)
                        'influence' => (float)($row['influence'] ?? 0),
                        'creativity' => (float)($row['creativity'] ?? 0),
                        'threat' => (float)($row['threat'] ?? 0),
                        'ict_index' => (float)($row['ict_index'] ?? 0),
                        // Expected statistics (added in migration)
                        'expected_goals' => (float)($row['expected_goals'] ?? 0),
                        'expected_assists' => (float)($row['expected_assists'] ?? 0),
                        'expected_goal_involvements' => (float)($row['expected_goal_involvements'] ?? 0),
                        'expected_goals_conceded' => (float)($row['expected_goals_conceded'] ?? 0),
                        'starts' => (int)($row['starts'] ?? 0),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $inserted++;

                    // Insert in chunks to avoid memory issues
                    if (count($playerGameweekStats) >= 200) {
                        DB::table('player_gameweek_stats')->insert($playerGameweekStats);
                        $playerGameweekStats = [];
                    }
                }

                // Insert remaining records for this gameweek
                if (!empty($playerGameweekStats)) {
                    DB::table('player_gameweek_stats')->insert($playerGameweekStats);
                }

                echo "     → GW{$gw}: Inserted {$inserted} stats, skipped {$skipped}\n";

                $totalInserted += $inserted;
                $totalSkipped += $skipped;

            } catch (\Exception $e) {
                echo "   ⚠️  Could not load player gameweek stats for GW{$gw}: " . $e->getMessage() . "\n";
                echo "   🔍 Error details: " . $e->getFile() . " line " . $e->getLine() . "\n";
                continue;
            }
        }

        echo "   ✅ Successfully seeded {$totalInserted} player gameweek stats, skipped {$totalSkipped}\n";
    }

    private function fetchCsvFromGitHub(string $filename): string
    {
        $url = $this->githubBaseUrl . $filename;
        echo "   → Fetching $filename from GitHub...\n";

        $response = Http::timeout(60)->get($url);

        if (!$response->successful()) {
            throw new \Exception("Failed to fetch $filename from GitHub. Status: " . $response->status());
        }

        return $response->body();
    }

    private function parseCsv(string $csvData): array
    {
        $lines = explode("\n", trim($csvData));
        $headers = str_getcsv(array_shift($lines));

        $data = [];
        foreach ($lines as $line) {
            if (trim($line)) {
                $values = str_getcsv($line);
                if (count($values) === count($headers)) {
                    $data[] = array_combine($headers, $values);
                }
            }
        }

        return $data;
    }

    private function getElementType(string $position): int
    {
        return match($position) {
            'Goalkeeper' => 1,
            'Defender' => 2,
            'Midfielder' => 3,
            'Forward' => 4,
            default => 3 // Default to midfielder
        };
    }

    private function parseBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim($value));
        return in_array($value, ['true', '1', 'yes', 'on']);
    }

    private function parseNullableInt($value): ?int
    {
        return (!empty($value) && is_numeric($value)) ? (int)$value : null;
    }

    private function parseNullableFloat($value): ?float
    {
        return (!empty($value) && is_numeric($value)) ? (float)$value : null;
    }

    private function parseIntOrNull($value): ?int
    {
        return (!empty($value) && is_numeric($value)) ? (int)$value : null;
    }

    private function parseFloatOrNull($value): ?float
    {
        return (!empty($value) && is_numeric($value)) ? (float)$value : null;
    }

    private function parseChipPlays($value): ?string
    {
        if (empty($value) || $value === '[]') {
            return null;
        }

        try {
            // Convert Python-style list format to valid JSON
            $jsonValue = str_replace("'", '"', $value);

            // Validate it's proper JSON
            $decoded = json_decode($jsonValue, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $jsonValue;
            }
        } catch (\Exception $e) {
            // If parsing fails, return null
        }

        return null;
    }

    private function parseTopElementInfo($value): ?string
    {
        if (empty($value) || $value === '{}') {
            return null;
        }

        try {
            // Convert Python-style dict format to valid JSON
            $jsonValue = str_replace("'", '"', $value);

            // Validate it's proper JSON
            $decoded = json_decode($jsonValue, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $jsonValue;
            }
        } catch (\Exception $e) {
            // If parsing fails, return null
        }

        return null;
    }

    private function seedFixtures(): void
    {
        echo "📅 Seeding fixtures...\n";

        $fixtures = [];
        $count = 0;

        // Get existing team and gameweek IDs for validation
        $existingTeamIds = DB::table('teams')->pluck('fpl_id')->toArray();
        $existingGameweekIds = DB::table('gameweeks')->pluck('gameweek_id')->toArray();

        // Loop through each gameweek to get fixtures
        for ($gw = 1; $gw <= 38; $gw++) {
            try {
                $csvData = $this->fetchCsvFromGitHub("By%20Tournament/Premier%20League/GW{$gw}/fixtures.csv");
                $rows = $this->parseCsv($csvData);

                foreach ($rows as $row) {
                    $homeTeam = (int)$row['home_team'];
                    $awayTeam = (int)$row['away_team'];
                    $gameweek = (int)$row['gameweek'];

                    // Skip if teams or gameweek don't exist
                    if (!in_array($homeTeam, $existingTeamIds) ||
                        !in_array($awayTeam, $existingTeamIds) ||
                        !in_array($gameweek, $existingGameweekIds)) {
                        continue;
                    }

                    $fixtures[] = [
                        'fixture_id' => $count + 1,
                        'gameweek' => $gameweek,
                        'kickoff_time' => Carbon::parse($row['kickoff_time']),
                        'home_team' => $homeTeam,
                        'away_team' => $awayTeam,
                        'home_team_elo' => $this->parseFloatOrNull($row['home_team_elo']),
                        'away_team_elo' => $this->parseFloatOrNull($row['away_team_elo']),
                        'home_score' => isset($row['home_score']) ? $this->parseIntOrNull($row['home_score']) : null,
                        'away_score' => isset($row['away_score']) ? $this->parseIntOrNull($row['away_score']) : null,
                        'finished' => isset($row['finished']) ? filter_var($row['finished'], FILTER_VALIDATE_BOOLEAN) : false,
                        'tournament' => 'Premier League',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $count++;

                    // Insert in batches
                    if (count($fixtures) >= 100) {
                        DB::table('fixtures')->insert($fixtures);
                        echo "   → Inserted batch of " . count($fixtures) . " fixtures\n";
                        $fixtures = [];
                    }
                }

            } catch (\Exception $e) {
                echo "   ⚠️  Could not load fixtures for GW{$gw}: " . $e->getMessage() . "\n";
                continue;
            }
        }

        // Insert remaining fixtures
        if (!empty($fixtures)) {
            DB::table('fixtures')->insert($fixtures);
            echo "   → Inserted final batch of " . count($fixtures) . " fixtures\n";
        }

        echo "   ✅ Successfully seeded {$count} fixtures\n";
    }

    private function seedMatches(): void
    {
        echo "⚽ Seeding completed matches...\n";

        $matches = [];
        $count = 0;

        // Get existing team and gameweek IDs for validation
        $existingTeamIds = DB::table('teams')->pluck('fpl_id')->toArray();
        $existingGameweekIds = DB::table('gameweeks')->pluck('gameweek_id')->toArray();

        // Loop through each gameweek to get matches
        for ($gw = 1; $gw <= 38; $gw++) {
            try {
                $csvData = $this->fetchCsvFromGitHub("By%20Tournament/Premier%20League/GW{$gw}/matches.csv");
                $rows = $this->parseCsv($csvData);

                foreach ($rows as $row) {
                    $homeTeam = (int)$row['home_team'];
                    $awayTeam = (int)$row['away_team'];
                    $gameweek = (int)$row['gameweek'];

                    // Skip if teams or gameweek don't exist
                    if (!in_array($homeTeam, $existingTeamIds) ||
                        !in_array($awayTeam, $existingTeamIds) ||
                        !in_array($gameweek, $existingGameweekIds)) {
                        continue;
                    }

                    $matches[] = [
                        'match_id' => $count + 1,
                        'gameweek' => $gameweek,
                        'kickoff_time' => Carbon::parse($row['kickoff_time']),
                        'home_team' => $homeTeam,
                        'away_team' => $awayTeam,
                        'home_team_elo' => (float)$row['home_team_elo'],
                        'away_team_elo' => (float)$row['away_team_elo'],
                        'home_score' => (int)$row['home_score'],
                        'away_score' => (int)$row['away_score'],
                        'finished' => filter_var($row['finished'], FILTER_VALIDATE_BOOLEAN),
                        'tournament' => 'Premier League',
                        'home_possession' => $this->parseFloatOrNull($row['home_possession']),
                        'away_possession' => $this->parseFloatOrNull($row['away_possession']),
                        'home_expected_goals_xg' => $this->parseFloatOrNull($row['home_expected_goals_xg']),
                        'away_expected_goals_xg' => $this->parseFloatOrNull($row['away_expected_goals_xg']),
                        'home_total_shots' => $this->parseIntOrNull($row['home_total_shots']),
                        'away_total_shots' => $this->parseIntOrNull($row['away_total_shots']),
                        'home_shots_on_target' => $this->parseIntOrNull($row['home_shots_on_target']),
                        'away_shots_on_target' => $this->parseIntOrNull($row['away_shots_on_target']),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $count++;

                    // Insert in batches
                    if (count($matches) >= 100) {
                        DB::table('matches')->insert($matches);
                        echo "   → Inserted batch of " . count($matches) . " matches\n";
                        $matches = [];
                    }
                }

            } catch (\Exception $e) {
                echo "   ⚠️  Could not load matches for GW{$gw}: " . $e->getMessage() . "\n";
                continue;
            }
        }

        // Insert remaining matches
        if (!empty($matches)) {
            DB::table('matches')->insert($matches);
            echo "   → Inserted final batch of " . count($matches) . " matches\n";
        }

        echo "   ✅ Successfully seeded {$count} completed matches\n";
    }
}
