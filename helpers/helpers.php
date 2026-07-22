<?php

declare(strict_types=1);

namespace OriolSegura;

use Illuminate\Contracts\Support\Arrayable;
use OriolSegura\Decimal\Decimal;

if (! function_exists(__NAMESPACE__ . '\sum')) {
    /**
     * Helper function to reduce a list of items by addition.
     */
    function sum(array|Arrayable $values, Decimal|int|string|null $initial = null): Decimal
    {
        return Decimal::parse($initial)->sum(...$values);
    }
}

if (! defined('DECIMAL_MIN_DIV_SCALE')) {
    /**
     * Minimum scale used for Decimal divisions.
     */
    define('DECIMAL_MIN_DIV_SCALE', 12);
}
