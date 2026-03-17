<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\League\SimulationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class SimulationController extends Controller
{
    public function state(SimulationService $simulation): JsonResponse
    {
        return response()->json([
            'data' => $simulation->getState(),
        ]);
    }

    public function playNextWeek(SimulationService $simulation): JsonResponse
    {
        return response()->json([
            'data' => $simulation->playNextWeek(),
        ]);
    }

    public function playAllWeeks(SimulationService $simulation): JsonResponse
    {
        return response()->json([
            'data' => $simulation->playAllWeeks(),
        ]);
    }

    public function reset(SimulationService $simulation): JsonResponse
    {
        $simulation->reset();

        return response()->json([
            'data' => $simulation->getState(),
        ]);
    }
}

