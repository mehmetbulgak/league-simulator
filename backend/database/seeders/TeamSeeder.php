<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Seed initial teams for the case project.
     */
    public function run(): void
    {
        Team::query()->updateOrCreate(
            ['name' => 'Liverpool'],
            ['power' => 88],
        );

        Team::query()->updateOrCreate(
            ['name' => 'Manchester City'],
            ['power' => 92],
        );

        Team::query()->updateOrCreate(
            ['name' => 'Chelsea'],
            ['power' => 84],
        );

        Team::query()->updateOrCreate(
            ['name' => 'Arsenal'],
            ['power' => 86],
        );
    }
}

