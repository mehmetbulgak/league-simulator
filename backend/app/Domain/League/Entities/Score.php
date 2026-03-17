<?php

declare(strict_types=1);

namespace App\Domain\League\Entities;

use InvalidArgumentException;

final readonly class Score
{
    public int $homeGoals;
    public int $awayGoals;

    public function __construct(int $homeGoals, int $awayGoals)
    {
        if ($homeGoals < 0 || $awayGoals < 0) {
            throw new InvalidArgumentException('Goals cannot be negative.');
        }

        $this->homeGoals = $homeGoals;
        $this->awayGoals = $awayGoals;
    }
}

