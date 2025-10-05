<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FPLGameweek1Service
{
    /**
     * Import Gameweek 1 data from official FPL API
     */
    public function importGameweek1Data(): void
    {
        echo "🎯 Importing Gameweek 1 data from FPL API...\n";

        try {
            // Fetch gameweek 1 data from FPL API
            $response = Http::timeout(30)->get('https://fantasy.premierleague.com/api/event/1/live/');

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch GW1 data from FPL API: " . $response->status());
            }

            $gwData = $response->json();

            if (!isset($gwData['elements'])) {
                throw new \Exception("Invalid GW1 data structure from FPL API");
            }

            echo "   → Fetched data for " . count($gwData['elements']) . " players from FPL API\n";

            // Clear existing GW1 stats first
            $deletedCount = DB::table('player_gameweek_stats')->where('gameweek', 1)->delete();
            echo "   → Cleared {$deletedCount} existing GW1 records\n";

            $inserted = 0;
            $skipped = 0;

            foreach ($gwData['elements'] as $playerData) {
                $playerId = $playerData['id'];
                $stats = $playerData['stats'];

                // Check if player exists in our database
                $playerExists = DB::table('players')->where('fpl_id', $playerId)->exists();

                if (!$playerExists) {
                    $skipped++;
                    continue;
                }

                // Insert player gameweek stats
                DB::table('player_gameweek_stats')->insert([
                    'player_id' => $playerId,
                    'gameweek' => 1,
                    'minutes' => $stats['minutes'] ?? 0,
                    'goals_scored' => $stats['goals_scored'] ?? 0,
                    'assists' => $stats['assists'] ?? 0,
                    'clean_sheets' => $stats['clean_sheets'] ?? 0,
                    'goals_conceded' => $stats['goals_conceded'] ?? 0,
                    'own_goals' => $stats['own_goals'] ?? 0,
                    'penalties_saved' => $stats['penalties_saved'] ?? 0,
                    'penalties_missed' => $stats['penalties_missed'] ?? 0,
                    'yellow_cards' => $stats['yellow_cards'] ?? 0,
                    'red_cards' => $stats['red_cards'] ?? 0,
                    'saves' => $stats['saves'] ?? 0,
                    'bonus' => $stats['bonus'] ?? 0,
                    'bps' => $stats['bps'] ?? 0,
                    'influence' => isset($stats['influence']) ? floatval($stats['influence']) : 0,
                    'creativity' => isset($stats['creativity']) ? floatval($stats['creativity']) : 0,
                    'threat' => isset($stats['threat']) ? floatval($stats['threat']) : 0,
                    'ict_index' => isset($stats['ict_index']) ? floatval($stats['ict_index']) : 0,
                    'total_points' => $stats['total_points'] ?? 0,
                    'expected_goals' => isset($stats['expected_goals']) ? floatval($stats['expected_goals']) : 0,
                    'expected_assists' => isset($stats['expected_assists']) ? floatval($stats['expected_assists']) : 0,
                    'expected_goal_involvements' => isset($stats['expected_goal_involvements']) ? floatval($stats['expected_goal_involvements']) : 0,
                    'expected_goals_conceded' => isset($stats['expected_goals_conceded']) ? floatval($stats['expected_goals_conceded']) : 0,
                    'starts' => $stats['starts'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $inserted++;

                if ($inserted % 100 == 0) {
                    echo "   → Inserted {$inserted} GW1 records...\n";
                }
            }

            echo "   ✅ Successfully imported {$inserted} GW1 records from FPL API";
            if ($skipped > 0) {
                echo " (skipped {$skipped} unknown players)";
            }
            echo "\n";

            // Update player_stats table with GW1 totals
            $this->updatePlayerStats($gwData['elements']);

        } catch (\Exception $e) {
            echo "   ❌ Error importing GW1 data: " . $e->getMessage() . "\n";
            Log::error('FPL GW1 Import Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Update player_stats table with GW1 data
     */
    private function updatePlayerStats(array $elements): void
    {
        echo "   → Updating player_stats with GW1 totals...\n";

        $updated = 0;

        foreach ($elements as $playerData) {
            $playerId = $playerData['id'];
            $stats = $playerData['stats'];

            $affected = DB::table('player_stats')
                ->where('player_id', $playerId)
                ->update([
                    'total_points' => $stats['total_points'] ?? 0,
                    'form' => isset($stats['form']) ? floatval($stats['form']) : 0,
                    'updated_at' => now()
                ]);

            if ($affected > 0) {
                $updated++;
            }
        }

        echo "   ✅ Updated player_stats for {$updated} players\n";
    }
}
