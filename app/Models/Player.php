<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    protected $fillable = [
        'fpl_code',
        'fpl_id',
        'first_name',
        'second_name',
        'web_name',
        'team_code',
        'position',
        'element_type'
    ];

    protected $casts = [
        'fpl_code' => 'integer',
        'fpl_id' => 'integer',
        'team_code' => 'integer',
        'element_type' => 'integer',
    ];

    /**
     * Relationships
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_code', 'fpl_code');
    }

    public function playerStats(): HasMany
    {
        return $this->hasMany(PlayerStat::class, 'player_id', 'fpl_id');
    }

    public function matchStats(): HasMany
    {
        return $this->hasMany(PlayerMatchStat::class, 'player_id', 'fpl_id');
    }

    public function gameweekStats(): HasMany
    {
        return $this->hasMany(PlayerGameweekStat::class, 'player_id', 'fpl_id');
    }

    /**
     * Get current season stats
     */
    public function getCurrentStats()
    {
        return $this->playerStats()
            ->latest()
            ->first();
    }

    /**
     * Get stats for specific gameweek
     */
    public function getGameweekStats(int $gameweek)
    {
        return $this->gameweekStats()
            ->where('gameweek', $gameweek)
            ->first();
    }

    /**
     * Get position name
     */
    public function getPositionNameAttribute(): string
    {
        return match($this->element_type) {
            1 => 'Goalkeeper',
            2 => 'Defender', 
            3 => 'Midfielder',
            4 => 'Forward',
            default => 'Unknown'
        };
    }

    /**
     * Get form over last n gameweeks
     */
    public function getForm(int $gameweeks = 5)
    {
        return $this->gameweekStats()
            ->orderBy('gameweek', 'desc')
            ->limit($gameweeks)
            ->get()
            ->sum('total_points');
    }

    /**
     * Scope filters
     */
    public function scopeByPosition($query, int $elementType)
    {
        return $query->where('element_type', $elementType);
    }

    public function scopeByTeam($query, int $teamCode)
    {
        return $query->where('team_code', $teamCode);
    }

    public function scopeGoalkeepers($query)
    {
        return $query->where('element_type', 1);
    }

    public function scopeDefenders($query)
    {
        return $query->where('element_type', 2);
    }

    public function scopeMidfielders($query)
    {
        return $query->where('element_type', 3);
    }

    public function scopeForwards($query)
    {
        return $query->where('element_type', 4);
    }
}
