<?php

declare(strict_types=1);

namespace OriolSegura\Decimal\Tests\Unit;

use OriolSegura\Decimal\Decimal;
use PHPUnit\Framework\TestCase;

class DecimalComparisonTest extends TestCase
{
    public function test_it_checks_equality_ignoring_trailing_zeros_conceptually(): void
    {
        $d1 = Decimal::from('1.5');
        $d2 = Decimal::from('1.5000');

        $this->assertTrue($d1->eq($d2));
        $this->assertTrue($d1->eq('1.5'));
    }

    public function test_it_checks_greater_and_less_than(): void
    {
        $ten = Decimal::from(10);
        $five = Decimal::from(5);

        $this->assertTrue($ten->gt($five));
        $this->assertTrue($ten->gte($five));
        $this->assertTrue($ten->gte(10));

        $this->assertTrue($five->lt($ten));
        $this->assertTrue($five->lte($ten));
    }

    public function test_it_checks_positivity_and_negativity(): void
    {
        $pos = Decimal::from('+0.00000000000000000000000000000000000000000000001');
        $neg = Decimal::from('-0.00000000000000000000000000000000000000000000001');
        $zero = Decimal::zero();

        // Positive
        $this->assertTrue($pos->isPositive());
        $this->assertTrue($pos->isStrictlyPositive());
        $this->assertFalse($zero->isStrictlyPositive());

        // Negative
        $this->assertTrue($neg->isNegative());
        $this->assertTrue($neg->isStrictlyNegative());

        // Zero
        $this->assertTrue($zero->isZero());
        $this->assertTrue($zero->isPositive());
        $this->assertTrue($zero->isNegative());
    }

    public function test_it_finds_min_and_max_values(): void
    {
        $a = Decimal::from(10);
        $b = Decimal::from(50);
        $c = Decimal::from(5);

        // Max
        $max = Decimal::max($a, $b, $c, 20);
        $this->assertSame('50', (string) $max);

        // Min
        $min = Decimal::min($a, $b, $c, '2.5');
        $this->assertSame('2.5', (string) $min);
    }
}
