# Laravel Decimal

A lightweight, immutable Value Object to handle decimals in Laravel without losing precision.
It uses `bcmath` internally to ensure `0.1 + 0.2 === 0.3`.

## Requirements

- PHP 8.2+
- Laravel 10.0+

## Installation

```bash
composer require oriolsegura/laravel-decimal
```

## Usage

```php
use OriolSegura\Decimal\Decimal;

$a = Decimal::from('0.1');
$b = Decimal::from('0.2');

$result = $a->add($b);

echo $result; // "0.3"
```

## Running Tests

```bash
composer test
```

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
