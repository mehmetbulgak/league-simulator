<?php

declare(strict_types=1);

namespace App\Domain\League\Services;

use App\Domain\League\Entities\LeagueMatch;
use App\Domain\League\Entities\Team;
use InvalidArgumentException;

final class FixtureGenerator
{
    /**
     * Generates a double round-robin fixture list (home/away) using the circle method.
     *
     * @param Team[] $teams
     * @return array<int, LeagueMatch[]> Weeks keyed by 1..N
     */
    public function generateDoubleRoundRobin(array $teams): array
    {
        $teamIds = $this->normalizeTeams($teams);

        // If odd, add a bye slot (0) so the algorithm still works.
        if (count($teamIds) % 2 === 1) {
            $teamIds[] = 0;
        }

        $teamCount = count($teamIds);
        if ($teamCount < 2) {
            throw new InvalidArgumentException('At least 2 teams are required to generate fixtures.');
        }

        $rounds = $teamCount - 1;
        $matchesPerRound = intdiv($teamCount, 2);

        $firstHalf = [];
        for ($round = 0; $round < $rounds; $round++) {
            $weekNumber = $round + 1;
            $weekMatches = [];

            for ($i = 0; $i < $matchesPerRound; $i++) {
                $homeId = $teamIds[$i];
                $awayId = $teamIds[$teamCount - 1 - $i];

                if ($homeId === 0 || $awayId === 0) {
                    continue; // bye
                }

                // Heuristic home/away balancing. Deterministic and reasonably fair.
                $shouldSwap = ($i === 0) ? ($round % 2 === 1) : ($round % 2 === 0);
                if ($shouldSwap) {
                    [$homeId, $awayId] = [$awayId, $homeId];
                }

                $weekMatches[] = new LeagueMatch($weekNumber, $homeId, $awayId);
            }

            $firstHalf[$weekNumber] = $weekMatches;
            $teamIds = $this->rotate($teamIds);
        }

        $secondHalf = [];
        for ($week = 1; $week <= $rounds; $week++) {
            $weekNumber = $week + $rounds;
            $weekMatches = [];
            foreach ($firstHalf[$week] as $match) {
                $weekMatches[] = new LeagueMatch($weekNumber, $match->awayTeamId, $match->homeTeamId);
            }
            $secondHalf[$weekNumber] = $weekMatches;
        }

        return $firstHalf + $secondHalf;
    }

    /**
     * @param Team[] $teams
     * @return int[]
     */
    private function normalizeTeams(array $teams): array
    {
        if (count($teams) < 2) {
            throw new InvalidArgumentException('At least 2 teams are required to generate fixtures.');
        }

        $ids = [];
        foreach ($teams as $team) {
            if (!$team instanceof Team) {
                throw new InvalidArgumentException('All items in $teams must be instances of ' . Team::class . '.');
            }
            $ids[] = $team->id;
        }

        $unique = array_values(array_unique($ids));
        if (count($unique) !== count($ids)) {
            throw new InvalidArgumentException('Team ids must be unique.');
        }

        return $unique;
    }

    /**
     * Circle method rotation: keep first fixed, rotate the rest by one.
     *
     * @param int[] $teamIds
     * @return int[]
     */
    private function rotate(array $teamIds): array
    {
        $fixed = $teamIds[0];
        $rest = array_slice($teamIds, 1);

        $last = array_pop($rest);
        array_unshift($rest, $last);

        return array_merge([$fixed], $rest);
    }
}

