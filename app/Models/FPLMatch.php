<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FPLMatch extends Model
{
    protected $table = 'matches';
    
    protected $fillable = [
        'match_id',
        'gameweek',
        'kickoff_time',
        'home_team',
        'away_team',
        'home_team_elo',
        'away_team_elo',
        'home_score',
        'away_score',
        'finished',
        'tournament',
        'home_possession',
        'away_possession',
        'home_expected_goals_xg',
        'away_expected_goals_xg',
        'home_total_shots',
        'away_total_shots',
        'home_shots_on_target',
        'away_shots_on_target',
        'home_big_chances',
        'away_big_chances',
        'home_big_chances_missed',
        'away_big_chances_missed',
        'home_accurate_passes',
        'away_accurate_passes',
        'home_accurate_passes_pct',
        'away_accurate_passes_pct',
        'home_fouls_committed',
        'away_fouls_committed',
        'home_corners',
        'away_corners',
        'home_xg_open_play',
        'away_xg_open_play',
        'home_xg_set_play',
        'away_xg_set_play',
        'home_yellow_cards',
        'away_yellow_cards',
        'home_red_cards',
        'away_red_cards',
        'home_tackles_won',
        'away_tackles_won',
        'home_interceptions',
        'away_interceptions',
        'home_blocks',
        'away_blocks',
        'home_clearances',
        'away_clearances',
        'home_keeper_saves',
        'away_keeper_saves',
        'stats_processed',
        'player_stats_processed'
    ];

    protected $casts = [
        'kickoff_time' => 'datetime',
        'finished' => 'boolean',
        'stats_processed' => 'boolean',
        'player_stats_processed' => 'boolean',
        'home_possession' => 'decimal:2',
        'away_possession' => 'decimal:2',
        'home_expected_goals_xg' => 'decimal:2',
        'away_expected_goals_xg' => 'decimal:2',
        'home_accurate_passes_pct' => 'decimal:2',
        'away_accurate_passes_pct' => 'decimal:2',
        'home_xg_open_play' => 'decimal:2',
        'away_xg_open_play' => 'decimal:2',
        'home_xg_set_play' => 'decimal:2',
        'away_xg_set_play' => 'decimal:2',
        'home_team_elo' => 'decimal:2',
        'away_team_elo' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team', 'fpl_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team', 'fpl_id');
    }

    public function gameweek(): BelongsTo
    {
        return $this->belongsTo(Gameweek::class, 'gameweek', 'gameweek_id');
    }

    public function playerMatchStats(): HasMany
    {
        return $this->hasMany(PlayerMatchStat::class, 'match_id', 'match_id');
    }

    /**
     * Get match result from perspective of given team
     */
    public function getResultForTeam(int $teamId): string
    {
        if ($this->home_team == $teamId) {
            if ($this->home_score > $this->away_score) return 'W';
            if ($this->home_score == $this->away_score) return 'D';
            return 'L';
        } else {
            if ($this->away_score > $this->home_score) return 'W';
            if ($this->away_score == $this->home_score) return 'D';
            return 'L';
        }
    }

    /**
     * Get goals for given team
     */
    public function getGoalsForTeam(int $teamId): int
    {
        return $this->home_team == $teamId ? $this->home_score : $this->away_score;
    }

    /**
     * Get goals against given team
     */
    public function getGoalsAgainstTeam(int $teamId): int
    {
        return $this->home_team == $teamId ? $this->away_score : $this->home_score;
    }

    /**
     * Check if match was high scoring
     */
    public function isHighScoring(): bool
    {
        return ($this->home_score + $this->away_score) >= 3;
    }

    /**
     * Get match difficulty based on xG difference
     */
    public function getDifficulty(): string
    {
        $xgDiff = abs(($this->home_expected_goals_xg ?? 0) - ($this->away_expected_goals_xg ?? 0));
        
        if ($xgDiff < 0.5) return 'Even';
        if ($xgDiff < 1.0) return 'Moderate';
        if ($xgDiff < 1.5) return 'Difficult';
        return 'Very Difficult';
    }

    /**
     * Scope filters
     */
    public function scopeFinished($query)
    {
        return $query->where('finished', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('finished', false);
    }

    public function scopeByGameweek($query, int $gameweek)
    {
        return $query->where('gameweek', $gameweek);
    }

    public function scopeByTournament($query, string $tournament)
    {
        return $query->where('tournament', $tournament);
    }

    public function scopeForTeam($query, int $teamId)
    {
        return $query->where('home_team', $teamId)
                     ->orWhere('away_team', $teamId);
    }

    public function scopeHighScoring($query)
    {
        return $query->whereRaw('(home_score + away_score) >= 3');
    }

    public function scopeLowScoring($query)
    {
        return $query->whereRaw('(home_score + away_score) <= 1');
    }
}
