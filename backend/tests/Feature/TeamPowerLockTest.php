<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TeamPowerLockTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_allows_updating_team_power_before_fixtures_are_generated(): void
    {
        $team = Team::query()->create(['name' => 'Liverpool', 'power' => 80]);
        Team::query()->create(['name' => 'Manchester City', 'power' => 90]);
        Team::query()->create(['name' => 'Chelsea', 'power' => 70]);
        Team::query()->create(['name' => 'Arsenal', 'power' => 75]);

        $this->patchJson("/api/teams/{$team->id}", [
            'power' => 95,
        ])->assertOk()->assertJsonPath('data.power', 95);

        $this->assertSame(95, $team->refresh()->power);
    }

    #[Test]
    public function it_locks_team_power_updates_after_fixtures_are_generated(): void
    {
        $team = Team::query()->create(['name' => 'Liverpool', 'power' => 80]);
        Team::query()->create(['name' => 'Manchester City', 'power' => 90]);
        Team::query()->create(['name' => 'Chelsea', 'power' => 70]);
        Team::query()->create(['name' => 'Arsenal', 'power' => 75]);

        $this->postJson('/api/fixtures/generate')->assertOk();

        $this->patchJson("/api/teams/{$team->id}", [
            'power' => 95,
        ])->assertStatus(409)->assertJsonPath('message', 'Team power is locked because fixtures are already generated. Reset to change power.');

        $this->assertSame(80, $team->refresh()->power);
    }
}

