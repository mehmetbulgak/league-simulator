<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\League\FixtureService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class FixtureController extends Controller
{
    public function index(FixtureService $fixtures): JsonResponse
    {
        return response()->json([
            'data' => $fixtures->listByWeek(),
        ]);
    }

    public function generate(FixtureService $fixtures): JsonResponse
    {
        return response()->json([
            'data' => $fixtures->generate(),
        ]);
    }
}
