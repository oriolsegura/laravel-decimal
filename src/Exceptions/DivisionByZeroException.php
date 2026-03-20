<?php

declare(strict_types=1);

namespace OriolSegura\Decimal\Exceptions;

use RuntimeException;

class DivisionByZeroException extends RuntimeException implements DecimalException
{
    public function __construct()
    {
        parent::__construct(message: 'Attempt to divide by zero.');
    }
}
