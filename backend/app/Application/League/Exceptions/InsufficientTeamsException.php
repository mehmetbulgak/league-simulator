<?php

declare(strict_types=1);

namespace App\Application\League\Exceptions;

use RuntimeException;

final class InsufficientTeamsException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('At least 2 teams are required to generate fixtures.');
    }
}

