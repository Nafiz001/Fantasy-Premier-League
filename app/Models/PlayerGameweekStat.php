<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerGameweekStat extends Model
{
    protected $fillable = [
        'player_id', 'gameweek', 'fixture_id', 'minutes', 'goals_scored', 'assists', 'clean_sheets',
        'goals_conceded', 'own_goals', 'penalties_saved', 'penalties_missed', 'yellow_cards',
        'red_cards', 'saves', 'bonus', 'bps', 'total_points', 'was_home', 'round',
        'key_passes', 'successful_dribbles', 'tackles', 'interceptions', 'clearances',
        'recoveries', 'blocks', 'big_chances_created', 'errors_leading_to_goal',
        'errors_leading_to_attempt', 'fouls', 'offsides'
    ];

    protected $casts = [
        'was_home' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id', 'fpl_id');
    }

    public function gameweek(): BelongsTo
    {
        return $this->belongsTo(Gameweek::class, 'gameweek', 'gameweek_id');
    }

    public function fixture(): BelongsTo
    {
        return $this->belongsTo(Fixture::class, 'fixture_id', 'fixture_id');
    }

    /**
     * Calculate CBIT score for gameweek
     */
    public function getCBITScore(): int
    {
        return $this->clearances + $this->blocks + $this->interceptions + $this->tackles;
    }

    /**
     * Get points breakdown
     */
    public function getPointsBreakdown(): array
    {
        $breakdown = [];
        
        // Playing time
        if ($this->minutes > 0) {
            $breakdown['playing'] = 2;
            if ($this->minutes >= 60) {
                $breakdown['60_minutes'] = 2;
            }
        }
        
        // Goals (position specific)
        if ($this->goals_scored > 0) {
            $player = $this->player;
            if ($player) {
                $pointsPerGoal = match($player->element_type) {
                    1, 2 => 6, // GK, DEF
                    3 => 5,    // MID
                    4 => 4,    // FWD
                    default => 4
                };
                $breakdown['goals'] = $this->goals_scored * $pointsPerGoal;
            }
        }
        
        // Assists
        if ($this->assists > 0) {
            $breakdown['assists'] = $this->assists * 3;
        }
        
        // Clean sheet (GK/DEF get 4, MID get 1)
        if ($this->clean_sheets > 0) {
            $player = $this->player;
            if ($player) {
                $cleanSheetPoints = in_array($player->element_type, [1, 2]) ? 4 : 1;
                $breakdown['clean_sheet'] = $cleanSheetPoints;
            }
        }
        
        // Saves (GK only)
        if ($this->saves > 0) {
            $breakdown['saves'] = intval($this->saves / 3);
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
        
        // Goals conceded (GK/DEF only)
        if ($this->goals_conceded >= 2) {
            $player = $this->player;
            if ($player && in_array($player->element_type, [1, 2])) {
                $breakdown['goals_conceded'] = intval($this->goals_conceded / 2) * -1;
            }
        }
        
        // Bonus
        if ($this->bonus > 0) {
            $breakdown['bonus'] = $this->bonus;
        }
        
        return $breakdown;
    }

    /**
     * Check if performance was captain-worthy
     */
    public function isCaptainWorthy(): bool
    {
        return $this->total_points >= 8;
    }

    /**
     * Get performance grade
     */
    public function getPerformanceGrade(): string
    {
        $points = $this->total_points;
        
        if ($points >= 15) return 'A+';
        if ($points >= 12) return 'A';
        if ($points >= 9) return 'B+';
        if ($points >= 6) return 'B';
        if ($points >= 3) return 'C';
        if ($points >= 1) return 'D';
        return 'F';
    }

    /**
     * Scopes
     */
    public function scopeByGameweek($query, int $gameweek)
    {
        return $query->where('gameweek', $gameweek);
    }

    public function scopeTopScorers($query, int $limit = 10)
    {
        return $query->orderBy('total_points', 'desc')->limit($limit);
    }

    public function scopeCaptainCandidates($query, int $threshold = 8)
    {
        return $query->where('total_points', '>=', $threshold);
    }

    public function scopeWithMinutes($query, int $minMinutes = 1)
    {
        return $query->where('minutes', '>=', $minMinutes);
    }

    public function scopeBonusPointsEarned($query)
    {
        return $query->where('bonus', '>', 0);
    }

    public function scopeGoalScorers($query)
    {
        return $query->where('goals_scored', '>', 0);
    }

    public function scopeAssistProviders($query)
    {
        return $query->where('assists', '>', 0);
    }
}
