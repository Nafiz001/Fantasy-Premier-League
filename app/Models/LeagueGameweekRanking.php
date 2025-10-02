<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueGameweekRanking extends Model
{
    protected $fillable = [
        'league_id',
        'user_id',
        'gameweek',
        'points',
        'rank',
        'total_points',
        'overall_rank',
        'played'
    ];

    protected $casts = [
        'gameweek' => 'integer',
        'points' => 'integer',
        'rank' => 'integer',
        'total_points' => 'integer',
        'overall_rank' => 'integer',
        'played' => 'boolean'
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
}
