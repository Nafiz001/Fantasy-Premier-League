<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'fpl_code',
        'fpl_id',
        'name',
        'short_name',
        'strength',
        'strength_overall_home',
        'strength_overall_away',
        'strength_attack_home',
        'strength_attack_away',
        'strength_defence_home',
        'strength_defence_away',
        'pulse_id',
        'elo'
    ];

    protected $casts = [
        'elo' => 'decimal:2',
        'strength' => 'integer',
        'strength_overall_home' => 'integer',
        'strength_overall_away' => 'integer',
        'strength_attack_home' => 'integer',
        'strength_attack_away' => 'integer',
        'strength_defence_home' => 'integer',
        'strength_defence_away' => 'integer',
    ];

    /**
     * Relationships
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class, 'team_code', 'fpl_code');
    }

    public function homeMatches(): HasMany
    {
        return $this->hasMany(FPLMatch::class, 'home_team', 'fpl_id');
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(FPLMatch::class, 'away_team', 'fpl_id');
    }

    public function homeFixtures(): HasMany
    {
        return $this->hasMany(Fixture::class, 'home_team', 'fpl_id');
    }

    public function awayFixtures(): HasMany
    {
        return $this->hasMany(Fixture::class, 'away_team', 'fpl_id');
    }

    /**
     * Get all matches for this team (home and away)
     */
    public function allMatches()
    {
        return FPLMatch::where('home_team', $this->fpl_id)
            ->orWhere('away_team', $this->fpl_id);
    }

    /**
     * Get team's form (last 5 matches)
     */
    public function getForm(int $limit = 5)
    {
        return $this->allMatches()
            ->where('finished', true)
            ->orderBy('kickoff_time', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($match) {
                if ($match->home_team == $this->fpl_id) {
                    // Home match
                    if ($match->home_score > $match->away_score) return 'W';
                    if ($match->home_score == $match->away_score) return 'D';
                    return 'L';
                } else {
                    // Away match
                    if ($match->away_score > $match->home_score) return 'W';
                    if ($match->away_score == $match->home_score) return 'D';
                    return 'L';
                }
            });
    }

    /**
     * Calculate team's average performance metrics
     */
    public function getAverageStats()
    {
        $matches = $this->allMatches()->where('finished', true)->get();
        
        if ($matches->isEmpty()) {
            return [];
        }
        
        $stats = [
            'goals_scored' => 0,
            'goals_conceded' => 0,
            'xg_for' => 0,
            'xg_against' => 0,
            'shots_for' => 0,
            'shots_against' => 0,
            'possession' => 0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0
        ];
        
        foreach ($matches as $match) {
            $isHome = $match->home_team == $this->fpl_id;
            
            if ($isHome) {
                $stats['goals_scored'] += $match->home_score;
                $stats['goals_conceded'] += $match->away_score;
                $stats['xg_for'] += $match->home_expected_goals_xg ?? 0;
                $stats['xg_against'] += $match->away_expected_goals_xg ?? 0;
                $stats['shots_for'] += $match->home_total_shots ?? 0;
                $stats['shots_against'] += $match->away_total_shots ?? 0;
                $stats['possession'] += $match->home_possession ?? 50;
                
                if ($match->home_score > $match->away_score) $stats['wins']++;
                elseif ($match->home_score == $match->away_score) $stats['draws']++;
                else $stats['losses']++;
            } else {
                $stats['goals_scored'] += $match->away_score;
                $stats['goals_conceded'] += $match->home_score;
                $stats['xg_for'] += $match->away_expected_goals_xg ?? 0;
                $stats['xg_against'] += $match->home_expected_goals_xg ?? 0;
                $stats['shots_for'] += $match->away_total_shots ?? 0;
                $stats['shots_against'] += $match->home_total_shots ?? 0;
                $stats['possession'] += $match->away_possession ?? 50;
                
                if ($match->away_score > $match->home_score) $stats['wins']++;
                elseif ($match->away_score == $match->home_score) $stats['draws']++;
                else $stats['losses']++;
            }
        }
        
        $matchCount = $matches->count();
        
        return [
            'matches_played' => $matchCount,
            'avg_goals_scored' => round($stats['goals_scored'] / $matchCount, 2),
            'avg_goals_conceded' => round($stats['goals_conceded'] / $matchCount, 2),
            'avg_xg_for' => round($stats['xg_for'] / $matchCount, 2),
            'avg_xg_against' => round($stats['xg_against'] / $matchCount, 2),
            'avg_shots_for' => round($stats['shots_for'] / $matchCount, 1),
            'avg_shots_against' => round($stats['shots_against'] / $matchCount, 1),
            'avg_possession' => round($stats['possession'] / $matchCount, 1),
            'wins' => $stats['wins'],
            'draws' => $stats['draws'],
            'losses' => $stats['losses'],
            'win_percentage' => round(($stats['wins'] / $matchCount) * 100, 1)
        ];
    }

    /**
     * Get upcoming fixtures
     */
    public function getUpcomingFixtures(int $limit = 5)
    {
        return Fixture::where(function ($query) {
                $query->where('home_team', $this->fpl_id)
                      ->orWhere('away_team', $this->fpl_id);
            })
            ->where('finished', false)
            ->orderBy('kickoff_time')
            ->limit($limit)
            ->get();
    }

    /**
     * Scope for filtering by strength
     */
    public function scopeByStrength($query, $operator = '>=', $value = 3)
    {
        return $query->where('strength', $operator, $value);
    }
}
