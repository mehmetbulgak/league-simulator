<?php

declare(strict_types=1);

namespace App\Domain\League\Services;

use App\Domain\League\DTO\Standing;
use App\Domain\League\Entities\LeagueMatch;
use App\Domain\League\Entities\Team;
use InvalidArgumentException;

final class StandingsCalculator
{
    private const WIN_POINTS = 3;
    private const DRAW_POINTS = 1;

    /**
     * @param Team[] $teams
     * @param LeagueMatch[] $matches
     * @return Standing[]
     */
    public function calculate(array $teams, array $matches): array
    {
        $teamById = $this->indexTeams($teams);

        $stats = [];
        foreach ($teamById as $teamId => $team) {
            $stats[$teamId] = [
                'played' => 0,
                'wins' => 0,
                'draws' => 0,
                'losses' => 0,
                'gf' => 0,
                'ga' => 0,
            ];
        }

        foreach ($matches as $match) {
            if (!$match instanceof LeagueMatch) {
                throw new InvalidArgumentException('All items in $matches must be instances of ' . LeagueMatch::class . '.');
            }

            if (!isset($teamById[$match->homeTeamId]) || !isset($teamById[$match->awayTeamId])) {
                throw new InvalidArgumentException('Match contains an unknown team id.');
            }

            if (!$match->isPlayed()) {
                continue;
            }

            $homeId = $match->homeTeamId;
            $awayId = $match->awayTeamId;
            $homeGoals = $match->score->homeGoals;
            $awayGoals = $match->score->awayGoals;

            $stats[$homeId]['played']++;
            $stats[$awayId]['played']++;

            $stats[$homeId]['gf'] += $homeGoals;
            $stats[$homeId]['ga'] += $awayGoals;
            $stats[$awayId]['gf'] += $awayGoals;
            $stats[$awayId]['ga'] += $homeGoals;

            if ($homeGoals > $awayGoals) {
                $stats[$homeId]['wins']++;
                $stats[$awayId]['losses']++;
                continue;
            }

            if ($homeGoals < $awayGoals) {
                $stats[$awayId]['wins']++;
                $stats[$homeId]['losses']++;
                continue;
            }

            $stats[$homeId]['draws']++;
            $stats[$awayId]['draws']++;
        }

        $standings = [];
        foreach ($teamById as $teamId => $team) {
            $row = $stats[$teamId];
            $gd = $row['gf'] - $row['ga'];
            $points = ($row['wins'] * self::WIN_POINTS) + ($row['draws'] * self::DRAW_POINTS);

            $standings[] = new Standing(
                teamId: $teamId,
                teamName: $team->name,
                played: $row['played'],
                wins: $row['wins'],
                draws: $row['draws'],
                losses: $row['losses'],
                goalsFor: $row['gf'],
                goalsAgainst: $row['ga'],
                goalDifference: $gd,
                points: $points,
            );
        }

        usort($standings, static function (Standing $a, Standing $b): int {
            return [$b->points, $b->goalDifference, $b->goalsFor, $a->teamName]
                <=> [$a->points, $a->goalDifference, $a->goalsFor, $b->teamName];
        });

        return $standings;
    }

    /**
     * @param Team[] $teams
     * @return array<int, Team>
     */
    private function indexTeams(array $teams): array
    {
        if (count($teams) < 2) {
            throw new InvalidArgumentException('At least 2 teams are required to calculate standings.');
        }

        $byId = [];
        foreach ($teams as $team) {
            if (!$team instanceof Team) {
                throw new InvalidArgumentException('All items in $teams must be instances of ' . Team::class . '.');
            }

            if (isset($byId[$team->id])) {
                throw new InvalidArgumentException('Team ids must be unique.');
            }

            $byId[$team->id] = $team;
        }

        return $byId;
    }
}
