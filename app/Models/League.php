<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class League extends Model
{
    protected $fillable = [
        'name',
        'league_code',
        'description',
        'type',
        'privacy',
        'admin_id',
        'max_entries',
        'current_entries',
        'is_active',
        'start_gameweek',
        'end_gameweek'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_gameweek' => 'datetime',
        'end_gameweek' => 'datetime',
        'max_entries' => 'integer',
        'current_entries' => 'integer'
    ];

    /**
     * Generate unique league code
     */
    public static function generateLeagueCode(): string
    {
        do {
            $code = strtoupper(Str::random(6)); // 6-character code like FPL
        } while (self::where('league_code', $code)->exists());

        return $code;
    }

    /**
     * Relationships
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'league_members')
            ->withPivot('joined_at', 'is_admin')
            ->withTimestamps();
    }

    public function leagueMembers(): HasMany
    {
        return $this->hasMany(LeagueMember::class);
    }

    /**
     * Get current leaderboard (ordered by user's total points)
     */
    public function getLeaderboard()
    {
        $pointsService = app(\App\Services\FPLPointsService::class);
        
        // Get all finished gameweeks
        $finishedGameweeks = \App\Models\Gameweek::where('finished', true)
            ->orderBy('gameweek_id')
            ->pluck('gameweek_id');

        // Get all members
        $members = $this->members()
            ->withPivot('joined_at')
            ->get();

        // Calculate total points and gameweeks played for each member
        $leaderboardData = $members->map(function ($user) use ($pointsService, $finishedGameweeks) {
            $totalPoints = 0;
            $gameweeksPlayed = 0;

            // Sum points across all finished gameweeks
            foreach ($finishedGameweeks as $gameweekId) {
                $squadPoints = $pointsService->getSquadPointsForGameweek($user->id, $gameweekId);
                $gwPoints = $squadPoints['total_points'] ?? 0;
                
                if ($gwPoints > 0) {
                    $totalPoints += $gwPoints;
                    $gameweeksPlayed++;
                }
            }

            // Add calculated fields to user object
            $user->points = $totalPoints;
            $user->gameweek = $gameweeksPlayed;
            $user->league_joined_at = $user->pivot->joined_at;

            return $user;
        });

        // Sort by total points (descending), then by join date (ascending)
        $leaderboardData = $leaderboardData->sortBy([
            ['points', 'desc'],
            ['league_joined_at', 'asc']
        ]);

        // Add rank to each member
        $leaderboardData = $leaderboardData->values()->map(function ($user, $index) {
            $user->current_rank = $index + 1;
            return $user;
        });

        return $leaderboardData;
    }

    /**
     * Check if league is full
     */
    public function isFull(): bool
    {
        return $this->current_entries >= $this->max_entries;
    }

    /**
     * Check if user is member
     */
    public function hasMember($userId): bool
    {
        return $this->members()->where('user_id', $userId)->exists();
    }

    /**
     * Add member to league
     */
    public function addMember($userId): bool
    {
        if ($this->isFull() || $this->hasMember($userId)) {
            return false;
        }

        $this->members()->attach($userId, [
            'joined_at' => now(),
            'is_admin' => false
        ]);

        $this->increment('current_entries');
        return true;
    }

    /**
     * Remove member from league
     */
    public function removeMember($userId): bool
    {
        if (!$this->hasMember($userId)) {
            return false;
        }

        $this->members()->detach($userId);
        $this->decrement('current_entries');
        return true;
    }
}
