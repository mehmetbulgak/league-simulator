<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Simulation Settings
    |--------------------------------------------------------------------------
    |
    | These settings control the league simulation behavior and are exposed via
    | environment variables to make the project easy to tune during the case.
    |
    */

    'max_goals_per_team' => (int) env('LEAGUE_MAX_GOALS_PER_TEAM', 20),

    'predictions' => [
        // Start calculating probabilities when entering the last N weeks.
        'last_weeks' => (int) env('LEAGUE_PREDICTION_LAST_WEEKS', 3),

        // Monte Carlo iterations (higher => smoother but slower).
        'simulations' => (int) env('LEAGUE_PREDICTION_SIMULATIONS', 10_000),
    ],
];

