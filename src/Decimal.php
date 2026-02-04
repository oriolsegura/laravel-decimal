<?php

namespace OriolSegura\Decimal;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;
use OriolSegura\Decimal\Exceptions\DivisionByZeroException;
use OriolSegura\Decimal\Exceptions\WrongDecimalFormatException;
use Stringable;

final readonly class Decimal implements Castable, JsonSerializable, Stringable
{
    protected function __construct(
        private string $value,
        private int $scale,
    ) {}

    public static function from(self|int|string|null $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        [$strValue, $scale] = self::validateAndAnalyze($value);

        return new self($strValue, $scale);
    }

    /**
     * @return array{0: string, 1: int} [value, scale]
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

        $parts = explode('.', $value);
        $scale = count($parts) > 1 ? strlen($parts[1]) : 0;

        return [$value, $scale];
    }

    public static function zero(): self
    {
        return self::from('0');
    }

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

    public function gt(self|int|string $other): bool
    {
        return $this->cmp($other) === 1;
    }

    public function gte(self|int|string $other): bool
    {
        return $this->cmp($other) >= 0;
    }

    public function lt(self|int|string $other): bool
    {
        return $this->cmp($other) === -1;
    }

    public function lte(self|int|string $other): bool
    {
        return $this->cmp($other) <= 0;
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

    public function dividedBy(self|int|string $other): self
    {
        $other = self::from($other);

        if ($other->isZero()) {
            throw new DivisionByZeroException();
        }

        // TODO: scale calculation
        $scale = $this->scale + $other->scale + 4;

        return self::from(bcdiv(
            $this->value,
            $other->value,
            scale: $scale,
        ));
    }

    public function inverse(): self
    {
        return self::from('1')->dividedBy($this);
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

    public function negate(): self
    {
        return $this->times('-1');
    }

    public function abs(): self
    {
        if ($this->isStrictlyNegative()) {
            return $this->negate();
        }

        return $this;
    }
}
