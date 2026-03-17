<?php

declare(strict_types=1);

namespace App\Application\League;

use App\Application\League\Exceptions\FixturesAlreadyGeneratedException;
use App\Application\League\Exceptions\InsufficientTeamsException;
use App\Domain\League\Entities\Team as DomainTeam;
use App\Domain\League\Services\FixtureGenerator;
use App\Models\Game;
use App\Models\Team;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class FixtureService
{
    public function __construct(
        private readonly FixtureGenerator $generator,
    ) {
    }

    /**
     * @return array<int, array{week: int, matches: array<int, array<string, mixed>>}>
     */
    public function listByWeek(): array
    {
        $games = Game::ordered()
            ->with(['homeTeam:id,name', 'awayTeam:id,name'])
            ->get();

        return $this->groupByWeek($games);
    }

    /**
     * @return array<int, array{week: int, matches: array<int, array<string, mixed>>}>
     */
    public function generate(): array
    {
        if (Game::query()->exists()) {
            throw new FixturesAlreadyGeneratedException();
        }

        $teams = Team::ordered()->get(['id', 'name', 'power']);
        if ($teams->count() < 2) {
            throw new InsufficientTeamsException();
        }

        $domainTeams = $teams
            ->map(static fn (Team $team) => new DomainTeam(
                id: (int) $team->id,
                name: (string) $team->name,
                power: (int) $team->power,
            ))
            ->all();

        $weeks = $this->generator->generateDoubleRoundRobin($domainTeams);

        $now = now();
        $rows = [];
        foreach ($weeks as $weekNumber => $matches) {
            foreach ($matches as $match) {
                $rows[] = [
                    'week' => $weekNumber,
                    'home_team_id' => $match->homeTeamId,
                    'away_team_id' => $match->awayTeamId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::transaction(static function () use ($rows) {
            Game::query()->insert($rows);
        });

        return $this->listByWeek();
    }

    /**
     * @param Collection<int, Game> $games
     * @return array<int, array{week: int, matches: array<int, array<string, mixed>>}>
     */
    private function groupByWeek(Collection $games): array
    {
        return $games
            ->groupBy('week')
            ->map(fn ($weekGames, $week) => [
                'week' => (int) $week,
                'matches' => $weekGames
                    ->map(fn (Game $game) => $this->presentGame($game))
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function presentGame(Game $game): array
    {
        return [
            'id' => $game->id,
            'homeTeam' => $game->homeTeam,
            'awayTeam' => $game->awayTeam,
            'homeGoals' => $game->home_goals,
            'awayGoals' => $game->away_goals,
            'playedAt' => optional($game->played_at)->toISOString(),
        ];
    }
}
