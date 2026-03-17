<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Game extends Model
{
    protected $fillable = [
        'week',
        'home_team_id',
        'away_team_id',
        'home_goals',
        'away_goals',
        'played_at',
    ];

    protected $casts = [
        'week' => 'integer',
        'home_goals' => 'integer',
        'away_goals' => 'integer',
        'played_at' => 'datetime',
    ];

    /**
     * @param Builder<Game> $query
     * @return Builder<Game>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('week')->orderBy('id');
    }

    /**
     * @param Builder<Game> $query
     * @return Builder<Game>
     */
    public function scopeUnplayed(Builder $query): Builder
    {
        return $query->whereNull('played_at');
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }
}
