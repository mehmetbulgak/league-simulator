<?php

declare(strict_types=1);

namespace App\Domain\League\Entities;

use InvalidArgumentException;

final readonly class LeagueMatch
{
    public int $week;
    public int $homeTeamId;
    public int $awayTeamId;
    public ?Score $score;

    public function __construct(int $week, int $homeTeamId, int $awayTeamId, ?Score $score = null)
    {
        if ($week <= 0) {
            throw new InvalidArgumentException('Week must be a positive integer.');
        }

        if ($homeTeamId <= 0 || $awayTeamId <= 0) {
            throw new InvalidArgumentException('Team ids must be positive integers.');
        }

        if ($homeTeamId === $awayTeamId) {
            throw new InvalidArgumentException('A team cannot play against itself.');
        }

        $this->week = $week;
        $this->homeTeamId = $homeTeamId;
        $this->awayTeamId = $awayTeamId;
        $this->score = $score;
    }

    public function isPlayed(): bool
    {
        return $this->score !== null;
    }
}

