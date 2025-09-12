<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fixture extends Model
{
    protected $fillable = [
        'fixture_id',
        'gameweek',
        'kickoff_time',
        'home_team',
        'away_team',
        'home_team_elo',
        'away_team_elo',
        'home_score',
        'away_score',
        'finished',
        'tournament'
    ];

    protected $casts = [
        'kickoff_time' => 'datetime',
        'finished' => 'boolean',
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

    /**
     * Get fixture difficulty rating based on team strengths
     */
    public function getDifficulty(int $forTeamId): string
    {
        $opponentId = $forTeamId == $this->home_team ? $this->away_team : $this->home_team;
        $isHome = $forTeamId == $this->home_team;
        
        // Get opponent team
        $opponent = Team::where('fpl_id', $opponentId)->first();
        
        if (!$opponent) return 'Unknown';
        
        // Use strength ratings to determine difficulty
        $opponentStrength = $isHome ? $opponent->strength_overall_away : $opponent->strength_overall_home;
        
        if ($opponentStrength <= 2) return 'Very Easy';
        if ($opponentStrength <= 3) return 'Easy';
        if ($opponentStrength <= 3.5) return 'Moderate';
        if ($opponentStrength <= 4) return 'Hard';
        return 'Very Hard';
    }

    /**
     * Get predicted score based on xG or Elo
     */
    public function getPredictedScore(): array
    {
        // Simple prediction based on Elo difference
        if ($this->home_team_elo && $this->away_team_elo) {
            $eloDiff = $this->home_team_elo - $this->away_team_elo;
            
            // Home advantage
            $homeAdvantage = 50;
            $adjustedEloDiff = $eloDiff + $homeAdvantage;
            
            if ($adjustedEloDiff > 100) {
                return ['home' => 2, 'away' => 0];
            } elseif ($adjustedEloDiff > 50) {
                return ['home' => 2, 'away' => 1];
            } elseif ($adjustedEloDiff > 0) {
                return ['home' => 1, 'away' => 1];
            } elseif ($adjustedEloDiff > -50) {
                return ['home' => 1, 'away' => 1];
            } elseif ($adjustedEloDiff > -100) {
                return ['home' => 1, 'away' => 2];
            } else {
                return ['home' => 0, 'away' => 2];
            }
        }
        
        return ['home' => 1, 'away' => 1]; // Default draw prediction
    }

    /**
     * Get fixture analysis
     */
    public function getAnalysis(): array
    {
        $homeTeam = $this->homeTeam;
        $awayTeam = $this->awayTeam;
        
        if (!$homeTeam || !$awayTeam) {
            return [];
        }

        $homeStats = $homeTeam->getAverageStats();
        $awayStats = $awayTeam->getAverageStats();
        
        return [
            'home_form' => $homeTeam->getForm(),
            'away_form' => $awayTeam->getForm(),
            'home_avg_goals' => $homeStats['avg_goals_scored'] ?? 0,
            'away_avg_goals' => $awayStats['avg_goals_scored'] ?? 0,
            'home_avg_conceded' => $homeStats['avg_goals_conceded'] ?? 0,
            'away_avg_conceded' => $awayStats['avg_goals_conceded'] ?? 0,
            'predicted_score' => $this->getPredictedScore(),
            'home_difficulty' => $this->getDifficulty($this->home_team),
            'away_difficulty' => $this->getDifficulty($this->away_team)
        ];
    }

    /**
     * Scopes
     */
    public function scopeUpcoming($query)
    {
        return $query->where('finished', false)
                     ->where('kickoff_time', '>', now());
    }

    public function scopeFinished($query)
    {
        return $query->where('finished', true);
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

    public function scopeToday($query)
    {
        return $query->whereDate('kickoff_time', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('kickoff_time', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }
}
