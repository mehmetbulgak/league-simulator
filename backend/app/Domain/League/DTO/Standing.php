<?php

declare(strict_types=1);

namespace App\Domain\League\DTO;

final readonly class Standing
{
    public int $teamId;
    public string $teamName;
    public int $played;
    public int $wins;
    public int $draws;
    public int $losses;
    public int $goalsFor;
    public int $goalsAgainst;
    public int $goalDifference;
    public int $points;

    public function __construct(
        int $teamId,
        string $teamName,
        int $played,
        int $wins,
        int $draws,
        int $losses,
        int $goalsFor,
        int $goalsAgainst,
        int $goalDifference,
        int $points,
    ) {
        $this->teamId = $teamId;
        $this->teamName = $teamName;
        $this->played = $played;
        $this->wins = $wins;
        $this->draws = $draws;
        $this->losses = $losses;
        $this->goalsFor = $goalsFor;
        $this->goalsAgainst = $goalsAgainst;
        $this->goalDifference = $goalDifference;
        $this->points = $points;
    }
}

