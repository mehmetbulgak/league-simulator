<?php

declare(strict_types=1);

namespace App\Application\League;

use App\Models\Game;
use Illuminate\Support\Facades\DB;

final class MatchResultService
{
    public function __construct(
        private readonly SimulationService $simulation,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function update(Game $game, int $homeGoals, int $awayGoals): array
    {
        DB::transaction(static function () use ($game, $homeGoals, $awayGoals) {
            $game->home_goals = $homeGoals;
            $game->away_goals = $awayGoals;
            $game->played_at = $game->played_at ?? now();
            $game->save();
        });

        return $this->simulation->getState();
    }
}

