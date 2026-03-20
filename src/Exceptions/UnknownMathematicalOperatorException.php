<?php

declare(strict_types=1);

namespace OriolSegura\Decimal\Exceptions;

use InvalidArgumentException;

class UnknownMathematicalOperatorException extends InvalidArgumentException implements DecimalException
{
    public function __construct(string $token)
    {
        parent::__construct(message: "Unknown operator: $token");
    }
}
