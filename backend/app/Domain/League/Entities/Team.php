<?php

declare(strict_types=1);

namespace App\Domain\League\Entities;

use InvalidArgumentException;

final readonly class Team
{
    public int $id;
    public string $name;
    public int $power;

    public function __construct(int $id, string $name, int $power = 0)
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('Team id must be a positive integer.');
        }

        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Team name cannot be empty.');
        }

        if ($power < 0) {
            throw new InvalidArgumentException('Team power cannot be negative.');
        }

        $this->id = $id;
        $this->name = $name;
        $this->power = $power;
    }
}

