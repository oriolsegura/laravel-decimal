<?php

declare(strict_types=1);

namespace OriolSegura\Decimal\Tests\Unit;

use OriolSegura\Decimal\Decimal;
use PHPUnit\Framework\TestCase;

class DecimalAliasesTest extends TestCase
{
    public function test_it_checks_arithmetic_aliases(): void
    {
        $base = Decimal::from(10);
        $other = 5;

        // 1. ADDITION (Canonical: plus)
        // Aliases: add, sum
        $expected = '15';
        $this->assertSame($expected, (string) $base->plus($other));
        $this->assertSame($expected, (string) $base->add($other));
        $this->assertSame($expected, (string) $base->sum($other));

        // 2. SUBTRACTION (Canonical: minus)
        // Aliases: take, subtract
        $expected = '5';
        $this->assertSame($expected, (string) $base->minus($other));
        $this->assertSame($expected, (string) $base->take($other));
        $this->assertSame($expected, (string) $base->subtract($other));

        // 3. MULTIPLICATION (Canonical: times)
        // Aliases: mul, multiply
        $expected = '50';
        $this->assertSame($expected, (string) $base->times($other));
        $this->assertSame($expected, (string) $base->mul($other));
        $this->assertSame($expected, (string) $base->multiply($other));

        // 4. DIVISION (Canonical: dividedBy)
        // Aliases: div
        $expected = '2';
        $this->assertSame($expected, (string) $base->dividedBy($other));
        $this->assertSame($expected, (string) $base->div($other));

        // 5. MODULUS (Canonical: mod)
        // Aliases: modulo, remainder
        $expected = '4';
        $this->assertSame($expected, (string) $base->mod(6));
        $this->assertSame($expected, (string) $base->modulo(6));
        $this->assertSame($expected, (string) $base->remainder(6));
    }

    public function test_it_checks_comparison_aliases(): void
    {
        $a = Decimal::from(10);
        $b = Decimal::from(10);
        $c = Decimal::from(5);

        // 1. EQUALS (Canonical: eq)
        // Alias: equals
        $this->assertTrue($a->eq($b));
        $this->assertTrue($a->equals($b));

        // 2. NOT EQUALS (Canonical: ne)
        // Aliases: notEquals, diff
        $this->assertTrue($a->ne($c));
        $this->assertTrue($a->notEquals($c));
        $this->assertTrue($a->diff($c));

        // 3. GREATER THAN (Canonical: gt)
        // Alias: greaterThan
        $this->assertTrue($a->gt($c));
        $this->assertTrue($a->greaterThan($c));

        // 4. GREATER THAN OR EQUAL (Canonical: gte)
        // Alias: greaterThanOrEqual
        $this->assertTrue($a->gte($b));
        $this->assertTrue($a->greaterThanOrEqual($b));

        // 5. LESS THAN (Canonical: lt)
        // Alias: lessThan
        $this->assertTrue($c->lt($a));
        $this->assertTrue($c->lessThan($a));

        // 6. LESS THAN OR EQUAL (Canonical: lte)
        // Alias: lessThanEqual
        $this->assertTrue($c->lte($a));
        $this->assertTrue($c->lessThanEqual($a));
    }

    public function test_it_checks_special_operation_aliases(): void
    {
        // 1. INVERSE (Canonical: inverse)
        // Aliases: inv, reciprocal
        $d = Decimal::from(2);

        $expected = '0.5';
        $this->assertSame($expected, (string) $d->inverse());
        $this->assertSame($expected, (string) $d->inv());
        $this->assertSame($expected, (string) $d->reciprocal());

        // 2. NEGATE (Canonical: negate)
        // Alias: neg
        $d = Decimal::from(5);

        $expected = '-5';
        $this->assertSame($expected, (string) $d->negate());
        $this->assertSame($expected, (string) $d->neg());
    }
}
