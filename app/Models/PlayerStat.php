<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerStat extends Model
{
    protected $fillable = [
        'player_id', 'gameweek', 'first_name', 'second_name', 'web_name', 'status', 'news', 'news_added',
        'chance_of_playing_next_round', 'chance_of_playing_this_round', 'now_cost', 'now_cost_rank', 
        'now_cost_rank_type', 'cost_change_event', 'cost_change_event_fall', 'cost_change_start', 
        'cost_change_start_fall', 'selected_by_percent', 'selected_rank', 'selected_rank_type',
        'total_points', 'event_points', 'points_per_game', 'points_per_game_rank', 'points_per_game_rank_type',
        'minutes', 'goals_scored', 'assists', 'clean_sheets', 'goals_conceded', 'own_goals',
        'penalties_saved', 'penalties_missed', 'yellow_cards', 'red_cards', 'saves', 'starts', 'bonus', 'bps',
        'form', 'form_rank', 'form_rank_type', 'value_form', 'value_season', 'dreamteam_count',
        'transfers_in', 'transfers_in_event', 'transfers_out', 'transfers_out_event',
        'ep_next', 'ep_this', 'expected_goals', 'expected_assists', 'expected_goal_involvements', 'expected_goals_conceded',
        'expected_goals_per_90', 'expected_assists_per_90', 'expected_goal_involvements_per_90', 'expected_goals_conceded_per_90',
        'influence', 'creativity', 'threat', 'ict_index', 'influence_rank', 'influence_rank_type',
        'creativity_rank', 'creativity_rank_type', 'threat_rank', 'threat_rank_type',
        'ict_index_rank', 'ict_index_rank_type', 'corners_and_indirect_freekicks_order',
        'direct_freekicks_order', 'penalties_order', 'corners_and_indirect_freekicks_text',
        'direct_freekicks_text', 'penalties_text', 'defensive_contribution', 'defensive_contribution_per_90',
        'saves_per_90', 'clean_sheets_per_90', 'goals_conceded_per_90', 'starts_per_90'
    ];

    protected $casts = [
        'news_added' => 'datetime',
        'selected_by_percent' => 'decimal:2',
        'points_per_game' => 'decimal:2',
        'form' => 'decimal:1',
        'value_form' => 'decimal:2',
        'value_season' => 'decimal:2',
        'ep_next' => 'decimal:2',
        'ep_this' => 'decimal:2',
        'expected_goals' => 'decimal:2',
        'expected_assists' => 'decimal:2',
        'expected_goal_involvements' => 'decimal:2',
        'expected_goals_conceded' => 'decimal:2',
        'expected_goals_per_90' => 'decimal:2',
        'expected_assists_per_90' => 'decimal:2',
        'expected_goal_involvements_per_90' => 'decimal:2',
        'expected_goals_conceded_per_90' => 'decimal:2',
        'influence' => 'decimal:1',
        'creativity' => 'decimal:1',
        'threat' => 'decimal:1',
        'ict_index' => 'decimal:1',
        'defensive_contribution' => 'decimal:2',
        'defensive_contribution_per_90' => 'decimal:2',
        'saves_per_90' => 'decimal:2',
        'clean_sheets_per_90' => 'decimal:2',
        'goals_conceded_per_90' => 'decimal:2',
        'starts_per_90' => 'decimal:2',
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

    /**
     * Get value rating
     */
    public function getValueRating(): string
    {
        $ppg = $this->points_per_game;
        $cost = $this->now_cost / 10; // Convert to millions
        
        if ($cost == 0) return 'N/A';
        
        $valueRatio = $ppg / $cost;
        
        if ($valueRatio >= 1.5) return 'Excellent';
        if ($valueRatio >= 1.0) return 'Good';
        if ($valueRatio >= 0.7) return 'Fair';
        if ($valueRatio >= 0.5) return 'Poor';
        return 'Very Poor';
    }

    /**
     * Check if player is in good form
     */
    public function isInGoodForm(): bool
    {
        return $this->form >= 6.0;
    }

    /**
     * Check if player is injury concern
     */
    public function hasInjuryConcern(): bool
    {
        return in_array($this->status, ['i', 'd']) || 
               $this->chance_of_playing_next_round < 75;
    }

    /**
     * Get position-specific performance rating
     */
    public function getPerformanceRating(): array
    {
        $player = $this->player;
        if (!$player) return [];
        
        switch ($player->element_type) {
            case 1: // Goalkeeper
                return [
                    'saves_rating' => $this->saves >= 50 ? 'Excellent' : ($this->saves >= 30 ? 'Good' : 'Average'),
                    'clean_sheets_rating' => $this->clean_sheets >= 10 ? 'Excellent' : ($this->clean_sheets >= 6 ? 'Good' : 'Average'),
                    'bonus_rating' => $this->bonus >= 20 ? 'Excellent' : ($this->bonus >= 10 ? 'Good' : 'Average'),
                ];
                
            case 2: // Defender
                return [
                    'clean_sheets_rating' => $this->clean_sheets >= 12 ? 'Excellent' : ($this->clean_sheets >= 8 ? 'Good' : 'Average'),
                    'goals_rating' => $this->goals_scored >= 3 ? 'Excellent' : ($this->goals_scored >= 1 ? 'Good' : 'Average'),
                    'assists_rating' => $this->assists >= 5 ? 'Excellent' : ($this->assists >= 2 ? 'Good' : 'Average'),
                ];
                
            case 3: // Midfielder
                return [
                    'goals_rating' => $this->goals_scored >= 8 ? 'Excellent' : ($this->goals_scored >= 4 ? 'Good' : 'Average'),
                    'assists_rating' => $this->assists >= 8 ? 'Excellent' : ($this->assists >= 4 ? 'Good' : 'Average'),
                    'creativity_rating' => $this->creativity >= 100 ? 'Excellent' : ($this->creativity >= 50 ? 'Good' : 'Average'),
                ];
                
            case 4: // Forward
                return [
                    'goals_rating' => $this->goals_scored >= 15 ? 'Excellent' : ($this->goals_scored >= 8 ? 'Good' : 'Average'),
                    'threat_rating' => $this->threat >= 150 ? 'Excellent' : ($this->threat >= 100 ? 'Good' : 'Average'),
                    'xg_rating' => $this->expected_goals >= 10 ? 'Excellent' : ($this->expected_goals >= 5 ? 'Good' : 'Average'),
                ];
        }
        
        return [];
    }

    /**
     * Scopes
     */
    public function scopeByPosition($query, int $elementType)
    {
        return $query->whereHas('player', function($q) use ($elementType) {
            $q->where('element_type', $elementType);
        });
    }

    public function scopeTopScorers($query, int $limit = 10)
    {
        return $query->orderBy('total_points', 'desc')->limit($limit);
    }

    public function scopeBestValue($query, int $limit = 10)
    {
        return $query->where('now_cost', '>', 0)
                     ->orderByRaw('(total_points / (now_cost / 10.0)) DESC')
                     ->limit($limit);
    }

    public function scopeInForm($query)
    {
        return $query->where('form', '>=', 6.0);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'a')
                     ->where('chance_of_playing_next_round', '>=', 75);
    }
}
