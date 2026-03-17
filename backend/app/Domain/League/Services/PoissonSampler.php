<?php

declare(strict_types=1);

namespace App\Domain\League\Services;

use InvalidArgumentException;
use Random\Randomizer;

final class PoissonSampler
{
    // 32-bit max for stable float conversion across platforms.
    private const FLOAT_INT_MAX = 2147483647;

    private Randomizer $randomizer;

    public function __construct(?Randomizer $randomizer = null)
    {
        $this->randomizer = $randomizer ?? new Randomizer();
    }

    public function sample(float $lambda): int
    {
        if ($lambda < 0) {
            throw new InvalidArgumentException('Lambda cannot be negative.');
        }

        if ($lambda === 0.0) {
            return 0;
        }

        // Knuth algorithm: https://en.wikipedia.org/wiki/Poisson_distribution#Generating_Poisson-distributed_random_variables
        $l = exp(-$lambda);
        $k = 0;
        $p = 1.0;

        do {
            $k++;
            $p *= $this->nextFloat01();
        } while ($p > $l);

        return $k - 1;
    }

    private function nextFloat01(): float
    {
        $n = $this->randomizer->getInt(0, self::FLOAT_INT_MAX);

        return $n / (self::FLOAT_INT_MAX + 1);
    }
}
