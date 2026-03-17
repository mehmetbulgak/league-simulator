<?php

declare(strict_types=1);

namespace App\Domain\League\Services;

use App\Domain\League\Entities\LeagueMatch;
use App\Domain\League\Entities\Team;
use InvalidArgumentException;

final class ChampionshipPredictor
{
    public function __construct(
        private readonly MatchSimulator $matchSimulator,
        private readonly StandingsCalculator $standingsCalculator,
    ) {
    }

    /**
     * Returns championship percentages (chance of finishing 1st) for each team.
     *
     * @param Team[] $teams
     * @param LeagueMatch[] $matches Played + unplayed fixtures
     * @return array<int, int> keyed by teamId with integer percentages summing to 100
     */
    public function predictChampionPercentages(array $teams, array $matches, int $simulations = 10000): array
    {
        if ($simulations <= 0) {
            throw new InvalidArgumentException('Simulations must be a positive integer.');
        }

        $teamById = $this->indexTeams($teams);
        $teamIds = array_keys($teamById);

        $played = [];
        $remaining = [];
        foreach ($matches as $match) {
            if (!$match instanceof LeagueMatch) {
                throw new InvalidArgumentException('All items in $matches must be instances of ' . LeagueMatch::class . '.');
            }

            if (!isset($teamById[$match->homeTeamId]) || !isset($teamById[$match->awayTeamId])) {
                throw new InvalidArgumentException('Match contains an unknown team id.');
            }

            if ($match->isPlayed()) {
                $played[] = $match;
                continue;
            }

            $remaining[] = $match;
        }

        if (count($remaining) === 0) {
            return $this->finishedSeasonPercentages($teams, $played);
        }

        $currentStandings = $this->standingsCalculator->calculate($teams, $matches);
        $currentPoints = [];
        foreach ($currentStandings as $row) {
            $currentPoints[$row->teamId] = $row->points;
        }

        $leaderId = $currentStandings[0]->teamId;
        $leaderPoints = $currentPoints[$leaderId];

        $remainingGamesCount = array_fill_keys($teamIds, 0);
        foreach ($remaining as $match) {
            $remainingGamesCount[$match->homeTeamId]++;
            $remainingGamesCount[$match->awayTeamId]++;
        }

        if ($this->isGuaranteedChampion($leaderId, $leaderPoints, $currentPoints, $remainingGamesCount)) {
            return $this->oneHundredToTeam($teamIds, $leaderId);
        }

        $championCounts = array_fill_keys($teamIds, 0);

        for ($i = 0; $i < $simulations; $i++) {
            $allMatches = $played;

            foreach ($remaining as $match) {
                $home = $teamById[$match->homeTeamId];
                $away = $teamById[$match->awayTeamId];

                $score = $this->matchSimulator->simulate($home, $away);

                $allMatches[] = new LeagueMatch(
                    week: $match->week,
                    homeTeamId: $match->homeTeamId,
                    awayTeamId: $match->awayTeamId,
                    score: $score,
                );
            }

            $finalStandings = $this->standingsCalculator->calculate($teams, $allMatches);
            $championCounts[$finalStandings[0]->teamId]++;
        }

        $rawPercentages = [];
        foreach ($teamIds as $teamId) {
            $rawPercentages[$teamId] = ($championCounts[$teamId] / $simulations) * 100;
        }

        return $this->normalizeToHundred($rawPercentages);
    }

    /**
     * @param Team[] $teams
     * @param LeagueMatch[] $playedMatches
     * @return array<int, int>
     */
    private function finishedSeasonPercentages(array $teams, array $playedMatches): array
    {
        $standings = $this->standingsCalculator->calculate($teams, $playedMatches);
        $championId = $standings[0]->teamId;

        $teamIds = array_map(static fn (Team $t) => $t->id, $teams);

        return $this->oneHundredToTeam($teamIds, $championId);
    }

    /**
     * @param int[] $teamIds
     * @return array<int, int>
     */
    private function oneHundredToTeam(array $teamIds, int $championId): array
    {
        $percentages = [];
        foreach ($teamIds as $teamId) {
            $percentages[$teamId] = ($teamId === $championId) ? 100 : 0;
        }

        return $percentages;
    }

    /**
     * @param array<int, int> $currentPoints keyed by teamId
     * @param array<int, int> $remainingGamesCount keyed by teamId
     */
    private function isGuaranteedChampion(int $leaderId, int $leaderPoints, array $currentPoints, array $remainingGamesCount): bool
    {
        foreach ($currentPoints as $teamId => $points) {
            if ($teamId === $leaderId) {
                continue;
            }

            $maxPossible = $points + (3 * $remainingGamesCount[$teamId]);
            if ($maxPossible >= $leaderPoints) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, float> $rawPercentages
     * @return array<int, int>
     */
    private function normalizeToHundred(array $rawPercentages): array
    {
        $floors = [];
        $fractions = [];
        $sum = 0;

        foreach ($rawPercentages as $teamId => $value) {
            $floor = (int) floor($value);
            $floors[$teamId] = $floor;
            $fractions[$teamId] = $value - $floor;
            $sum += $floor;
        }

        $diff = 100 - $sum;
        if ($diff > 0) {
            arsort($fractions);
            foreach (array_keys($fractions) as $teamId) {
                if ($diff === 0) {
                    break;
                }
                $floors[$teamId]++;
                $diff--;
            }
        } elseif ($diff < 0) {
            asort($fractions);
            foreach (array_keys($fractions) as $teamId) {
                if ($diff === 0) {
                    break;
                }
                if ($floors[$teamId] <= 0) {
                    continue;
                }
                $floors[$teamId]--;
                $diff++;
            }
        }

        return $floors;
    }

    /**
     * @param Team[] $teams
     * @return array<int, Team>
     */
    private function indexTeams(array $teams): array
    {
        if (count($teams) < 2) {
            throw new InvalidArgumentException('At least 2 teams are required to predict the champion.');
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

