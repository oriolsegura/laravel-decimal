<?php

declare(strict_types=1);

namespace OriolSegura\Decimal\Tests\Unit;

use OriolSegura\Decimal\Decimal;
use OriolSegura\Decimal\Exceptions\DivisionByZeroException;
use PHPUnit\Framework\TestCase;

class DecimalDivisionTest extends TestCase
{
    public function test_it_throws_exception_on_division_by_zero(): void
    {
        $this->expectException(DivisionByZeroException::class);

        Decimal::from('10')->dividedBy('0');
    }

    public function test_it_divides_integers_exactly(): void
    {
        $result = Decimal::from(10)->dividedBy(2);

        $this->assertSame('5', (string) $result);
        $this->assertSame(0, $result->getScale());
    }

    public function test_it_creates_decimals_automatically(): void
    {
        $result = Decimal::from(5)->dividedBy(2);

        $this->assertSame('2.5', (string) $result);
        $this->assertSame(1, $result->getScale());
    }

    public function test_it_uses_min_scale_for_infinite_fractions(): void
    {
        // We do not set scale here, so it should default to MIN_DIV_SCALE (12)
        $result = Decimal::from(1)->dividedBy(3);

        $this->assertTrue(str_starts_with((string) $result, '0.333333333333'));
        $this->assertGreaterThanOrEqual(12, $result->getScale());
    }

    public function test_it_optimizes_division_by_one(): void
    {
        $d = Decimal::from('10.50');

        // Division by 1 without scale, returns the same object
        $resultA = $d->dividedBy(1);
        $this->assertSame($d, $resultA);

        // Division by 1 with greater scale, returns the same object
        $resultB = $d->dividedBy(1, scale: 5);
        $this->assertSame($d, $resultB);
    }

    public function test_it_bypasses_optimization_when_rounding_is_needed(): void
    {
        // Tenim 3 decimals: 10.555
        $d = Decimal::from('10.555');

        // Division by 1 with smaller scale, returns a new object (because it needs to truncate)
        $result = $d->dividedBy(1, scale: 2);
        $this->assertSame('10.56', (string) $result);
        $this->assertNotSame($d, $result); // Ha de ser un objecte nou
    }

    public function test_it_rounds_half_up_correctly(): void
    {
        $result = Decimal::from(2)->dividedBy(3, scale: 2);
        $this->assertSame('0.67', (string) $result);

        $result5 = Decimal::from('0.125')->dividedBy(1, scale: 2);
        $this->assertSame('0.13', (string) $result5);
    }

    public function test_it_truncates_correctly_when_below_five(): void
    {
        $result = Decimal::from(1)->dividedBy(3, scale: 2);
        $this->assertSame('0.33', (string) $result);

        $result4 = Decimal::from('0.124')->dividedBy(1, scale: 2);
        $this->assertSame('0.12', (string) $result4);
    }

    public function test_it_handles_carry_over_rounding(): void
    {
        $d = Decimal::from('1.99996');
        $result = $d->dividedBy(1, scale: 2);

        $this->assertSame('2', (string) $result);
    }

    public function test_it_handles_negative_rounding_symmetrically(): void
    {
        $result = Decimal::from(-2)->dividedBy(3, scale: 2);
        $this->assertSame('-0.67', (string) $result);

        $result2 = Decimal::from(-1)->dividedBy(3, scale: 2);
        $this->assertSame('-0.33', (string) $result2);
    }
}
