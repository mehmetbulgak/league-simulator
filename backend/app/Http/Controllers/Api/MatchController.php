<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\League\MatchResultService;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateMatchResultRequest;
use App\Models\Game;
use Illuminate\Http\JsonResponse;

final class MatchController extends Controller
{
    public function update(UpdateMatchResultRequest $request, Game $game, MatchResultService $matches): JsonResponse
    {
        $data = $request->validated();

        return response()->json([
            'data' => $matches->update(
                game: $game,
                homeGoals: $data['homeGoals'],
                awayGoals: $data['awayGoals'],
            ),
        ]);
    }
}
