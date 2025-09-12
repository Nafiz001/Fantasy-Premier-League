<?php

namespace App\Services;

/**
 * FPL Point Calculation Service
 * 
 * This service handles all Fantasy Premier League point calculations
 * based on the official Premier League scoring system.
 */
class FPLScoringService
{
    /**
     * Player position constants
     */
    const GOALKEEPER = 1;
    const DEFENDER = 2;
    const MIDFIELDER = 3;
    const FORWARD = 4;

    /**
     * Base points for playing
     */
    const POINTS_FOR_PLAYING = [
        self::GOALKEEPER => 2,
        self::DEFENDER => 2,
        self::MIDFIELDER => 2,
        self::FORWARD => 2
    ];

    /**
     * Points for playing 60+ minutes
     */
    const POINTS_FOR_60_MINUTES = [
        self::GOALKEEPER => 2,
        self::DEFENDER => 2,
        self::MIDFIELDER => 2,
        self::FORWARD => 2
    ];

    /**
     * Points for goals scored
     */
    const POINTS_FOR_GOAL = [
        self::GOALKEEPER => 10,
        self::DEFENDER => 6,
        self::MIDFIELDER => 5,
        self::FORWARD => 4
    ];

    /**
     * Points for assists
     */
    const POINTS_FOR_ASSIST = 3;

    /**
     * Clean sheet points
     */
    const POINTS_FOR_CLEAN_SHEET = [
        self::GOALKEEPER => 4,
        self::DEFENDER => 4,
        self::MIDFIELDER => 1,
        self::FORWARD => 0
    ];

    /**
     * Points for saves (goalkeepers only)
     */
    const POINTS_PER_3_SAVES = 1;

    /**
     * Penalty points
     */
    const POINTS_FOR_PENALTY_SAVE = 5;
    const POINTS_FOR_PENALTY_MISS = -2;

    /**
     * Cards and disciplinary points
     */
    const POINTS_FOR_YELLOW_CARD = -1;
    const POINTS_FOR_RED_CARD = -3;

    /**
     * Own goal points
     */
    const POINTS_FOR_OWN_GOAL = -2;

    /**
     * Goals conceded points (GK and DEF only)
     */
    const POINTS_FOR_2_GOALS_CONCEDED = -1;

    /**
     * Calculate total points for a player in a gameweek
     *
     * @param int $position Player position (1-4)
     * @param array $stats Player statistics for the gameweek
     * @return int Total points scored
     */
    public function calculatePlayerPoints(int $position, array $stats): int
    {
        $totalPoints = 0;

        // Playing time points
        $totalPoints += $this->calculatePlayingPoints($position, $stats);

        // Goal points
        $totalPoints += $this->calculateGoalPoints($position, $stats);

        // Assist points
        $totalPoints += $this->calculateAssistPoints($stats);

        // Clean sheet points
        $totalPoints += $this->calculateCleanSheetPoints($position, $stats);

        // Save points (goalkeepers only)
        $totalPoints += $this->calculateSavePoints($position, $stats);

        // Penalty points
        $totalPoints += $this->calculatePenaltyPoints($stats);

        // Card points
        $totalPoints += $this->calculateCardPoints($stats);

        // Own goal points
        $totalPoints += $this->calculateOwnGoalPoints($stats);

        // Goals conceded points
        $totalPoints += $this->calculateGoalsConcededPoints($position, $stats);

        // Bonus points
        $totalPoints += $this->calculateBonusPoints($stats);

        return $totalPoints;
    }

    /**
     * Calculate points for playing time
     */
    private function calculatePlayingPoints(int $position, array $stats): int
    {
        $minutes = $stats['minutes'] ?? 0;
        
        if ($minutes == 0) {
            return 0;
        }

        $points = self::POINTS_FOR_PLAYING[$position];

        // Additional points for playing 60+ minutes
        if ($minutes >= 60) {
            $points += self::POINTS_FOR_60_MINUTES[$position];
        }

        return $points;
    }

    /**
     * Calculate points for goals scored
     */
    private function calculateGoalPoints(int $position, array $stats): int
    {
        $goals = $stats['goals_scored'] ?? 0;
        return $goals * self::POINTS_FOR_GOAL[$position];
    }

    /**
     * Calculate points for assists
     */
    private function calculateAssistPoints(array $stats): int
    {
        $assists = $stats['assists'] ?? 0;
        return $assists * self::POINTS_FOR_ASSIST;
    }

    /**
     * Calculate clean sheet points
     */
    private function calculateCleanSheetPoints(int $position, array $stats): int
    {
        $cleanSheets = $stats['clean_sheets'] ?? 0;
        return $cleanSheets * self::POINTS_FOR_CLEAN_SHEET[$position];
    }

    /**
     * Calculate save points (goalkeepers only)
     */
    private function calculateSavePoints(int $position, array $stats): int
    {
        if ($position !== self::GOALKEEPER) {
            return 0;
        }

        $saves = $stats['saves'] ?? 0;
        return intval($saves / 3) * self::POINTS_PER_3_SAVES;
    }

