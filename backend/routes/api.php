<?php

use App\Http\Controllers\Api\FixtureController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\SimulationController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/teams', [TeamController::class, 'index']);
Route::patch('/teams/{team}', [TeamController::class, 'update']);

Route::post('/fixtures/generate', [FixtureController::class, 'generate']);
Route::get('/fixtures', [FixtureController::class, 'index']);

Route::get('/simulation/state', [SimulationController::class, 'state']);
Route::post('/simulation/play-next-week', [SimulationController::class, 'playNextWeek']);
Route::post('/simulation/play-all-weeks', [SimulationController::class, 'playAllWeeks']);
Route::post('/simulation/reset', [SimulationController::class, 'reset']);

Route::patch('/matches/{game}', [MatchController::class, 'update']);
