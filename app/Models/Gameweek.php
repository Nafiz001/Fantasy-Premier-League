<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gameweek extends Model
{
    protected $fillable = [
        'gameweek_id',
        'name',
        'deadline_time',
        'deadline_time_epoch',
        'deadline_time_game_offset',
        'average_entry_score',
        'highest_score',
        'finished',
        'is_previous',
        'is_current',
        'is_next',
        'chip_plays',
        'most_selected',
        'most_transferred_in',
        'most_captained',
        'most_vice_captained',
        'top_element',
        'top_element_info',
        'transfers_made'
    ];

    protected $casts = [
        'deadline_time' => 'datetime',
        'deadline_time_epoch' => 'integer',
        'finished' => 'boolean',
        'is_previous' => 'boolean',
        'is_current' => 'boolean',
        'is_next' => 'boolean',
        'chip_plays' => 'array',
        'top_element_info' => 'array',
        'average_entry_score' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function matches(): HasMany
    {
        return $this->hasMany(FPLMatch::class, 'gameweek', 'gameweek_id');
    }

    public function fixtures(): HasMany
    {
        return $this->hasMany(Fixture::class, 'gameweek', 'gameweek_id');
    }

    public function playerStats(): HasMany
    {
        return $this->hasMany(PlayerStat::class, 'gameweek', 'gameweek_id');
    }

    public function playerGameweekStats(): HasMany
    {
        return $this->hasMany(PlayerGameweekStat::class, 'gameweek', 'gameweek_id');
    }

    /**
     * Get top element player
     */
    public function topElementPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'top_element', 'fpl_id');
    }

    /**
     * Get most selected player
     */
    public function mostSelectedPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'most_selected', 'fpl_id');
    }

    /**
     * Get most captained player
     */
    public function mostCaptainedPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'most_captained', 'fpl_id');
    }

    /**
     * Check if gameweek is active
     */
    public function isActive(): bool
    {
        return $this->is_current || (!$this->finished && $this->deadline_time->isPast());
    }

    /**
     * Get gameweek summary statistics
     */
    public function getSummaryStats(): array
    {
        $matches = $this->matches()->finished()->get();
        
        if ($matches->isEmpty()) {
            return [];
        }

        $stats = [
            'total_matches' => $matches->count(),
            'total_goals' => 0,
            'avg_goals_per_match' => 0,
            'highest_scoring_match' => null,
            'clean_sheets' => 0,
            'total_cards' => 0,
            'avg_possession_difference' => 0
        ];

        $totalGoals = 0;
        $highestGoals = 0;
        $highestScoringMatch = null;
        $totalCards = 0;
        $possessionDiffs = [];

        foreach ($matches as $match) {
            $matchGoals = $match->home_score + $match->away_score;
            $totalGoals += $matchGoals;

            if ($matchGoals > $highestGoals) {
                $highestGoals = $matchGoals;
                $highestScoringMatch = $match;
            }

            // Clean sheets
            if ($match->home_score == 0) $stats['clean_sheets']++;
            if ($match->away_score == 0) $stats['clean_sheets']++;

            // Cards
            $totalCards += ($match->home_yellow_cards ?? 0) + ($match->away_yellow_cards ?? 0);
            $totalCards += (($match->home_red_cards ?? 0) + ($match->away_red_cards ?? 0)) * 3; // Weight red cards

            // Possession difference
            if ($match->home_possession && $match->away_possession) {
                $possessionDiffs[] = abs($match->home_possession - $match->away_possession);
            }
        }

        $stats['total_goals'] = $totalGoals;
        $stats['avg_goals_per_match'] = round($totalGoals / $matches->count(), 2);
        $stats['highest_scoring_match'] = $highestScoringMatch;
        $stats['total_cards'] = $totalCards;
        
        if (!empty($possessionDiffs)) {
            $stats['avg_possession_difference'] = round(array_sum($possessionDiffs) / count($possessionDiffs), 1);
        }

        return $stats;
    }

    /**
     * Scopes
     */
    public function scopeFinished($query)
    {
        return $query->where('finished', true);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeNext($query)
    {
        return $query->where('is_next', true);
    }

    public function scopePrevious($query)
    {
        return $query->where('is_previous', true);
    }
}
