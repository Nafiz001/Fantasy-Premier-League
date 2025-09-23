<?php

namespace App\Services;

use App\Models\Gameweek;
use App\Models\PlayerStat;
use App\Models\Player;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FPLPointsService
{
    /**
     * Get the most recent finished gameweek
     */
    public function getLatestFinishedGameweekId()
    {
        $gameweek = Gameweek::where('finished', true)
            ->orderBy('deadline_time', 'desc')
            ->first();

        return $gameweek ? $gameweek->gameweek_id : null;
    }

    /**
     * Get the current gameweek (for display purposes)
     */
    public function getCurrentGameweek()
    {
        return Gameweek::where('is_current', true)->first() ??
               Gameweek::where('finished', true)->orderBy('deadline_time', 'desc')->first();
    }

    /**
     * Calculate FPL points for a player in a specific gameweek
     */
    public function calculatePlayerPoints($playerId, $gameweekId)
    {
        $stat = PlayerStat::where('player_id', $playerId)
            ->where('gameweek', $gameweekId)
            ->first();

        if (!$stat) {
            return 0;
        }

        $player = Player::where('fpl_id', $playerId)->first();
        if (!$player) {
            return 0;
        }

        $points = 0;

        // Minutes played points
        if ($stat->minutes >= 60) {
            $points += 2;
        } elseif ($stat->minutes > 0) {
            $points += 1;
        }

        // Goal scoring points based on position
        switch ($player->position) {
            case 'Goalkeeper':
                $points += $stat->goals_scored * 10;
                break;
            case 'Defender':
                $points += $stat->goals_scored * 6;
                break;
            case 'Midfielder':
                $points += $stat->goals_scored * 5;
                break;
            case 'Forward':
                $points += $stat->goals_scored * 4;
                break;
        }

        // Assists
        $points += $stat->assists * 3;

        // Clean sheets
        if ($stat->clean_sheets > 0) {
            if (in_array($player->position, ['Goalkeeper', 'Defender'])) {
                $points += $stat->clean_sheets * 4;
            } elseif ($player->position === 'Midfielder') {
                $points += $stat->clean_sheets * 1;
            }
        }

        // Goalkeeper specific points
        if ($player->position === 'Goalkeeper') {
            // Shots saved (1 point for every 3 saves)
            $points += floor($stat->saves / 3);
            // Penalty saves
            $points += $stat->penalties_saved * 5;
        }

        // Defensive contributions (using defensive_contribution field)
        $defensiveContrib = $stat->defensive_contribution ?? 0;
        if ($player->position === 'Defender' && $defensiveContrib >= 10) {
            $points += floor($defensiveContrib / 10) * 2;
        } elseif (in_array($player->position, ['Midfielder', 'Forward']) && $defensiveContrib >= 12) {
            $points += floor($defensiveContrib / 12) * 2;
        }

        // Penalty misses
        $points -= $stat->penalties_missed * 2;

        // Bonus points
        $points += $stat->bonus;

        // Goals conceded (for goalkeepers and defenders only)
        if (in_array($player->position, ['Goalkeeper', 'Defender'])) {
            $points -= floor($stat->goals_conceded / 2);
        }

        // Yellow cards
        $points -= $stat->yellow_cards;

        // Red cards
        $points -= $stat->red_cards * 3;

        // Own goals
        $points -= $stat->own_goals * 2;

        return max(0, $points); // Ensure points don't go below 0
    }

    /**
     * Get points breakdown for a user's squad in the latest finished gameweek
     */
    public function getSquadPoints($userId)
    {
        $user = User::find($userId);
        if (!$user || !$user->starting_xi) {
            return null;
        }

        $gameweekId = $this->getLatestFinishedGameweekId();
        if (!$gameweekId) {
            return null;
        }

        $gameweek = Gameweek::where('gameweek_id', $gameweekId)->first();

        // Parse starting XI
        $startingXI = is_string($user->starting_xi) ? json_decode($user->starting_xi, true) : $user->starting_xi;

        if (!$startingXI || !is_array($startingXI)) {
            return null;
        }

        $totalPoints = 0;
        $playerBreakdown = [];
        $positionTotals = [
            'Goalkeeper' => 0,
            'Defender' => 0,
            'Midfielder' => 0,
            'Forward' => 0
        ];

        foreach ($startingXI as $playerData) {
            $playerId = is_array($playerData) ? ($playerData['player_id'] ?? $playerData['id'] ?? null) : $playerData;

            if (!$playerId) continue;

            $player = Player::where('fpl_id', $playerId)->first();
            if (!$player) continue;

            $points = $this->calculatePlayerPoints($playerId, $gameweekId);

            // Apply captain multiplier
            $multiplier = 1;
            if ($playerId == $user->captain_id) {
                $multiplier = 2;
                $points *= 2;
            } elseif ($playerId == $user->vice_captain_id && $this->isCaptainNotPlaying($user->captain_id, $gameweekId)) {
                $multiplier = 2;
                $points *= 2;
            }

            $playerBreakdown[] = [
                'player_id' => $playerId,
                'name' => $player->web_name,
                'position' => $player->position,
                'points' => $points,
                'base_points' => $points / $multiplier,
                'multiplier' => $multiplier,
                'is_captain' => $playerId == $user->captain_id,
                'is_vice_captain' => $playerId == $user->vice_captain_id
            ];

            $totalPoints += $points;
            $positionTotals[$player->position] += $points;
        }

        return [
            'total_points' => $totalPoints,
            'gameweek_id' => $gameweekId,
            'gameweek_name' => $gameweek->name ?? "Gameweek {$gameweekId}",
            'player_breakdown' => $playerBreakdown,
            'position_totals' => $positionTotals,
            'average_points' => count($playerBreakdown) > 0 ? round($totalPoints / count($playerBreakdown), 1) : 0
        ];
    }

    /**
     * Check if captain didn't play (for vice-captain activation)
     */
    private function isCaptainNotPlaying($captainId, $gameweekId)
    {
        $stat = PlayerStat::where('player_id', $captainId)
            ->where('gameweek', $gameweekId)
            ->first();

        return !$stat || $stat->minutes == 0;
    }

    /**
     * Get points history for a user across all gameweeks
     */
    public function getUserPointsHistory($userId, $limit = 10)
    {
        $user = User::find($userId);
        if (!$user) return [];

        $finishedGameweeks = Gameweek::where('finished', true)
            ->orderBy('deadline_time', 'desc')
            ->limit($limit)
            ->get();

        $history = [];
        foreach ($finishedGameweeks as $gameweek) {
            $points = $this->getSquadPointsForGameweek($userId, $gameweek->gameweek_id);
            if ($points) {
                $history[] = [
                    'gameweek_id' => $gameweek->gameweek_id,
                    'gameweek_name' => $gameweek->name,
                    'points' => $points['total_points'],
                    'deadline' => $gameweek->deadline_time
                ];
            }
        }

        return $history;
    }

    /**
     * Get squad points for a specific gameweek (starting XI only)
     */
    public function getSquadPointsForGameweek($userId, $gameweekId)
    {
        $user = User::find($userId);
        if (!$user || !$user->starting_xi) {
            return ['total_points' => 0, 'player_details' => [], 'count' => 0, 'gameweek_id' => $gameweekId];
        }

        // Parse starting XI from JSON
        $startingXI = is_string($user->starting_xi) ? json_decode($user->starting_xi, true) : $user->starting_xi;
        if (!$startingXI || !is_array($startingXI)) {
            return ['total_points' => 0, 'player_details' => [], 'count' => 0, 'gameweek_id' => $gameweekId];
        }

        // Get all player IDs from starting XI
        $playerIds = array_map(function($playerData) {
            return is_array($playerData) ? ($playerData['player_id'] ?? $playerData['id'] ?? null) : $playerData;
        }, $startingXI);

        // Filter out null values
        $playerIds = array_filter($playerIds);

        if (empty($playerIds)) {
            return ['total_points' => 0, 'player_details' => [], 'count' => 0, 'gameweek_id' => $gameweekId];
        }

        // Get player details
        $players = Player::whereIn('fpl_id', $playerIds)->get()->keyBy('fpl_id');

        // Group players by position for formation-based selection
        $playersByPosition = [
            'Goalkeeper' => [],
            'Defender' => [],
            'Midfielder' => [],
            'Forward' => []
        ];

        foreach ($playerIds as $playerId) {
            if ($playerId && isset($players[$playerId])) {
                $player = $players[$playerId];
                $playersByPosition[$player->position][] = $player;
            }
        }

        // Get formation for starting XI selection
        $formation = $user->formation ?? '4-4-2';
        $formationParts = explode('-', $formation);
        $defCount = (int)($formationParts[0] ?? 4);
        $midCount = (int)($formationParts[1] ?? 4);
        $fwdCount = (int)($formationParts[2] ?? 2);

        // Select starting XI players
        $startingPlayers = [];

        // Always 1 goalkeeper
        if (!empty($playersByPosition['Goalkeeper'])) {
            $startingPlayers[] = $playersByPosition['Goalkeeper'][0];
        }

        // Add defenders
        $startingPlayers = array_merge($startingPlayers, array_slice($playersByPosition['Defender'], 0, $defCount));

        // Add midfielders
        $startingPlayers = array_merge($startingPlayers, array_slice($playersByPosition['Midfielder'], 0, $midCount));

        // Add forwards
        $startingPlayers = array_merge($startingPlayers, array_slice($playersByPosition['Forward'], 0, $fwdCount));

        $playersWithPoints = [];
        $totalPoints = 0;

        foreach ($startingPlayers as $player) {
            if (!$player) continue;

            // Calculate FPL points using the 2025/26 scoring system
            $points = $this->calculateFPL2025Points($player, $gameweekId);
            $originalPoints = $points;

            // Apply captain multiplier
            $multiplier = 1;
            if ($player->fpl_id == $user->captain_id) {
                $points *= 2;
                $multiplier = 2;
            } elseif ($player->fpl_id == $user->vice_captain_id && $this->isCaptainNotPlaying($user->captain_id, $gameweekId)) {
                $points *= 2;
                $multiplier = 2;
            }

            $totalPoints += $points;

            $playersWithPoints[] = [
                'player' => $player,
                'points' => $points,
                'original_points' => $originalPoints,
                'multiplier' => $multiplier,
                'is_captain' => $player->fpl_id == $user->captain_id,
                'is_vice_captain' => $player->fpl_id == $user->vice_captain_id
            ];
        }

        return [
            'total_points' => $totalPoints,
            'player_details' => $playersWithPoints,
            'count' => count($playersWithPoints),
            'gameweek_id' => $gameweekId
        ];
    }

    /**
     * Calculate FPL points for a player in a specific gameweek using 2025/26 scoring system
     */
    private function calculateFPL2025Points($player, $gameweek)
    {
        // Get player gameweek stats which contains total_points
        $stats = DB::table('player_gameweek_stats')
            ->where('player_id', $player->id)  // Use internal ID, not fpl_id
            ->where('gameweek', $gameweek)
            ->first();

        if (!$stats) {
            return 0; // Player didn't play
        }

        // If we have total_points in the database, use that
        if (isset($stats->total_points) && $stats->total_points != 0) {
            return $stats->total_points;
        }

        // Otherwise fall back to detailed calculation
        $points = 0;
        $position = $player->position;

        // Playing Time Points
        $minutes = $stats->minutes_played ?? $stats->minutes ?? 0;
        if ($minutes > 0) {
            if ($minutes >= 60) {
                $points += 2; // 2 points for 60+ minutes
            } else {
                $points += 1; // 1 point for playing up to 60 minutes
            }
        }
        $goals = $stats->goals ?? $stats->goals_scored ?? 0;
        if ($goals > 0) {
            switch ($position) {
                case 'GKP':
                case 'DEF':
                    $points += $goals * 6;
                    break;
                case 'MID':
                    $points += $goals * 5;
                    break;
                case 'FWD':
                    $points += $goals * 4;
                    break;
            }
        }

        // Assists (3 points each)
        $assists = $stats->assists ?? 0;
        if ($assists > 0) {
            $points += $assists * 3;
        }

        // Clean Sheets
        $cleanSheet = $stats->clean_sheet ?? $stats->clean_sheets ?? 0;
        if ($cleanSheet > 0) {
            switch ($position) {
                case 'GKP':
                case 'DEF':
                    $points += $cleanSheet * 4;
                    break;
                case 'MID':
                    $points += $cleanSheet * 1;
                    break;
            }
        }

        // Goalkeeper Saves (1 point for every 3 saves)
        if ($position === 'GKP') {
            $saves = $stats->saves ?? 0;
            if ($saves > 0) {
                $points += floor($saves / 3);
            }

            // Penalty Saves (5 points each for goalkeepers)
            $penaltiesSaved = $stats->penalties_saved ?? 0;
            if ($penaltiesSaved > 0) {
                $points += $penaltiesSaved * 5;
            }
        }

        // Defensive Contributions (2025/26 season enhancement)
        $tackles = $stats->tackles ?? 0;
        $interceptions = $stats->interceptions ?? 0;
        $clearances = $stats->clearances ?? 0;
        $blocks = $stats->blocks ?? 0;
        $recoveries = $stats->recoveries ?? 0;

        $cbits = $clearances + $blocks + $interceptions + $tackles;

        if ($position === 'DEF' && $cbits >= 10) {
            $points += 2; // 2 points for 10+ CBITs for defenders
        } elseif (in_array($position, ['MID', 'FWD'])) {
            $cbitsAndRecoveries = $cbits + $recoveries;
            if ($cbitsAndRecoveries >= 12) {
                $points += 2; // 2 points for 12+ CBITs and recoveries for mids/forwards
            }
        }

        // POINT DEDUCTIONS

        // Goals Conceded (-1 for every 2 goals conceded by GKP/DEF)
        if (in_array($position, ['GKP', 'DEF'])) {
            $goalsConceded = $stats->goals_conceded ?? 0;
            if ($goalsConceded > 0) {
                $points -= floor($goalsConceded / 2);
            }
        }

        // Penalty Misses (-2 points each)
        $penaltiesMissed = $stats->penalties_missed ?? 0;
        if ($penaltiesMissed > 0) {
            $points -= $penaltiesMissed * 2;
        }

        // Yellow Cards (-1 point each)
        $yellowCards = $stats->yellow_cards ?? 0;
        if ($yellowCards > 0) {
            $points -= $yellowCards * 1;
        }

        // Red Cards (-3 points each)
        $redCards = $stats->red_cards ?? 0;
        if ($redCards > 0) {
            $points -= $redCards * 3;
        }

        // Own Goals (-2 points each)
        $ownGoals = $stats->own_goals ?? 0;
        if ($ownGoals > 0) {
            $points -= $ownGoals * 2;
        }

        // Bonus Points (from BPS system)
        $bonus = $stats->bonus_points ?? $stats->bonus ?? 0;
        if ($bonus > 0) {
            $points += $bonus;
        }

        return max(0, $points); // Ensure points don't go below 0
    }

    /**
     * Get league-wide statistics for the latest gameweek
     */
    public function getGameweekStatistics($gameweekId = null)
    {
        if (!$gameweekId) {
            $gameweekId = $this->getLatestFinishedGameweekId();
        }

        if (!$gameweekId) return null;

        $gameweek = Gameweek::where('gameweek_id', $gameweekId)->first();

        return [
            'gameweek_id' => $gameweekId,
            'gameweek_name' => $gameweek->name ?? "Gameweek {$gameweekId}",
            'average_score' => $gameweek->average_entry_score ?? 0,
            'highest_score' => $gameweek->highest_score ?? 0,
            'most_captained' => $gameweek->most_captained ?? null,
            'most_selected' => $gameweek->most_selected ?? null,
            'most_transferred_in' => $gameweek->most_transferred_in ?? null,
            'finished' => $gameweek->finished ?? false
        ];
    }
}
