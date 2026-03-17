<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\League;

use App\Domain\League\Entities\Team;
use App\Domain\League\Services\FixtureGenerator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FixtureGeneratorTest extends TestCase
{
    #[Test]
    public function it_generates_a_double_round_robin_for_four_teams(): void
    {
        $teams = [
            new Team(1, 'Liverpool'),
            new Team(2, 'Manchester City'),
            new Team(3, 'Chelsea'),
            new Team(4, 'Arsenal'),
        ];

        $generator = new FixtureGenerator();
        $weeks = $generator->generateDoubleRoundRobin($teams);

        $this->assertCount(6, $weeks, 'Expected 6 weeks for 4 teams in a double round-robin.');

        $matchCount = 0;
        $pairCounts = [];
        $orientedPairCounts = [];

        foreach ($weeks as $weekNumber => $matches) {
            $this->assertSame($weekNumber, (int) $weekNumber);
            $this->assertCount(2, $matches, "Week {$weekNumber} should have 2 matches.");

            $usedTeamIds = [];
            foreach ($matches as $match) {
                $matchCount++;

                $this->assertSame($weekNumber, $match->week);
                $this->assertNotSame($match->homeTeamId, $match->awayTeamId);

                $usedTeamIds[] = $match->homeTeamId;
                $usedTeamIds[] = $match->awayTeamId;

                $min = min($match->homeTeamId, $match->awayTeamId);
                $max = max($match->homeTeamId, $match->awayTeamId);
                $pairKey = "{$min}-{$max}";
                $pairCounts[$pairKey] = ($pairCounts[$pairKey] ?? 0) + 1;

                $orientedKey = "{$match->homeTeamId}-{$match->awayTeamId}";
                $orientedPairCounts[$orientedKey] = ($orientedPairCounts[$orientedKey] ?? 0) + 1;
            }

            sort($usedTeamIds);
            $this->assertSame([1, 2, 3, 4], $usedTeamIds, "Week {$weekNumber} should contain each team exactly once.");
        }

        $this->assertSame(12, $matchCount, 'Expected 12 matches for 4 teams in a double round-robin.');

        // Each unordered pair should occur exactly twice.
        $this->assertCount(6, $pairCounts, 'Expected 6 unique pairings for 4 teams.');
        foreach ($pairCounts as $pairKey => $count) {
            $this->assertSame(2, $count, "Pair {$pairKey} should be played twice.");

            [$a, $b] = array_map('intval', explode('-', $pairKey));
            $this->assertSame(1, $orientedPairCounts["{$a}-{$b}"] ?? 0);
            $this->assertSame(1, $orientedPairCounts["{$b}-{$a}"] ?? 0);
        }
    }

    #[Test]
    public function it_handles_an_odd_number_of_teams_by_using_byes(): void
    {
        $teams = [
            new Team(1, 'A'),
            new Team(2, 'B'),
            new Team(3, 'C'),
            new Team(4, 'D'),
            new Team(5, 'E'),
        ];

        $generator = new FixtureGenerator();
        $weeks = $generator->generateDoubleRoundRobin($teams);

        // For 5 teams: add a bye => 6 teams => 5 rounds per half => 10 weeks total.
        $this->assertCount(10, $weeks);

        $matchCount = 0;
        $pairCounts = [];

        foreach ($weeks as $matches) {
            // With 5 teams, there will be 2 matches + 1 bye per week.
            $this->assertCount(2, $matches);
            foreach ($matches as $match) {
                $matchCount++;
                $min = min($match->homeTeamId, $match->awayTeamId);
                $max = max($match->homeTeamId, $match->awayTeamId);
                $pairKey = "{$min}-{$max}";
                $pairCounts[$pairKey] = ($pairCounts[$pairKey] ?? 0) + 1;
            }
        }

        // C(5,2)=10 pairs; each played twice => 20 matches.
        $this->assertSame(20, $matchCount);
        $this->assertCount(10, $pairCounts);
        foreach ($pairCounts as $pairKey => $count) {
            $this->assertSame(2, $count, "Pair {$pairKey} should be played twice.");
        }
    }
}

