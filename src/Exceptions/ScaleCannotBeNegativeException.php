<?php

declare(strict_types=1);

namespace OriolSegura\Decimal\Exceptions;

use InvalidArgumentException;

class ScaleCannotBeNegativeException extends InvalidArgumentException implements DecimalException
{
    public function __construct(int $scale)
    {
        parent::__construct("Scale cannot be negative: $scale");
    }
}
