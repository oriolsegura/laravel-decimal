# Laravel Decimal

A lightweight, immutable Value Object to handle decimals in Laravel without losing precision.

## Requirements

- PHP 8.2+
- Laravel 11.0+

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

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
