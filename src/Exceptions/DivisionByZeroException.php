<?php

declare(strict_types=1);

namespace OriolSegura\Decimal\Exceptions;

use RuntimeException;
use Throwable;

class DivisionByZeroException extends RuntimeException implements DecimalException
{
    public function __construct(int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct(
            message: 'Attempt to divide by zero.',
            code: $code,
            previous: $previous,
        );
    }
}