    /**
     * Calculate penalty-related points
     */
    private function calculatePenaltyPoints(array $stats): int
    {
        $points = 0;
        
        // Penalty saves
        $penaltySaves = $stats['penalties_saved'] ?? 0;
        $points += $penaltySaves * self::POINTS_FOR_PENALTY_SAVE;

        // Penalty misses
        $penaltyMisses = $stats['penalties_missed'] ?? 0;
        $points += $penaltyMisses * self::POINTS_FOR_PENALTY_MISS;

        return $points;
    }

    /**
     * Calculate card points
     */
    private function calculateCardPoints(array $stats): int
    {
        $points = 0;
        
        // Yellow cards
        $yellowCards = $stats['yellow_cards'] ?? 0;
        $points += $yellowCards * self::POINTS_FOR_YELLOW_CARD;

        // Red cards
        $redCards = $stats['red_cards'] ?? 0;
        $points += $redCards * self::POINTS_FOR_RED_CARD;

        return $points;
    }

    /**
     * Calculate own goal points
     */
    private function calculateOwnGoalPoints(array $stats): int
    {
        $ownGoals = $stats['own_goals'] ?? 0;
        return $ownGoals * self::POINTS_FOR_OWN_GOAL;
    }

    /**
     * Calculate goals conceded points (GK and DEF only)
     */
    private function calculateGoalsConcededPoints(int $position, array $stats): int
    {
        if ($position !== self::GOALKEEPER && $position !== self::DEFENDER) {
            return 0;
        }

        $goalsConceded = $stats['goals_conceded'] ?? 0;
        return intval($goalsConceded / 2) * self::POINTS_FOR_2_GOALS_CONCEDED;
    }

    /**
     * Calculate bonus points
     */
    private function calculateBonusPoints(array $stats): int
    {
        return $stats['bonus'] ?? 0;
    }

    /**
     * Get position-specific scoring breakdown
     */
    public function getScoringBreakdown(int $position): array
    {
        return [
            'playing' => self::POINTS_FOR_PLAYING[$position],
            '60_minutes' => self::POINTS_FOR_60_MINUTES[$position],
            'goal' => self::POINTS_FOR_GOAL[$position],
            'assist' => self::POINTS_FOR_ASSIST,
            'clean_sheet' => self::POINTS_FOR_CLEAN_SHEET[$position],
            'penalty_save' => $position === self::GOALKEEPER ? self::POINTS_FOR_PENALTY_SAVE : 0,
            'penalty_miss' => self::POINTS_FOR_PENALTY_MISS,
            'yellow_card' => self::POINTS_FOR_YELLOW_CARD,
            'red_card' => self::POINTS_FOR_RED_CARD,
            'own_goal' => self::POINTS_FOR_OWN_GOAL,
            'goals_conceded_2' => in_array($position, [self::GOALKEEPER, self::DEFENDER]) ? self::POINTS_FOR_2_GOALS_CONCEDED : 0,
            'saves_per_3' => $position === self::GOALKEEPER ? self::POINTS_PER_3_SAVES : 0
        ];
    }

    /**
     * Calculate captain points (double points)
     */
    public function calculateCaptainPoints(int $position, array $stats): int
    {
        return $this->calculatePlayerPoints($position, $stats) * 2;
    }

    /**
     * Calculate vice-captain points (used if captain doesn't play)
     */
    public function calculateViceCaptainPoints(int $position, array $stats, bool $captainPlayed): int
    {
        if ($captainPlayed) {
            return $this->calculatePlayerPoints($position, $stats);
        }
        
        return $this->calculateCaptainPoints($position, $stats);
    }

    /**
     * Calculate triple captain points (triple points - rare chip)
     */
    public function calculateTripleCaptainPoints(int $position, array $stats): int
    {
        return $this->calculatePlayerPoints($position, $stats) * 3;
    }

    /**
     * Validate player statistics array
     */
    private function validateStats(array $stats): bool
    {
        $requiredFields = [
            'minutes', 'goals_scored', 'assists', 'clean_sheets',
            'saves', 'penalties_saved', 'penalties_missed',
            'yellow_cards', 'red_cards', 'own_goals', 'goals_conceded'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($stats[$field])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get position name from position ID
     */
    public function getPositionName(int $position): string
    {
        return match($position) {
            self::GOALKEEPER => 'Goalkeeper',
            self::DEFENDER => 'Defender',
            self::MIDFIELDER => 'Midfielder',
            self::FORWARD => 'Forward',
            default => 'Unknown'
        };
    }

    /**
     * Example usage method
     */
    public function exampleCalculation(): array
    {
        // Example stats for a midfielder who played 90 minutes, scored 1 goal, 1 assist
        $midfielderStats = [
            'minutes' => 90,
            'goals_scored' => 1,
            'assists' => 1,
            'clean_sheets' => 1,
            'saves' => 0,
            'penalties_saved' => 0,
            'penalties_missed' => 0,
            'yellow_cards' => 0,
            'red_cards' => 0,
            'own_goals' => 0,
            'goals_conceded' => 0,
            'bonus' => 2
        ];

        return [
            'position' => 'Midfielder',
            'stats' => $midfielderStats,
            'points_breakdown' => [
                'playing' => 2,
                '60_minutes' => 2,
                'goals' => 5,
                'assists' => 3,
                'clean_sheet' => 1,
                'bonus' => 2
            ],
            'total_points' => $this->calculatePlayerPoints(self::MIDFIELDER, $midfielderStats)
        ];
    }
}
