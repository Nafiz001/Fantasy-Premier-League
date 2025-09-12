<?php

namespace App\Services;

/**
 * FPL Bonus Points System (BPS) Calculator
 * 
 * The Bonus Points System awards additional points based on player performance.
 * The top 3 BPS scorers in each match receive bonus points: 3, 2, and 1 respectively.
 */
class FPLBonusPointsService
{
    /**
     * BPS scoring system weights
     */
    const BPS_SCORING = [
        // Attacking actions
        'goals' => 24,
        'assists' => 18,
        'key_passes' => 1,
        'successful_dribbles' => 1,
        'penalties_won' => 9,
        'big_chances_created' => 3,
        
        // Defensive actions
        'clean_sheets_gk_def' => 12,
        'clean_sheets_mid' => 6,
        'saves' => 2,
        'penalty_saves' => 15,
        'recoveries' => 1,
        'key_blocks' => 1,
        'interceptions' => 1,
        'tackles' => 2,
        'clearances' => 1,
        'blocks' => 1,
        
        // Negative actions
        'own_goals' => -6,
        'penalties_missed' => -6,
        'yellow_cards' => -3,
        'red_cards' => -9,
        'goals_conceded_gk_def' => -4,
        'errors_leading_to_goal' => -4,
        'errors_leading_to_attempt' => -1,
        'fouls' => -1
    ];

    /**
     * Bonus points awarded to top BPS scorers
     */
    const BONUS_POINTS = [3, 2, 1];

    /**
     * Calculate Bonus Points System (BPS) score for a player
     */
    public function calculateBPS(int $position, array $playerStats): int
    {
        $bpsScore = 0;

        // Goals
        $goals = $playerStats['goals_scored'] ?? 0;
        $bpsScore += $goals * self::BPS_SCORING['goals'];

        // Assists
        $assists = $playerStats['assists'] ?? 0;
        $bpsScore += $assists * self::BPS_SCORING['assists'];

        // Clean sheets (position-dependent)
        $cleanSheets = $playerStats['clean_sheets'] ?? 0;
        if (in_array($position, [1, 2])) { // GK, DEF
            $bpsScore += $cleanSheets * self::BPS_SCORING['clean_sheets_gk_def'];
        } elseif ($position == 3) { // MID
            $bpsScore += $cleanSheets * self::BPS_SCORING['clean_sheets_mid'];
        }

        // Goalkeeper specific
        if ($position == 1) {
            $saves = $playerStats['saves'] ?? 0;
            $bpsScore += $saves * self::BPS_SCORING['saves'];
            
            $penaltySaves = $playerStats['penalties_saved'] ?? 0;
            $bpsScore += $penaltySaves * self::BPS_SCORING['penalty_saves'];
            
            $goalsConceded = $playerStats['goals_conceded'] ?? 0;
            $bpsScore += $goalsConceded * self::BPS_SCORING['goals_conceded_gk_def'];
        }

        // Defensive actions (all positions)
        $tackles = $playerStats['tackles'] ?? 0;
        $bpsScore += $tackles * self::BPS_SCORING['tackles'];

        $interceptions = $playerStats['interceptions'] ?? 0;
        $bpsScore += $interceptions * self::BPS_SCORING['interceptions'];

        $clearances = $playerStats['clearances'] ?? 0;
        $bpsScore += $clearances * self::BPS_SCORING['clearances'];

        $recoveries = $playerStats['recoveries'] ?? 0;
        $bpsScore += $recoveries * self::BPS_SCORING['recoveries'];

        // Attacking actions
        $keyPasses = $playerStats['key_passes'] ?? 0;
        $bpsScore += $keyPasses * self::BPS_SCORING['key_passes'];

        $dribbles = $playerStats['successful_dribbles'] ?? 0;
        $bpsScore += $dribbles * self::BPS_SCORING['successful_dribbles'];

        $bigChances = $playerStats['big_chances_created'] ?? 0;
        $bpsScore += $bigChances * self::BPS_SCORING['big_chances_created'];

        // Negative actions
        $ownGoals = $playerStats['own_goals'] ?? 0;
        $bpsScore += $ownGoals * self::BPS_SCORING['own_goals'];

        $penaltyMisses = $playerStats['penalties_missed'] ?? 0;
        $bpsScore += $penaltyMisses * self::BPS_SCORING['penalties_missed'];

        $yellowCards = $playerStats['yellow_cards'] ?? 0;
        $bpsScore += $yellowCards * self::BPS_SCORING['yellow_cards'];

        $redCards = $playerStats['red_cards'] ?? 0;
        $bpsScore += $redCards * self::BPS_SCORING['red_cards'];

        $fouls = $playerStats['fouls'] ?? 0;
        $bpsScore += $fouls * self::BPS_SCORING['fouls'];

        return max(0, $bpsScore); // Ensure BPS is never negative
    }

