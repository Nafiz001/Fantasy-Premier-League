<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerMatchStat extends Model
{
    protected $fillable = [
        'player_id', 'match_id', 'start_min', 'finish_min', 'minutes_played', 'goals', 'assists',
        'penalties_scored', 'penalties_missed', 'total_shots', 'shots_on_target', 'big_chances_missed',
        'xg', 'xa', 'xgot', 'touches', 'touches_opposition_box', 'accurate_passes', 'accurate_passes_percent',
        'chances_created', 'final_third_passes', 'accurate_crosses', 'accurate_crosses_percent',
        'accurate_long_balls', 'accurate_long_balls_percent', 'successful_dribbles', 'successful_dribbles_percent',
        'dribbled_past', 'tackles', 'tackles_won', 'tackles_won_percent', 'interceptions', 'recoveries',
        'blocks', 'clearances', 'headed_clearances', 'duels_won', 'duels_lost', 'ground_duels_won',
        'ground_duels_won_percent', 'aerial_duels_won', 'aerial_duels_won_percent', 'was_fouled',
        'fouls_committed', 'offsides', 'yellow_cards', 'red_cards', 'saves', 'goals_conceded',
        'team_goals_conceded', 'xgot_faced', 'goals_prevented', 'sweeper_actions', 'high_claim',
        'gk_accurate_passes', 'gk_accurate_long_balls', 'bonus_points', 'bps', 'total_points',
        'clean_sheet', 'own_goals'
    ];

    protected $casts = [
        'clean_sheet' => 'boolean',
        'xg' => 'decimal:2',
        'xa' => 'decimal:2',
        'xgot' => 'decimal:2',
        'xgot_faced' => 'decimal:2',
        'accurate_passes_percent' => 'decimal:2',
        'accurate_crosses_percent' => 'decimal:2',
        'accurate_long_balls_percent' => 'decimal:2',
        'successful_dribbles_percent' => 'decimal:2',
        'tackles_won_percent' => 'decimal:2',
        'ground_duels_won_percent' => 'decimal:2',
        'aerial_duels_won_percent' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id', 'fpl_id');
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(FPLMatch::class, 'match_id', 'match_id');
    }

    /**
     * Calculate CBIT score (Clearances, Blocks, Interceptions, Tackles)
     */
    public function getCBITScore(): int
    {
        return $this->clearances + $this->blocks + $this->interceptions + $this->tackles_won;
    }

    /**
     * Get performance rating for this match
     */
    public function getMatchRating(): string
    {
        $points = $this->total_points;
        
        if ($points >= 15) return 'Exceptional';
        if ($points >= 10) return 'Excellent';
        if ($points >= 6) return 'Good';
        if ($points >= 3) return 'Average';
        if ($points >= 1) return 'Poor';
        return 'Very Poor';
    }

    /**
     * Check if player had a standout performance
     */
    public function isStandoutPerformance(): bool
    {
        return $this->total_points >= 10 || 
               $this->goals >= 2 || 
               $this->assists >= 2 || 
               $this->bonus_points >= 3;
    }

    /**
     * Get key performance indicators
     */
    public function getKPIs(): array
    {
        $kpis = [];
        
        // Goals and assists
        if ($this->goals > 0) $kpis['goals'] = $this->goals;
        if ($this->assists > 0) $kpis['assists'] = $this->assists;
        
        // Defensive actions
        $cbit = $this->getCBITScore();
        if ($cbit >= 5) $kpis['defensive_actions'] = $cbit;
        
        // Goalkeeper specific
        if ($this->saves >= 3) $kpis['saves'] = $this->saves;
        if ($this->clean_sheet) $kpis['clean_sheet'] = true;
        
        // Creative actions
        if ($this->chances_created >= 3) $kpis['chances_created'] = $this->chances_created;
        if ($this->successful_dribbles >= 3) $kpis['dribbles'] = $this->successful_dribbles;
        
        // Bonus points
        if ($this->bonus_points > 0) $kpis['bonus'] = $this->bonus_points;
        
        return $kpis;
    }

    /**
     * Scopes
     */
    public function scopeWithGoals($query)
    {
        return $query->where('goals', '>', 0);
    }

    public function scopeWithAssists($query)
    {
        return $query->where('assists', '>', 0);
    }

    public function scopeWithBonusPoints($query)
    {
        return $query->where('bonus_points', '>', 0);
    }

    public function scopeHighPoints($query, $threshold = 10)
    {
        return $query->where('total_points', '>=', $threshold);
    }

    public function scopeCleanSheets($query)
    {
        return $query->where('clean_sheet', true);
    }

    public function scopeByPosition($query, int $elementType)
    {
        return $query->whereHas('player', function($q) use ($elementType) {
            $q->where('element_type', $elementType);
        });
    }
}
