<?php

declare(strict_types=1);

namespace OriolSegura\Decimal\Tests\Unit;

use OriolSegura\Decimal\Decimal;
use PHPUnit\Framework\TestCase;

class DecimalConversionTest extends TestCase
{
    public function test_it_identifies_integers_correctly(): void
    {
        $this->assertTrue(Decimal::from(5)->isInteger());
        $this->assertTrue(Decimal::from('100')->isInteger());
        $this->assertTrue(Decimal::from('-50')->isInteger());

        $this->assertFalse(Decimal::from('5.5')->isInteger());
        $this->assertFalse(Decimal::from('0.0001')->isInteger());
    }

    public function test_it_identifies_decimals_normalized_as_integers(): void
    {
        $this->assertTrue(Decimal::from('5.00')->isInteger());
        $this->assertTrue(Decimal::from('10.00000')->isInteger());
    }

    public function test_it_converts_to_native_integer(): void
    {
        $d = Decimal::from('123');
        $this->assertSame(123, $d->toInt());

        $d2 = Decimal::from('12.99');
        $this->assertSame(12, $d2->toInt());

        $d3 = Decimal::from('-5.5');
        $this->assertSame(-5, $d3->toInt());
    }

    public function test_it_converts_to_native_float(): void
    {
        $d = Decimal::from('12.5');
        $this->assertSame(12.5, $d->toFloat());
        $this->assertIsFloat($d->toFloat());

        $precise = Decimal::from('0.1');
        $this->assertNotEquals(0.3, $precise->toFloat() + 0.2);
    }
}
