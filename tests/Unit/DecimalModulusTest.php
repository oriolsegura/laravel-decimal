<?php

declare(strict_types=1);

namespace OriolSegura\Decimal\Tests\Unit;

use PHPUnit\Framework\TestCase;
use OriolSegura\Decimal\Decimal;
use OriolSegura\Decimal\Exceptions\DivisionByZeroException;

class DecimalModulusTest extends TestCase
{
    public function test_it_calculates_modulus_correctly(): void
    {
        // Case 1: Integers (10 % 3 = 1)
        $val = Decimal::from(10);
        $this->assertSame('1', (string) $val->mod(3));

        // Case 2: Dividend decimal (10.5 % 3 = 1.5)
        $valDec = Decimal::from('10.5');
        $this->assertSame('1.5', (string) $valDec->mod(3));

        // Case 3: Both dividend and divisor decimals (5.5 % 2.5 = 0.5)
        $valMix = Decimal::from('5.5');
        $this->assertSame('0.5', (string) $valMix->mod('2.5'));
    }

    public function test_it_throws_exception_on_modulo_by_zero(): void
    {
        $this->expectException(DivisionByZeroException::class);

        Decimal::from(10)->mod(0);
    }

    public function test_it_handles_negative_modulus(): void
    {
        $this->assertSame('-1', (string) Decimal::from(-10)->mod(3));
        $this->assertSame('1', (string) Decimal::from(10)->mod(-3));
    }
}
