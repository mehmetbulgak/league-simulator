<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\League;

use App\Domain\League\Entities\LeagueMatch;
use App\Domain\League\Entities\Score;
use App\Domain\League\Entities\Team;
use App\Domain\League\Services\StandingsCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StandingsCalculatorTest extends TestCase
{
    #[Test]
    public function it_calculates_points_and_orders_by_points_then_gd_then_goals_for(): void
    {
        $teams = [
            new Team(1, 'Liverpool'),
            new Team(2, 'Manchester City'),
            new Team(3, 'Chelsea'),
            new Team(4, 'Arsenal'),
        ];

        $matches = [
            // Week 1
            new LeagueMatch(1, 1, 2, new Score(2, 0)), // Liverpool win
            new LeagueMatch(1, 3, 4, new Score(1, 1)), // draw
            // Week 2
            new LeagueMatch(2, 1, 3, new Score(0, 1)), // Chelsea win
            new LeagueMatch(2, 2, 4, new Score(3, 3)), // draw
            // Unplayed match (ignored)
            new LeagueMatch(3, 4, 1, null),
        ];

        $calculator = new StandingsCalculator();
        $standings = $calculator->calculate($teams, $matches);

        $this->assertSame([3, 1, 4, 2], array_map(static fn ($s) => $s->teamId, $standings));

        $byId = [];
        foreach ($standings as $standing) {
            $byId[$standing->teamId] = $standing;
        }

        $this->assertSame(4, $byId[3]->points); // Chelsea: W + D
        $this->assertSame(3, $byId[1]->points); // Liverpool: W + L
        $this->assertSame(2, $byId[4]->points); // Arsenal: D + D
        $this->assertSame(1, $byId[2]->points); // Man City: D + L

        $this->assertSame(2, $byId[1]->played);
        $this->assertSame(2, $byId[2]->played);
        $this->assertSame(2, $byId[3]->played);
        $this->assertSame(2, $byId[4]->played);
    }

    #[Test]
    public function it_throws_when_a_match_contains_an_unknown_team_id(): void
    {
        $teams = [
            new Team(1, 'A'),
            new Team(2, 'B'),
        ];

        $matches = [
            new LeagueMatch(1, 1, 999, new Score(1, 0)),
        ];

        $calculator = new StandingsCalculator();

        $this->expectException(InvalidArgumentException::class);
        $calculator->calculate($teams, $matches);
    }
}

