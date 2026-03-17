<?php

declare(strict_types=1);

namespace App\Application\League\Exceptions;

use RuntimeException;

final class TeamPowerLockedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Team power is locked because fixtures are already generated. Reset to change power.');
    }
}

