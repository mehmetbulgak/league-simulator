<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\League\TeamPowerService;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTeamPowerRequest;
use App\Models\Team;
use Illuminate\Http\JsonResponse;

final class TeamController extends Controller
{
    public function index(): JsonResponse
    {
        $teams = Team::ordered()->get(['id', 'name', 'power']);

        return response()->json([
            'data' => $teams,
        ]);
    }

    public function update(UpdateTeamPowerRequest $request, Team $team, TeamPowerService $teams): JsonResponse
    {
        $data = $request->validated();

        return response()->json([
            'data' => $teams->updatePower($team, $data['power']),
        ]);
    }
}
