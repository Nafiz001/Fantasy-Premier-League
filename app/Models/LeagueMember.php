<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueMember extends Model
{
    protected $fillable = [
        'league_id',
        'user_id',
        'joined_at',
        'is_admin'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'is_admin' => 'boolean'
    ];

    /**
     * Relationships
     */
    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Update member's points and rank
     */
    public function updatePoints($gameweekPoints, $gameweek): void
    {
        $this->total_points += $gameweekPoints;
        $this->gameweeks_played++;
        $this->save();

        // Update rank within league
        $this->updateRank();
    }

    /**
     * Update member's rank in league
     */
    private function updateRank(): void
    {
        $rank = LeagueMember::where('league_id', $this->league_id)
            ->where('total_points', '>', $this->total_points)
            ->count() + 1;

        $this->rank = $rank;
        $this->save();
    }
}
