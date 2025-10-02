<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'has_selected_squad',
        'team_name',
        'budget_remaining',
        'points',
        'free_transfers',
        'active_chip',
        'used_chips',
        'starting_xi',
        'captain_id',
        'vice_captain_id',
        'formation',
        'gameweek',
        'squad_completed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'used_chips' => 'array',
            'starting_xi' => 'array',
            'squad_completed' => 'boolean',
        ];
    }

    /**
     * League relationships
     */
    public function leagues()
    {
        return $this->belongsToMany(League::class, 'league_members')
            ->withPivot('joined_at', 'is_admin')
            ->withTimestamps();
    }

    public function adminLeagues()
    {
        return $this->hasMany(League::class, 'admin_id');
    }

    public function leagueMembers()
    {
        return $this->hasMany(LeagueMember::class);
    }
}
