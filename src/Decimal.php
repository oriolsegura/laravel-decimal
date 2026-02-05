<?php

namespace OriolSegura\Decimal;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;
use OriolSegura\Decimal\Exceptions\DivisionByZeroException;
use OriolSegura\Decimal\Exceptions\WrongDecimalFormatException;
use Stringable;

/**
 * @author Oriol Segura <oriol.segura.nino@gmail.com>
 *
 * @immutable
 * An immutable Value Object for handling high-precision decimals in Laravel,
 * compatible with Eloquent casting and JSON serialization.
 * Provides a rich set of methods for arithmetic operations and comparisons while ensuring data integrity.
 */
final readonly class Decimal implements Castable, JsonSerializable, Stringable
{
    private const MIN_DIV_SCALE = 12;

    protected function __construct(
        private string $value,
        private int $scale,
    ) {}

    /**
     * Create a new Decimal instance from a string, integer, or another Decimal.
     *
     * @throws WrongDecimalFormatException If the format is invalid.
     */
    public static function from(self|int|string|null $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        [$strValue, $scale] = self::validateAndAnalyze($value);

        return new self($strValue, $scale);
    }

    /**
     * Parse and validate the input value.
     *
     * @return array{0: string, 1: int} Returns [string value, int scale]
     * @throws WrongDecimalFormatException
     */
    public static function validateAndAnalyze(int|string|null $value): array
    {
        if (is_null($value)) {
            $value = 0;
        }

        if (is_int($value)) {
            return [(string) $value, 0];
        }

        $value = (string) $value;
        $value = trim($value);
        $value = ltrim($value, '+');

        if (! preg_match('/^-?\d+(\.\d+)?$/', $value)) {
            throw new WrongDecimalFormatException($value);
        }

        if (str_contains($value, '.')) {
            $parts = explode('.', $value);
            $parts[1] = rtrim($parts[1], '0');
            $parts[1] = rtrim($parts[1], '.');

            if (empty($parts[1])) {
                $scale = 0;
                $value = $parts[0];
            } else {
                $scale = strlen($parts[1]);
                $value = "$parts[0].$parts[1]";
            }
        } else {
            $scale = 0;
        }

        return [$value, $scale];
    }

    /**
     * Create a Decimal instance representing zero.
     */
    public static function zero(): self
    {
        return self::from('0');
    }

    /**
     * Get the caster class to use when casting from / to this cast target.
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes
        {
            public function get(Model $model, string $key, mixed $value, array $attributes): Decimal|null
            {
                return is_null($value) ? null : Decimal::from($value);
            }

            public function set(Model $model, string $key, mixed $value, array $attributes): string|null
            {
                return is_null($value) ? null : (string) $value;
            }

            public function serialize(Model $model, string $key, mixed $value, array $attributes): string|null
            {
                return is_null($value) ? null : (string) $value;
            }
        };
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return (string) $this;
    }

    // ──────────────────────────────
    // Utility methods
    // ──────────────────────────────

    public function getScale(): int
    {
        return $this->scale;
    }

    public function clone(): self
    {
        return clone $this;
    }

    public function copy(): self
    {
        return clone $this;
    }

    // ──────────────────────────────
    // Comparisons
    // ──────────────────────────────

    protected function cmp(self|int|string $other): int
    {
        $other = self::from($other);

        return bccomp(
            $this->value,
            $other->value,
            scale: max($this->scale, $other->scale),
        );
    }

    public function eq(self|int|string $other): bool
    {
        return $this->cmp($other) === 0;
    }

    public function equals(self|int|string $other): bool
    {
        return $this->eq($other);
    }

    public function ne(self|int|string $other): bool
    {
        return ! $this->eq($other);
    }

    public function notEquals(self|int|string $other): bool
    {
        return $this->ne($other);
    }

    public function diff(self|int|string $other): bool
    {
        return $this->ne($other);
    }

    public function gt(self|int|string $other): bool
    {
        return $this->cmp($other) === 1;
    }

    public function greaterThan(self|int|string $other): bool
    {
        return $this->gt($other);
    }

    public function gte(self|int|string $other): bool
    {
        return $this->cmp($other) >= 0;
    }

    public function greaterThanOrEqual(self|int|string $other): bool
    {
        return $this->gte($other);
    }

    public function lt(self|int|string $other): bool
    {
        return $this->cmp($other) === -1;
    }

    public function lessThan(self|int|string $other): bool
    {
        return $this->lt($other);
    }

    public function lte(self|int|string $other): bool
    {
        return $this->cmp($other) <= 0;
    }

    public function lessThanEqual(self|int|string $other): bool
    {
        return $this->lte($other);
    }

    public function isZero(): bool
    {
        return $this->cmp(self::zero()) === 0;
    }

    public function isPositive(): bool
    {
        return $this->gte(self::zero());
    }

    public function isNegative(): bool
    {
        return $this->lte(self::zero());
    }

    public function isStrictlyPositive(): bool
    {
        return $this->gt(self::zero());
    }

    public function isStrictlyNegative(): bool
    {
        return $this->lt(self::zero());
    }

    public static function min(self|int|string $value, self|int|string ...$values): self
    {
        $min = self::from($value);

        foreach ($values as $val) {
            $val = self::from($val);

            if ($val->lt($min)) {
                $min = $val;
            }
        }

        return $min;
    }

    public static function max(self|int|string $value, self|int|string ...$values): self
    {
        $max = self::from($value);

        foreach ($values as $val) {
            $val = self::from($val);

            if ($val->gt($max)) {
                $max = $val;
            }
        }

        return $max;
    }

    // ──────────────────────────────
    // Arithmetics
    // ──────────────────────────────

    public function plus(self|int|string $other): self
    {
        $other = self::from($other);
        $scale = max($this->scale, $other->scale);

        return self::from(bcadd(
            $this->value,
            $other->value,
            scale: $scale,
        ));
    }

    public function add(self|int|string $other): self
    {
        return $this->plus($other);
    }

    public function sum(self|int|string $other): self
    {
        return $this->plus($other);
    }

    public function minus(self|int|string $other): self
    {
        $other = self::from($other);
        $scale = max($this->scale, $other->scale);

        return self::from(bcsub(
            $this->value,
            $other->value,
            scale: $scale,
        ));
    }

    public function take(self|int|string $other): self
    {
        return $this->minus($other);
    }

    public function subtract(self|int|string $other): self
    {
        return $this->minus($other);
    }

    /**
     * Divide the current value by another value.
     * If no scale is provided, it calculates the minimum necessary scale (at least 12).
     * If scale is provided, it applies Half-Up Rounding.
     *
     * @throws DivisionByZeroException
     */
    public function dividedBy(self|int|string $other, int|null $scale = null): self
    {
        $other = self::from($other);

        if ($other->isZero()) {
            throw new DivisionByZeroException();
        }

        if ($other->eq('1') && (is_null($scale) || $scale >= $this->scale)) {
            return $this;
        }

        if (is_null($scale)) {
            return self::from(bcdiv(
                $this->value,
                $other->value,
                scale: max($this->scale, $other->scale, self::MIN_DIV_SCALE),
            ));
        }

        $result = bcdiv(
            $this->value,
            $other->value,
            scale: $scale + 1,
        );

        $lastDigit = (int) substr($result, -1);
        $truncated = substr($result, 0, -1);

        if ($lastDigit < 5) {
            return self::from($truncated);
        }

        $unit = bcpow('0.1', (string) $scale, scale: $scale);

        if ($result[0] === '-') {
            return self::from(bcsub($truncated, $unit, scale: $scale));
        }

        return self::from(bcadd($truncated, $unit, scale: $scale));
    }

    public function div(self|int|string $other, int|null $scale = null): self
    {
        return $this->dividedBy($other, scale: $scale);
    }

    public function inverse(): self
    {
        return self::from('1')->dividedBy($this);
    }

    public function inv(): self
    {
        return $this->inverse();
    }

    public function reciprocal(): self
    {
        return $this->inverse();
    }

    public function times(self|int|string $other): self
    {
        $other = self::from($other);
        $scale = $this->scale + $other->scale;

        return self::from(bcmul(
            $this->value,
            $other->value,
            scale: $scale,
        ));
    }

    public function mul(self|int|string $other): self
    {
        return $this->times($other);
    }

    public function multiply(self|int|string $other): self
    {
        return $this->times($other);
    }

    public function negate(): self
    {
        return $this->times('-1');
    }

    public function neg(): self
    {
        return $this->negate();
    }

    public function abs(): self
    {
        if ($this->isStrictlyNegative()) {
            return $this->negate();
        }

        return $this;
    }

    // ──────────────────────────────
    // Checks & Converters
    // ──────────────────────────────

    /**
     * Check if the value represents a whole number (has no significant decimals).
     *
     * Relies on validateAndAnalyze to adjust the scale correctly.
     */
    public function isInteger(): bool
    {
        return $this->scale === 0;
    }

    /**
     * Convert the value to a native PHP integer.
     *
     * @warning This may result in data loss or overflow if the value exceeds PHP_INT_MAX.
     */
    public function toInt(): int
    {
        return (int) $this->value;
    }

    /**
     * Convert the value to a native PHP float.
     *
     * @warning Precision is likely to be lost due to IEEE 754 limitations.
     */
    public function toFloat(): float
    {
        return (float) $this->value;
    }
}
