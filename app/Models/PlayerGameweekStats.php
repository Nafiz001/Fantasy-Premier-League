<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\FPLScoringService;
use App\Services\FPLBonusPointsService;

class PlayerGameweekStats extends Model
{
    protected $fillable = [
        'player_id',
        'gameweek_id',
        'fixture_id',
        'minutes',
        'goals_scored',
        'assists',
        'clean_sheets',
        'goals_conceded',
        'own_goals',
        'penalties_saved',
        'penalties_missed',
        'yellow_cards',
        'red_cards',
        'saves',
        'bonus',
        'bps',
        'total_points',
        'was_home',
        'round',
        // Extended stats for BPS calculation
        'key_passes',
        'successful_dribbles',
        'tackles',
        'interceptions',
        'clearances',
        'recoveries',
        'blocks',
        'big_chances_created',
        'errors_leading_to_goal',
        'errors_leading_to_attempt',
        'fouls',
        'offsides'
    ];

    protected $casts = [
        'was_home' => 'boolean'
    ];

    /**
     * Relationships
     */
    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function fixture()
    {
        return $this->belongsTo(Fixture::class);
    }

    /**
     * Calculate and update total points for this gameweek performance
     */
    public function calculateTotalPoints(): int
    {
        $scoringService = new FPLScoringService();
        
        $stats = $this->getStatsArray();
        $position = $this->player->element_type;
        
        $totalPoints = $scoringService->calculatePlayerPoints($position, $stats);
        
        $this->update(['total_points' => $totalPoints]);
        
        return $totalPoints;
    }

    /**
     * Calculate and update BPS for this performance
     */
    public function calculateBPS(): int
    {
        $bonusService = new FPLBonusPointsService();
        
        $stats = $this->getStatsArray();
        $position = $this->player->element_type;
        
        $bps = $bonusService->calculateBPS($position, $stats);
        
        $this->update(['bps' => $bps]);
        
        return $bps;
    }

    /**
     * Get stats as array for service calculations
     */
    public function getStatsArray(): array
    {
        return [
            'minutes' => $this->minutes,
            'goals_scored' => $this->goals_scored,
            'assists' => $this->assists,
            'clean_sheets' => $this->clean_sheets,
            'goals_conceded' => $this->goals_conceded,
            'own_goals' => $this->own_goals,
            'penalties_saved' => $this->penalties_saved,
            'penalties_missed' => $this->penalties_missed,
            'yellow_cards' => $this->yellow_cards,
            'red_cards' => $this->red_cards,
            'saves' => $this->saves,
            'bonus' => $this->bonus,
            'key_passes' => $this->key_passes,
            'successful_dribbles' => $this->successful_dribbles,
            'tackles' => $this->tackles,
            'interceptions' => $this->interceptions,
            'clearances' => $this->clearances,
            'recoveries' => $this->recoveries,
            'blocks' => $this->blocks,
            'big_chances_created' => $this->big_chances_created,
            'errors_leading_to_goal' => $this->errors_leading_to_goal,
            'errors_leading_to_attempt' => $this->errors_leading_to_attempt,
            'fouls' => $this->fouls,
            'offsides' => $this->offsides
        ];
    }

    /**
     * Get points breakdown for this performance
     */
    public function getPointsBreakdown(): array
    {
        $scoringService = new FPLScoringService();
        $position = $this->player->element_type;
        $stats = $this->getStatsArray();

        $breakdown = [];

        // Playing time
        if ($this->minutes > 0) {
            $breakdown['playing'] = 2;
            if ($this->minutes >= 60) {
                $breakdown['60_minutes'] = 2;
            }
        }

        // Goals
        if ($this->goals_scored > 0) {
            $pointsPerGoal = $scoringService::POINTS_FOR_GOAL[$position];
            $breakdown['goals'] = $this->goals_scored * $pointsPerGoal;
        }

        // Assists
        if ($this->assists > 0) {
            $breakdown['assists'] = $this->assists * 3;
        }

        // Clean sheet
        if ($this->clean_sheets > 0) {
            $cleanSheetPoints = $scoringService::POINTS_FOR_CLEAN_SHEET[$position];
            if ($cleanSheetPoints > 0) {
                $breakdown['clean_sheet'] = $cleanSheetPoints;
            }
        }

        // Saves (GK only)
        if ($position == 1 && $this->saves > 0) {
            $savePoints = intval($this->saves / 3);
            if ($savePoints > 0) {
                $breakdown['saves'] = $savePoints;
            }
        }

        // Penalties
        if ($this->penalties_saved > 0) {
            $breakdown['penalty_saves'] = $this->penalties_saved * 5;
        }
        if ($this->penalties_missed > 0) {
            $breakdown['penalty_misses'] = $this->penalties_missed * -2;
        }

        // Cards
        if ($this->yellow_cards > 0) {
            $breakdown['yellow_cards'] = $this->yellow_cards * -1;
        }
        if ($this->red_cards > 0) {
            $breakdown['red_cards'] = $this->red_cards * -3;
        }

        // Own goals
        if ($this->own_goals > 0) {
            $breakdown['own_goals'] = $this->own_goals * -2;
        }

        // Goals conceded (GK/DEF)
        if (in_array($position, [1, 2]) && $this->goals_conceded >= 2) {
            $concededPenalty = intval($this->goals_conceded / 2) * -1;
            $breakdown['goals_conceded'] = $concededPenalty;
        }

        // Bonus
        if ($this->bonus > 0) {
            $breakdown['bonus'] = $this->bonus;
        }

        return $breakdown;
    }

    /**
     * Scope for specific gameweek
     */
    public function scopeGameweek($query, int $gameweek)
    {
        return $query->where('gameweek_id', $gameweek);
    }

    /**
     * Scope for specific player
     */
    public function scopeForPlayer($query, int $playerId)
    {
        return $query->where('player_id', $playerId);
    }

    /**
     * Get top performers for a gameweek
     */
    public static function getTopPerformers(int $gameweek, int $limit = 10)
    {
        return self::with(['player', 'player.team'])
            ->gameweek($gameweek)
            ->orderBy('total_points', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get captain recommendations based on points
     */
    public static function getCaptainRecommendations(int $gameweek, int $limit = 5)
    {
        return self::with(['player', 'player.team'])
            ->gameweek($gameweek)
            ->where('total_points', '>', 5) // Minimum threshold
            ->orderBy('total_points', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($stats) {
                return [
                    'player' => $stats->player,
                    'points' => $stats->total_points,
                    'captain_points' => $stats->total_points * 2,
                    'breakdown' => $stats->getPointsBreakdown()
                ];
            });
    }
}
