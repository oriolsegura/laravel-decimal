# Laravel Decimal

[![Latest Version on Packagist](https://img.shields.io/packagist/v/oriolsegura/laravel-decimal.svg?style=flat-square)](https://packagist.org/packages/oriolsegura/laravel-decimal)
[![Tests](https://img.shields.io/github/actions/workflow/status/oriolsegura/laravel-decimal/tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/oriolsegura/laravel-decimal/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/oriolsegura/laravel-decimal.svg?style=flat-square)](https://packagist.org/packages/oriolsegura/laravel-decimal)
[![License](https://img.shields.io/packagist/l/oriolsegura/laravel-decimal.svg?style=flat-square)](https://packagist.org/packages/oriolsegura/laravel-decimal)
[![PHP Version](https://img.shields.io/packagist/php-v/oriolsegura/laravel-decimal?style=flat-square)](https://packagist.org/packages/oriolsegura/laravel-decimal)

**A lightweight, immutable Value Object for high-precision decimal arithmetic in Laravel.**

Uses `bcmath` internally to guarantee numerical correctness — essential for financial applications where floating-point errors are unacceptable.

## 🚀 Why This Package Matters

Floating-point arithmetic is fundamentally imprecise. This library solves that problem cleanly:

- **Financial-grade precision**: Perfect for money, pricing, accounting, and market data.
- **Immutable & Type-Safe**: Designed with Value Object principles in mind.
- **Production-focused**: Built with rigorous quality practices.

Featured on [Laravel News](https://laravel-news.com).

### Quality & Correctness Pipeline
- **Mutation Testing** with [Infection](https://github.com/infection/infection).

## ⚠️ The Problem with Floats

Floating-point arithmetic is not precise because IEEE 754 standard cannot represent all decimal fractions exactly.

Some examples of this issue:

```php
echo sprintf("%.17f", 0.1 + 0.2); // 0.30000000000000004 ❌

echo var_dump(0.3 === (0.1 + 0.2)); // bool(false) ❌
````

## 🛠️ Requirements

- PHP 8.2+
- Laravel 11.0+ / 12.0+ / 13.0+
- `ext-bcmath`

## Installation

```bash
composer require oriolsegura/laravel-decimal
```

## Eloquent Integration

This package shines when used with Eloquent models. You can store values as precise decimals (or strings) in your database and work with Decimal objects automatically in your code.

```php
use Illuminate\Database\Eloquent\Model;
use OriolSegura\Decimal\Decimal;

class Product extends Model
{
    protected $casts = [
        'price' => Decimal::class, // <-- Automatic casting
    ];
}


// Enjoy seamless precision!

$product = Product::create([
    'price' => '19.99',
]);

$product->price = $product->price->add('5.50');
$product->save();
```

## Usage Highlights

### Creation & Arithmetic

You can create a Decimal from a string, integer or another Decimal.

```php
$val = Decimal::from('10.50');

$result = $val->plus('5.50')
              ->minus(2)
              ->times(2);

echo $val; // "10.50" (original value remains unchanged)
echo $result; // "28.00" (new Decimal instance with the result)
```

Additionally, a `Decimal::zero()` static method is available for convenience.

### Safe Expression Evaluator

Laravel Decimal includes a robust, zero-dependency mathematical expression parser based on the [Shunting yard algorithm](https://en.wikipedia.org/wiki/Shunting_yard_algorithm).

```php
echo Decimal::resolve('(10.5 + 2) * -1.5 / 2'); // '-9.375'
```

Thanks to the `__toString()` implementation, you can even interpolate existing Decimal instances directly into your expression strings for ultimate readability:

```php
$base    = Decimal::from('100');
$taxRate = Decimal::from('0.21');

echo Decimal::resolve("$base + ($base * $taxRate)"); // '121'
```

### Division & Rounding

By default, division uses an automatic scale equal to the maximum of the two operands scales, ensuring this is also at least 12 decimal places to ensure precision. But you can also provide a `$scale` parameter to specify the number of decimal places in the result.

```php
echo Decimal::from(2)->div(3, scale: 2); // "0.67"
```

### All Supported Methods

These are the implemented methods for arithmetic operations:

- `plus(self|int|string $other, self|int|string ...$others)` (aliases: `add`, `sum`)
- `minus(self|int|string $other)` (aliases: `take`, `subtract`)
- `times(self|int|string $other, self|int|string ...$others)` (aliases: `mul`, `multiply`)
- `dividedBy(self|int|string $other, int|null $scale = null)` (alias: `div`)
- `inverse(null|int $scale = null)` (aliases: `inv`, `reciprocal`)
- `mod(self|int|string $other)` (aliases: `modulo`, `remainder`)
- `negate()` (alias: `neg`)
- `abs()`

And these are the implemented methods for comparisons:

- `cmp(self|int|string $other)` (alias: `compare`)
- `eq(self|int|string $other)` (alias: `equals`)
- `ne(self|int|string $other)` (aliases: `notEquals`, `diff`)
- `gt(self|int|string $other)` (alias: `greaterThan`)
- `gte(self|int|string $other)` (alias: `greaterThanOrEqual`)
- `lt(self|int|string $other)` (alias: `lessThan`)
- `lte(self|int|string $other)` (alias: `lessThanOrEqual`)
- `isZero()`
- `isPositive()`
- `isNegative()`
- `isStrictlyPositive()`
- `isStrictlyNegative()`
- static: `min(self|int|string $other, self|int|string ...$values)`
- static: `max(self|int|string $other, self|int|string ...$values)`

Support is also given for truncation and rounding:

- `truncate(int $scale = 0)`
- `roundUp(int $scale = 0)`

### Operate with Collections

Both the sum and multiplication methods leverage PHP's variadic arguments, making them incredibly easy to apply to collections:

```php
$sum = $initial->sum(...$collection->pluck('value'));
// or if no initial value is needed:
$sum = Decimal::zero()->sum(...$collection->pluck('value'));
```

This clean syntax completely replaces the traditional, much more verbose `reduce` pattern:

```php
$sum = $collection->reduce(function (Decimal $carry, $item): Decimal {
    return $carry->plus($item->value);
}, initial: $initial);
```

Which can be even simpler thanks to the helper function:

```php
use function OriolSegura\sum;
$sum = sum(...$collection->pluck('value'));
$sum = sum(...$collection->pluck('value'), initial: 64);
```

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
