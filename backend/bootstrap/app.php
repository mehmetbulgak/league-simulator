<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Application\League\Exceptions\FixturesAlreadyGeneratedException;
use App\Application\League\Exceptions\InsufficientTeamsException;
use App\Application\League\Exceptions\TeamPowerLockedException;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(static function (FixturesAlreadyGeneratedException $e, $request) {
            if (!$request->expectsJson()) {
                return null;
            }

            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_CONFLICT);
        });

        $exceptions->renderable(static function (InsufficientTeamsException $e, $request) {
            if (!$request->expectsJson()) {
                return null;
            }

            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $exceptions->renderable(static function (TeamPowerLockedException $e, $request) {
            if (!$request->expectsJson()) {
                return null;
            }

            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_CONFLICT);
        });
    })->create();
