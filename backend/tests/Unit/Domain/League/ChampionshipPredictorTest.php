<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\League;

use App\Domain\League\Entities\LeagueMatch;
use App\Domain\League\Entities\Score;
use App\Domain\League\Entities\Team;
use App\Domain\League\Services\ChampionshipPredictor;
use App\Domain\League\Services\MatchSimulator;
use App\Domain\League\Services\PoissonSampler;
use App\Domain\League\Services\StandingsCalculator;
use PHPUnit\Framework\Attributes\Test;
use Random\Engine\Mt19937;
use Random\Randomizer;
use Tests\TestCase;

final class ChampionshipPredictorTest extends TestCase
{
    #[Test]
    public function it_returns_100_percent_when_the_leader_is_mathematically_guaranteed(): void
    {
        $teams = [
            new Team(1, 'A', 90),
            new Team(2, 'B', 70),
            new Team(3, 'C', 70),
            new Team(4, 'D', 70),
        ];

        // Full double round-robin schedule (12 matches). After week 4, each team played 4 matches (8 matches total).
        $matches = [
            // Week 1
            new LeagueMatch(1, 1, 2, new Score(2, 0)),
            new LeagueMatch(1, 3, 4, new Score(1, 0)),
            // Week 2
            new LeagueMatch(2, 1, 3, new Score(1, 0)),
            new LeagueMatch(2, 2, 4, new Score(0, 0)),
            // Week 3
            new LeagueMatch(3, 1, 4, new Score(3, 0)),
            new LeagueMatch(3, 2, 3, new Score(1, 1)),
            // Week 4
            new LeagueMatch(4, 2, 1, new Score(0, 1)),
            new LeagueMatch(4, 4, 3, new Score(2, 2)),
            // Week 5 (unplayed)
            new LeagueMatch(5, 3, 1, null),
            new LeagueMatch(5, 4, 2, null),
            // Week 6 (unplayed)
            new LeagueMatch(6, 4, 1, null),
            new LeagueMatch(6, 3, 2, null),
        ];

        $predictor = $this->makePredictor();
        $percentages = $predictor->predictChampionPercentages($teams, $matches, simulations: 200);

        $this->assertSame(100, $percentages[1]);
        $this->assertSame(0, $percentages[2]);
        $this->assertSame(0, $percentages[3]);
        $this->assertSame(0, $percentages[4]);
    }

    #[Test]
    public function it_returns_100_percent_for_the_champion_when_the_season_is_finished(): void
    {
        $teams = [
            new Team(1, 'A', 50),
            new Team(2, 'B', 50),
        ];

        $matches = [
            new LeagueMatch(1, 1, 2, new Score(0, 1)),
            new LeagueMatch(2, 2, 1, new Score(2, 0)),
        ];

        $predictor = $this->makePredictor();
        $percentages = $predictor->predictChampionPercentages($teams, $matches, simulations: 50);

        $this->assertSame(0, $percentages[1]);
        $this->assertSame(100, $percentages[2]);
    }

    #[Test]
    public function it_runs_monte_carlo_and_sums_to_100(): void
    {
        $teams = [
            new Team(1, 'Strong', 100),
            new Team(2, 'Weak-1', 10),
            new Team(3, 'Weak-2', 10),
            new Team(4, 'Weak-3', 10),
        ];

        // Minimal valid double round-robin for 4 teams (12 unplayed matches).
        $matches = [
            new LeagueMatch(1, 1, 2, null),
            new LeagueMatch(1, 3, 4, null),
            new LeagueMatch(2, 1, 3, null),
            new LeagueMatch(2, 2, 4, null),
            new LeagueMatch(3, 1, 4, null),
            new LeagueMatch(3, 2, 3, null),
            new LeagueMatch(4, 2, 1, null),
            new LeagueMatch(4, 4, 3, null),
            new LeagueMatch(5, 3, 1, null),
            new LeagueMatch(5, 4, 2, null),
            new LeagueMatch(6, 4, 1, null),
            new LeagueMatch(6, 3, 2, null),
        ];

        $predictor = $this->makePredictor(seed: 12345);
        $percentages = $predictor->predictChampionPercentages($teams, $matches, simulations: 500);

        $this->assertSame(100, array_sum($percentages));
        $this->assertGreaterThan($percentages[2], $percentages[1]);
    }

    private function makePredictor(int $seed = 42): ChampionshipPredictor
    {
        $randomizer = new Randomizer(new Mt19937($seed));
        $poissonSampler = new PoissonSampler($randomizer);
        $matchSimulator = new MatchSimulator($poissonSampler);

        return new ChampionshipPredictor(
            matchSimulator: $matchSimulator,
            standingsCalculator: new StandingsCalculator(),
        );
    }
}

