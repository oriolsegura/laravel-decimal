<?php

declare(strict_types=1);

namespace OriolSegura\Decimal\Exceptions;

use InvalidArgumentException;

class InvalidExpressionException extends InvalidArgumentException implements DecimalException
{
    public function __construct()
    {
        parent::__construct(message: 'Invalid mathematical expression');
    }
}
