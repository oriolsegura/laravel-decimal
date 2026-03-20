<?php

declare(strict_types=1);

namespace OriolSegura\Decimal\Tests\Unit;

use OriolSegura\Decimal\Decimal;
use OriolSegura\Decimal\Exceptions\InvalidExpressionException;
use OriolSegura\Decimal\Exceptions\UnknownMathematicalOperatorException;
use PHPUnit\Framework\TestCase;

class ExpressionEvaluatorTest extends TestCase
{
    public function test_it_resolves_basic_addition_and_subtraction(): void
    {
        $this->assertSame('3', Decimal::resolve('1 + 2')->toString());
        $this->assertSame('5.5', Decimal::resolve('10 - 4.5')->toString());
    }

    public function test_it_respects_operator_precedence(): void
    {
        $this->assertSame('14', Decimal::resolve('2 + 3 * 4')->toString());
        $this->assertSame('14', Decimal::resolve('20 - 12 / 2')->toString());
    }

    public function test_it_handles_parentheses(): void
    {
        $this->assertSame('20', Decimal::resolve('(2 + 3) * 4')->toString());
        $this->assertSame('1.05', Decimal::resolve('(1 + 3.2) * 1 / 4')->toString());
    }

    public function test_it_handles_spaces_gracefully(): void
    {
        $this->assertSame('10', Decimal::resolve('  5   +     5 ')->toString());
    }

    public function test_it_handles_negative_numbers(): void
    {
        $this->assertSame('-2', Decimal::resolve('-5 + 3')->toString());
        $this->assertSame('-15', Decimal::resolve('5 * -3')->toString());
        $this->assertSame('-8', Decimal::resolve('-10 - -2')->toString());
    }

    public function test_it_evaluates_expressions_using_decimal_string_interpolation(): void
    {
        $a = Decimal::from('10.5');
        $b = Decimal::from('2');
        $c = Decimal::from('-1.5');

        $this->assertSame('-18.75', Decimal::resolve("($a + $b) * $c")->toString());
    }

    public function test_it_maintains_precision_unlike_native_php_floats(): void
    {
        $this->assertSame('0.3', Decimal::resolve('0.1 + 0.2')->toString());

        $this->assertSame('8', Decimal::resolve('(0.1 + 0.7) * 10')->toString());
    }

    public function test_it_handles_deeply_nested_parentheses_and_complex_precedence(): void
    {
        $this->assertSame('10', Decimal::resolve('((10 + 5) * (2 - -1)) / (3 + 1.5)')->toString());
    }

    public function test_it_throws_exception_on_invalid_expression(): void
    {
        $this->expectException(InvalidExpressionException::class);
        Decimal::resolve('5 + * 3');
    }

    public function test_it_throws_exception_on_unmatched_parentheses_left(): void
    {
        $this->expectException(InvalidExpressionException::class);
        Decimal::resolve('((5 + 3) * 2');
    }

    public function test_it_throws_exception_on_unmatched_parentheses_right(): void
    {
        $this->expectException(InvalidExpressionException::class);
        Decimal::resolve('(5 + 3)) * 2');
    }

    public function test_it_throws_exception_on_invalid_characters(): void
    {
        $this->expectException(UnknownMathematicalOperatorException::class);
        Decimal::resolve('5 + a * 2');
    }

    public function test_it_throws_exception_on_empty_expression(): void
    {
        $this->expectException(InvalidExpressionException::class);
        Decimal::resolve('   ');
    }
}
