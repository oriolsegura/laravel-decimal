<?php

declare(strict_types=1);

namespace OriolSegura;

use Illuminate\Contracts\Support\Arrayable;
use OriolSegura\Decimal\Decimal;

if (! function_exists(__NAMESPACE__ . '\\sum')) {
    /**
     * Helper function to reduce a list of items by addition.
     */
    function sum(array|Arrayable $values, Decimal|int|string|null $initial = null): Decimal
    {
        $items = $values instanceof Arrayable ? $values->toArray() : $values;

        return Decimal::parse($initial)->sum(...$items);
    }
}

if (! defined(__NAMESPACE__ . '\\DIV_SCALE')) {
    /**
     * Minimum scale used for Decimal divisions.
     */
    define(__NAMESPACE__ . '\\DIV_SCALE', 12);
}

if (! defined('DECIMAL_MIN_DIV_SCALE')) {
    /**
     * Minimum scale used for Decimal divisions.
     *
     * @deprecated 0.11.1 Use OriolSegura\DIV_SCALE instead.
     */
    define('DECIMAL_MIN_DIV_SCALE', DIV_SCALE);
}
