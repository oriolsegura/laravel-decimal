# Laravel Decimal

[![Latest Version on Packagist](https://img.shields.io/packagist/v/oriolsegura/laravel-decimal.svg?style=flat-square)](https://packagist.org/packages/oriolsegura/laravel-decimal)
[![Tests](https://img.shields.io/github/actions/workflow/status/oriolsegura/laravel-decimal/tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/oriolsegura/laravel-decimal/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/oriolsegura/laravel-decimal.svg?style=flat-square)](https://packagist.org/packages/oriolsegura/laravel-decimal)
[![License](https://img.shields.io/packagist/l/oriolsegura/laravel-decimal.svg?style=flat-square)](https://packagist.org/packages/oriolsegura/laravel-decimal)
[![PHP Version](https://img.shields.io/packagist/php-v/oriolsegura/laravel-decimal?style=flat-square)](https://packagist.org/packages/oriolsegura/laravel-decimal)

A lightweight, **immutable** Value Object to handle decimals in Laravel without losing precision.

It uses `bcmath` internally to ensure mathematical correctness where floats fail.

## 🚀 Why use this package?

Designed for **simplicity and immediate productivity**. If you need to handle money or precise numbers in Laravel but don't want the overhead of heavy financial libraries or complex configurations, this is for you.

* **Plug & Play:** Zero configuration. Works out of the box.
* **Laravel Native:** Built with Eloquent casting in mind.
* **Lightweight:** No heavy dependencies. Just a wrapper around `bcmath`.

Ideal for e-commerce, invoices, scientific data, and any scenario where `0.1 + 0.2` **must** equal `0.3`.

## ⚠️ The Problem with Floats

Floating-point arithmetic is not precise because IEEE 754 standard cannot represent all decimal fractions exactly.

An example of this issue:

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

## Eloquent Casting

This package shines when used with Eloquent models. You can store values as precise decimals (or strings) in your database and work with Decimal objects automatically in your code.

1. Define the cast in your Model:

```php
use Illuminate\Database\Eloquent\Model;
use OriolSegura\Decimal\Decimal;

class Product extends Model
{
    protected $casts = [
        'price' => Decimal::class, // <--- Auto-casting
    ];
}
```

2. Enjoy seamless precision:

```php
$product = Product::create([
    'price' => '19.99',
]);

// You can operate directly on the attribute
$product->price = $product->price->add('5.50');
$product->save();
```

## Usage API

### Creation

You can create a Decimal from a string, integer or another Decimal.

```php
$a = Decimal::from('10.50');
$b = Decimal::from(10);
```

Additionally, a `Decimal::zero()` static method is available for convenience.

### Arithmetic

Since the object is immutable, operations always return a new instance.

```php
$val = Decimal::from('10');

// Chaining
$result = $val->plus(5)->minus(2)->times(2); // (10 + 5 - 2) * 2 = 26
```

### Supported methods

These are the implemented methods for arithmetic operations:

- `plus(self|int|string $other)` (alias: `add`, `sum`)
- `minus(self|int|string $other)` (alias: `take`, `subtract`)
- `times(self|int|string $other)` (alias: `mul`, `multiply`)
- `dividedBy(self|int|string $other, int|null $scale = null)` (alias: `div`)
- `mod(self|int|string $other)` (alias: `modulo`, `remainder`)
- `abs()`
- `negate()` (aliases: `neg`)
- `inverse()` (aliases: `inv`, `reciprocal`)

And these are the implemented methods for comparisons:

- `cmp(self|int|string $other)` (alias: `compare`)
- `eq(self|int|string $other)` (alias: `equals`)
- `ne(self|int|string $other)` (alias: `notEquals`, `diff`)
- `gt(self|int|string $other)` (alias: `greaterThan`)
- `gte(self|int|string $other)` (alias: `greaterThanOrEqual`)
- `lt(self|int|string $other)` (alias: `lessThan`)
- `lte(self|int|string $other)` (alias: `lessThanOrEqual`)
- `isZero()`
- `isPositive()`
- `isNegative()`
- `isStrictlyPositive()`
- `isStrictlyNegative()`
- `min(self|int|string $other, self|int|string ...$values)`
- `max(self|int|string $other, self|int|string ...$values)`

## Division & Rounding

By default, division uses an automatic scale equal to the maximum of the two operands scales, ensuring this is also at least 12 decimal places to ensure precision. But you can also provide a `$scale` parameter to specify the number of decimal places in the result.

Currently, this library only supports Half-Up Rounding for division.

```php
// Automatic scale (truncating logic for infinite fractions)
echo Decimal::from(1)->div(3); // "0.333333333333"

// Explicit scale (Rounds Half-Up)
echo Decimal::from(2)->div(3, scale: 2); // "0.67"
```

## Safe Expression Evaluator

Laravel Decimal includes a robust, zero-dependency mathematical expression parser based on the [Shunting yard algorithm](https://en.wikipedia.org/wiki/Shunting_yard_algorithm). It respects order of operations, nested parentheses, and handles unary negative numbers safely.

```php
echo Decimal::resolve('(10.5 + 2) * -1.5 / 2'); // '-9.375'
```

Thanks to the `__toString()` implementation, you can even interpolate existing Decimal instances directly into your expression strings for ultimate readability:

```php
$base = Decimal::from('100');
$taxRate = Decimal::from('0.21');

echo Decimal::resolve("$base + ($base * $taxRate)"); // '121'
```

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
