<?php

namespace OriolSegura\Decimal\Tests\Unit;

use OriolSegura\Decimal\Decimal;
use OriolSegura\Decimal\Exceptions\DivisionByZeroException;
use OriolSegura\Decimal\Exceptions\WrongDecimalFormatException;
use PHPUnit\Framework\TestCase;

class DecimalTest extends TestCase
{
    public function test_it_instantiates_correctly_and_detects_scale(): void
    {
        // Case 1: Integer
        $d1 = Decimal::from(10);
        $this->assertSame('10', (string) $d1);
        $this->assertSame(0, $d1->getScale());

        // Case 2: String with decimals
        $d2 = Decimal::from('10.505');
        $this->assertSame('10.505', (string) $d2);
        $this->assertSame(3, $d2->getScale());

        // Case 3: From another Decimal
        $d3 = Decimal::from($d2);
        $this->assertSame('10.505', (string) $d3);
    }

    public function test_it_solves_simple_floating_point_problem(): void
    {
        $a = Decimal::from('0.1');
        $b = Decimal::from('0.2');

        // In IEEE 754, 0.1 + 0.2 results in 0.30000000000000004
        $result = $a->add($b);

        $this->assertSame('0.3', (string) $result);
        $this->assertSame(1, $result->getScale());
    }

    public function test_it_handles_immutability(): void
    {
        $original = Decimal::from('10');

        $new = $original->add('5');

        $this->assertSame('10', (string) $original);
        $this->assertSame('15', (string) $new);

        $this->assertNotSame($original, $new);
    }

    public function test_it_throws_exception_on_division_by_zero(): void
    {
        $this->expectException(DivisionByZeroException::class);

        Decimal::from('10')->dividedBy('0');
    }

    public function test_it_throws_exception_on_invalid_format(): void
    {
        $this->expectException(WrongDecimalFormatException::class);
        $this->expectExceptionMessage("Wrong decimal format given: [abc]");

        Decimal::from('abc');
    }
}
