<?php

declare(strict_types=1);

namespace App\Application\League;

use App\Application\League\Exceptions\TeamPowerLockedException;
use App\Models\Game;
use App\Models\Team;

final class TeamPowerService
{
    /**
     * @return array{id: int, name: string, power: int}
     */
    public function updatePower(Team $team, int $power): array
    {
        if (Game::query()->exists()) {
            throw new TeamPowerLockedException();
        }

        $team->power = $power;
        $team->save();

        return [
            'id' => (int) $team->id,
            'name' => (string) $team->name,
            'power' => (int) $team->power,
        ];
    }
}
