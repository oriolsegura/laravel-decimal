<?php

declare(strict_types=1);

namespace OriolSegura\Decimal\Exceptions;

use RuntimeException;
use Throwable;

class WrongDecimalFormatException extends RuntimeException implements DecimalException
{
    public function __construct(mixed $given, int $code = 0, Throwable|null $previous = null)
    {
        $value = is_scalar($given) ? (string) $given : gettype($given);

        parent::__construct(
            message: "Wrong decimal format given: [$value].",
            code: $code,
            previous: $previous,
        );
    }
}