    /**
     * Calculate bonus points for all players in a match
     * 
     * @param array $matchPlayers Array of players with their BPS scores
     * @return array Players with their bonus points awarded
     */
    public function calculateMatchBonusPoints(array $matchPlayers): array
    {
        // Calculate BPS for each player
        foreach ($matchPlayers as &$player) {
            $player['bps'] = $this->calculateBPS($player['position'], $player['stats']);
        }

        // Sort players by BPS (descending)
        usort($matchPlayers, function($a, $b) {
            return $b['bps'] <=> $a['bps'];
        });

        // Award bonus points to top 3 unique BPS scores
        $bonusPointsAwarded = [];
        $currentRank = 0;
        $previousBPS = null;
        
        foreach ($matchPlayers as &$player) {
            // Reset bonus points
            $player['bonus_points'] = 0;
            
            // If this is a new BPS score and we haven't awarded all bonus points
            if ($player['bps'] !== $previousBPS && $currentRank < 3 && $player['bps'] > 0) {
                $player['bonus_points'] = self::BONUS_POINTS[$currentRank];
                $bonusPointsAwarded[] = [
                    'player_id' => $player['player_id'],
                    'bps' => $player['bps'],
                    'bonus_points' => $player['bonus_points']
                ];
                $currentRank++;
                $previousBPS = $player['bps'];
            }
            // If same BPS as previous player, award same bonus points
            elseif ($player['bps'] === $previousBPS && $currentRank > 0) {
                $player['bonus_points'] = self::BONUS_POINTS[$currentRank - 1];
            }
        }

        return $matchPlayers;
    }

    /**
     * Get detailed BPS breakdown for a player
     */
    public function getBPSBreakdown(int $position, array $playerStats): array
    {
        $breakdown = [];
        
        // Goals
        if (($playerStats['goals_scored'] ?? 0) > 0) {
            $breakdown['goals'] = [
                'count' => $playerStats['goals_scored'],
                'points_per' => self::BPS_SCORING['goals'],
                'total' => $playerStats['goals_scored'] * self::BPS_SCORING['goals']
            ];
        }

        // Assists
        if (($playerStats['assists'] ?? 0) > 0) {
            $breakdown['assists'] = [
                'count' => $playerStats['assists'],
                'points_per' => self::BPS_SCORING['assists'],
                'total' => $playerStats['assists'] * self::BPS_SCORING['assists']
            ];
        }

        // Clean sheets
        if (($playerStats['clean_sheets'] ?? 0) > 0) {
            $pointsPer = in_array($position, [1, 2]) 
                ? self::BPS_SCORING['clean_sheets_gk_def'] 
                : ($position == 3 ? self::BPS_SCORING['clean_sheets_mid'] : 0);
            
            if ($pointsPer > 0) {
                $breakdown['clean_sheets'] = [
                    'count' => $playerStats['clean_sheets'],
                    'points_per' => $pointsPer,
                    'total' => $playerStats['clean_sheets'] * $pointsPer
                ];
            }
        }

        // Add other significant contributions...
        
        return $breakdown;
    }

    /**
     * Example BPS calculation
     */
    public function exampleBPSCalculation(): array
    {
        $matchPlayers = [
            [
                'player_id' => 1,
                'name' => 'Mo Salah',
                'position' => 4, // Forward
                'stats' => [
                    'goals_scored' => 2,
                    'assists' => 1,
                    'key_passes' => 3,
                    'successful_dribbles' => 2,
                    'tackles' => 1,
                    'yellow_cards' => 0,
                    'clean_sheets' => 0
                ]
            ],
            [
                'player_id' => 2,
                'name' => 'Kevin De Bruyne',
                'position' => 3, // Midfielder
                'stats' => [
                    'goals_scored' => 1,
                    'assists' => 2,
                    'key_passes' => 5,
                    'successful_dribbles' => 1,
                    'tackles' => 2,
                    'yellow_cards' => 0,
                    'clean_sheets' => 1
                ]
            ],
            [
                'player_id' => 3,
                'name' => 'Virgil van Dijk',
                'position' => 2, // Defender
                'stats' => [
                    'goals_scored' => 0,
                    'assists' => 0,
                    'key_passes' => 1,
                    'tackles' => 4,
                    'interceptions' => 3,
                    'clearances' => 8,
                    'yellow_cards' => 0,
                    'clean_sheets' => 1
                ]
            ]
        ];

        return $this->calculateMatchBonusPoints($matchPlayers);
    }
}
