<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class MatchEditingAndWeeksTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_includes_all_weeks_in_state_and_allows_editing_a_match_result(): void
    {
        Team::query()->create(['name' => 'Liverpool', 'power' => 80]);
        Team::query()->create(['name' => 'Manchester City', 'power' => 90]);
        Team::query()->create(['name' => 'Chelsea', 'power' => 70]);
        Team::query()->create(['name' => 'Arsenal', 'power' => 75]);

        $this->postJson('/api/fixtures/generate')->assertOk();

        $state = $this->getJson('/api/simulation/state')
            ->assertOk()
            ->json('data');

        $this->assertTrue($state['fixturesGenerated']);
        $this->assertSame(1, $state['currentWeek']);
        $this->assertCount(6, $state['weeks']);
        $this->assertCount(2, $state['weeks'][0]['matches']);

        $game = Game::query()->with(['homeTeam', 'awayTeam'])->firstOrFail();

        $updated = $this->patchJson("/api/matches/{$game->id}", [
            'homeGoals' => 2,
            'awayGoals' => 0,
        ])->assertOk()->json('data');

        $this->assertSame(1, $updated['currentWeek'], 'Current week should remain 1 until all week 1 games are played.');

        $homeRow = collect($updated['standings'])->firstWhere('teamName', $game->homeTeam->name);
        $this->assertNotNull($homeRow);
        $this->assertSame(3, $homeRow['points']);

        $matchInWeeks = collect($updated['weeks'])
            ->flatMap(static fn (array $week) => $week['matches'])
            ->firstWhere('id', $game->id);

        $this->assertNotNull($matchInWeeks);
        $this->assertSame(2, $matchInWeeks['homeGoals']);
        $this->assertSame(0, $matchInWeeks['awayGoals']);
        $this->assertNotNull($matchInWeeks['playedAt']);
    }
}
