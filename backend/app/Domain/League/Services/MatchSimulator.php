<?php

declare(strict_types=1);

namespace App\Domain\League\Services;

use App\Domain\League\Entities\Score;
use App\Domain\League\Entities\Team;

final class MatchSimulator
{
    private const MIN_LAMBDA = 0.2;

    public function __construct(
        private readonly PoissonSampler $poissonSampler,
    ) {
    }

    public function simulate(Team $homeTeam, Team $awayTeam): Score
    {
        [$homeLambda, $awayLambda] = $this->expectedGoals($homeTeam, $awayTeam);

        $homeGoals = $this->poissonSampler->sample($homeLambda);
        $awayGoals = $this->poissonSampler->sample($awayLambda);

        return new Score($homeGoals, $awayGoals);
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function expectedGoals(Team $homeTeam, Team $awayTeam): array
    {
        $homeAdvantage = 0.25;
        $baseHome = 1.35;
        $baseAway = 1.10;

        $powerDiff = $homeTeam->power - $awayTeam->power;
        $powerFactor = $powerDiff / 40.0;

        $homeLambda = max(self::MIN_LAMBDA, $baseHome + $homeAdvantage + $powerFactor);
        $awayLambda = max(self::MIN_LAMBDA, $baseAway - $powerFactor);

        return [$homeLambda, $awayLambda];
    }
}
