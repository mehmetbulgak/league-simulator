<?php

declare(strict_types=1);

namespace App\Application\League;

use App\Domain\League\Entities\LeagueMatch as DomainLeagueMatch;
use App\Domain\League\Entities\Score as DomainScore;
use App\Domain\League\Entities\Team as DomainTeam;
use App\Domain\League\Services\ChampionshipPredictor;
use App\Domain\League\Services\MatchSimulator;
use App\Domain\League\Services\StandingsCalculator;
use App\Models\Game;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

final class SimulationService
{
    public function __construct(
        private readonly MatchSimulator $matchSimulator,
        private readonly StandingsCalculator $standingsCalculator,
        private readonly ChampionshipPredictor $championshipPredictor,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getState(): array
    {
        $teams = Team::ordered()->get(['id', 'name', 'power']);
        $games = Game::ordered()
            ->with(['homeTeam:id,name', 'awayTeam:id,name'])
            ->get();

        $fixturesGenerated = $games->isNotEmpty();
        $totalWeeks = $fixturesGenerated ? (int) $games->max('week') : null;

        $currentWeek = null;
        if ($fixturesGenerated) {
            $currentWeek = Game::unplayed()->min('week');
            $currentWeek = $currentWeek !== null ? (int) $currentWeek : null;
        }

        $domainTeams = $teams
            ->map(static fn (Team $team) => new DomainTeam(
                id: (int) $team->id,
                name: (string) $team->name,
                power: (int) $team->power,
            ))
            ->all();

        $domainMatches = $games
            ->map(static function (Game $game) {
                $score = null;

                if ($game->played_at !== null && $game->home_goals !== null && $game->away_goals !== null) {
                    $score = new DomainScore((int) $game->home_goals, (int) $game->away_goals);
                }

                return new DomainLeagueMatch(
                    week: (int) $game->week,
                    homeTeamId: (int) $game->home_team_id,
                    awayTeamId: (int) $game->away_team_id,
                    score: $score,
                );
            })
            ->all();

        $standings = $teams->isEmpty()
            ? []
            : $this->standingsCalculator->calculate($domainTeams, $domainMatches);

        $currentWeekMatches = [];
        if ($fixturesGenerated && $currentWeek !== null) {
            $currentWeekMatches = $games
                ->where('week', $currentWeek)
                ->map(fn (Game $game) => $this->presentGame($game))
                ->values()
                ->all();
        }

        $weeks = [];
        if ($fixturesGenerated) {
            $weeks = $games
                ->groupBy('week')
                ->map(fn ($weekGames, $week) => [
                    'week' => (int) $week,
                    'matches' => $weekGames->map(fn (Game $game) => $this->presentGame($game))->values()->all(),
                ])
                ->values()
                ->all();
        }

        $predictions = [];

        if ($fixturesGenerated && $this->shouldComputePredictions($currentWeek, $totalWeeks) && $teams->isNotEmpty()) {
            $percentages = $this->championshipPredictor->predictChampionPercentages(
                teams: $domainTeams,
                matches: $domainMatches,
                simulations: $this->predictionSimulations(),
            );

            $predictions = $teams->map(static function (Team $team) use ($percentages) {
                return [
                    'teamId' => (int) $team->id,
                    'teamName' => (string) $team->name,
                    'percentage' => (int) ($percentages[(int) $team->id] ?? 0),
                ];
            })->values()->all();
        }

        return [
            'fixturesGenerated' => $fixturesGenerated,
            'currentWeek' => $currentWeek,
            'totalWeeks' => $totalWeeks,
            'isFinished' => $fixturesGenerated && $currentWeek === null,
            'teams' => $teams,
            'standings' => $standings,
            'currentWeekMatches' => $currentWeekMatches,
            'weeks' => $weeks,
            'predictions' => $predictions,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function playNextWeek(): array
    {
        $currentWeek = Game::unplayed()->min('week');
        if ($currentWeek === null) {
            return $this->getState();
        }

        $currentWeek = (int) $currentWeek;

        $teams = Team::ordered()->get(['id', 'name', 'power']);
        $domainTeamsById = [];
        foreach ($teams as $team) {
            $domainTeamsById[(int) $team->id] = new DomainTeam(
                id: (int) $team->id,
                name: (string) $team->name,
                power: (int) $team->power,
            );
        }

        $this->playWeek($currentWeek, $domainTeamsById);

        return $this->getState();
    }

    /**
     * @return array<string, mixed>
     */
    public function playAllWeeks(): array
    {
        $teams = Team::ordered()->get(['id', 'name', 'power']);
        $domainTeamsById = [];
        foreach ($teams as $team) {
            $domainTeamsById[(int) $team->id] = new DomainTeam(
                id: (int) $team->id,
                name: (string) $team->name,
                power: (int) $team->power,
            );
        }

        while (true) {
            $currentWeek = Game::unplayed()->min('week');
            if ($currentWeek === null) {
                break;
            }

            $this->playWeek((int) $currentWeek, $domainTeamsById);
        }

        return $this->getState();
    }

    public function reset(): void
    {
        Game::query()->delete();
    }

    /**
     * @param array<int, DomainTeam> $domainTeamsById
     */
    private function playWeek(int $week, array $domainTeamsById): void
    {
        DB::transaction(function () use ($week, $domainTeamsById) {
            $games = Game::query()
                ->where('week', $week)
                ->unplayed()
                ->get();

            $now = now();
            foreach ($games as $game) {
                $home = $domainTeamsById[(int) $game->home_team_id];
                $away = $domainTeamsById[(int) $game->away_team_id];

                $score = $this->matchSimulator->simulate($home, $away);

                $game->home_goals = $score->homeGoals;
                $game->away_goals = $score->awayGoals;
                $game->played_at = $now;
                $game->save();
            }
        });
    }

    private function shouldComputePredictions(?int $currentWeek, ?int $totalWeeks): bool
    {
        if ($totalWeeks === null) {
            return false;
        }

        if ($currentWeek === null) {
            return true;
        }

        $lastWeeks = $this->predictionLastWeeks();
        $startWeek = max(1, $totalWeeks - $lastWeeks + 1);

        return $currentWeek >= $startWeek;
    }

    private function predictionLastWeeks(): int
    {
        return max(1, (int) config('league.predictions.last_weeks', 3));
    }

    private function predictionSimulations(): int
    {
        return max(1, (int) config('league.predictions.simulations', 10_000));
    }

    /**
     * @return array<string, mixed>
     */
    private function presentGame(Game $game): array
    {
        return [
            'id' => $game->id,
            'homeTeam' => [
                'id' => $game->homeTeam->id,
                'name' => $game->homeTeam->name,
            ],
            'awayTeam' => [
                'id' => $game->awayTeam->id,
                'name' => $game->awayTeam->name,
            ],
            'homeGoals' => $game->home_goals,
            'awayGoals' => $game->away_goals,
            'playedAt' => optional($game->played_at)->toISOString(),
        ];
    }
}
