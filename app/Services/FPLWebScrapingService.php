<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FPLWebScrapingService
{
    private const FPL_BASE_URL = 'https://fantasy.premierleague.com/api';
    private const CURRENT_SEASON = '2024-25'; // Current FPL season

    /**
     * Fetch and update GW1 data from official FPL API
     */
    public function updateGW1DataFromAPI(): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'players_updated' => 0,
            'errors' => []
        ];

        try {
            echo "🌐 Fetching GW1 data from official FPL API...\n";

            // Get bootstrap data (contains all players and their current stats)
            $bootstrapResponse = Http::timeout(30)->get(self::FPL_BASE_URL . '/bootstrap-static/');

            if (!$bootstrapResponse->successful()) {
                throw new \Exception("Failed to fetch bootstrap data: " . $bootstrapResponse->status());
            }

            $bootstrapData = $bootstrapResponse->json();

            if (!isset($bootstrapData['elements'])) {
                throw new \Exception("Invalid bootstrap data structure");
            }

            echo "✅ Fetched data for " . count($bootstrapData['elements']) . " players\n";

            // Get specific GW1 event data
            $gw1Response = Http::timeout(30)->get(self::FPL_BASE_URL . '/event/1/live/');

            if (!$gw1Response->successful()) {
                echo "⚠️  GW1 live data not available, using bootstrap data only\n";
                $gw1Data = null;
            } else {
                $gw1Data = $gw1Response->json();
                echo "✅ Fetched GW1 live event data\n";
            }

            // Update database with real FPL data
            $playersUpdated = $this->updatePlayersFromFPLData($bootstrapData['elements'], $gw1Data);

            $result['success'] = true;
            $result['message'] = "Successfully updated GW1 data from FPL API";
            $result['players_updated'] = $playersUpdated;

        } catch (\Exception $e) {
            $result['message'] = "Error: " . $e->getMessage();
            $result['errors'][] = $e->getMessage();
            Log::error('FPL API Error: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Update players with real FPL data
     */
    private function updatePlayersFromFPLData(array $players, ?array $gw1Data): int
    {
        $updated = 0;
        $gw1LiveData = $gw1Data['elements'] ?? [];

        echo "🔄 Updating player data...\n";

        foreach ($players as $player) {
            try {
                $fplId = $player['id'];

                // Find corresponding player in our database
                $dbPlayer = DB::table('players')->where('fpl_id', $fplId)->first();

                if (!$dbPlayer) {
                    continue; // Skip if player not in our database
                }

                // Get live GW1 data for this player if available
                $liveData = null;
                foreach ($gw1LiveData as $live) {
                    if ($live['id'] == $fplId) {
                        $liveData = $live;
                        break;
                    }
                }

                // Calculate GW1 points using official FPL logic
                $gw1Points = $this->calculateGW1Points($player, $liveData);
                $gw1Minutes = $this->getGW1Minutes($player, $liveData);

                // Update or insert GW1 stats
                DB::table('player_gameweek_stats')
                    ->updateOrInsert(
                        [
                            'player_id' => $dbPlayer->id,
                            'gameweek' => 1
                        ],
                        [
                            'total_points' => $gw1Points,
                            'minutes' => $gw1Minutes,
                            'goals_scored' => $liveData['stats']['goals_scored'] ?? 0,
                            'assists' => $liveData['stats']['assists'] ?? 0,
                            'clean_sheets' => $liveData['stats']['clean_sheets'] ?? 0,
                            'goals_conceded' => $liveData['stats']['goals_conceded'] ?? 0,
                            'own_goals' => $liveData['stats']['own_goals'] ?? 0,
                            'penalties_saved' => $liveData['stats']['penalties_saved'] ?? 0,
                            'penalties_missed' => $liveData['stats']['penalties_missed'] ?? 0,
                            'yellow_cards' => $liveData['stats']['yellow_cards'] ?? 0,
                            'red_cards' => $liveData['stats']['red_cards'] ?? 0,
                            'saves' => $liveData['stats']['saves'] ?? 0,
                            'bonus' => $liveData['stats']['bonus'] ?? 0,
                            'bps' => $liveData['stats']['bps'] ?? 0,
                            'updated_at' => now()
                        ]
                    );

                $updated++;

                if ($updated % 50 == 0) {
                    echo "   → Updated $updated players so far...\n";
                }

            } catch (\Exception $e) {
                echo "   ⚠️  Error updating player $fplId: " . $e->getMessage() . "\n";
                continue;
            }
        }

        echo "✅ Updated $updated players with real FPL data\n";
        return $updated;
    }

    /**
     * Calculate GW1 points using real FPL rules
     */
    private function calculateGW1Points(array $player, ?array $liveData): int
    {
        if (!$liveData || !isset($liveData['stats'])) {
            // Fallback to basic calculation if no live data
            return max(0, $player['event_points'] ?? 0);
        }

        $stats = $liveData['stats'];
        $points = 0;

        // Minutes played (2 points for 60+ minutes, 1 point for 1-59 minutes)
        $minutes = $stats['minutes'] ?? 0;
        if ($minutes >= 60) {
            $points += 2;
        } elseif ($minutes > 0) {
            $points += 1;
        }

        // Goals (varies by position)
        $position = $player['element_type']; // 1=GK, 2=DEF, 3=MID, 4=FWD
        $goals = $stats['goals_scored'] ?? 0;
        if ($position == 1 || $position == 2) { // GK or DEF
            $points += $goals * 6;
        } elseif ($position == 3) { // MID
            $points += $goals * 5;
        } else { // FWD
            $points += $goals * 4;
        }

        // Assists
        $points += ($stats['assists'] ?? 0) * 3;

        // Clean sheets (GK and DEF only)
        if (($position == 1 || $position == 2) && ($stats['clean_sheets'] ?? 0) > 0) {
            $points += 4;
        }

        // Goals conceded (GK and DEF only) - lose 1 point for every 2 goals conceded
        if ($position == 1 || $position == 2) {
            $points -= floor(($stats['goals_conceded'] ?? 0) / 2);
        }

        // Penalty saves (GK only)
        if ($position == 1) {
            $points += ($stats['penalties_saved'] ?? 0) * 5;
        }

        // Penalties missed
        $points -= ($stats['penalties_missed'] ?? 0) * 2;

        // Yellow cards
        $points -= ($stats['yellow_cards'] ?? 0) * 1;

        // Red cards
        $points -= ($stats['red_cards'] ?? 0) * 3;

        // Own goals
        $points -= ($stats['own_goals'] ?? 0) * 2;

        // Saves (GK only) - 1 point for every 3 saves
        if ($position == 1) {
            $points += floor(($stats['saves'] ?? 0) / 3);
        }

        // Bonus points
        $points += ($stats['bonus'] ?? 0);

        return max(0, $points); // Ensure non-negative
    }

    /**
     * Get GW1 minutes
     */
    private function getGW1Minutes(array $player, ?array $liveData): int
    {
        if ($liveData && isset($liveData['stats']['minutes'])) {
            return max(0, $liveData['stats']['minutes']);
        }

        // Fallback: estimate based on whether player played
        $eventPoints = $player['event_points'] ?? 0;
        return $eventPoints > 0 ? 90 : 0; // Simple estimation
    }
}
