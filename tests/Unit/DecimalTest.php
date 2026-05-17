<?php

declare(strict_types=1);

namespace OriolSegura\Decimal\Tests\Unit;

use InvalidArgumentException;
use OriolSegura\Decimal\Decimal;
use OriolSegura\Decimal\Exceptions\DivisionByZeroException;
use OriolSegura\Decimal\Exceptions\InvalidExpressionException;
use OriolSegura\Decimal\Exceptions\UnknownMathematicalOperatorException;
use OriolSegura\Decimal\Exceptions\WrongDecimalFormatException;
use PHPUnit\Framework\TestCase;

class DecimalTest extends TestCase
{
    public function test_it_triggers_deprecation_warning_when_passing_null_to_from(): void
    {
        $triggered = false;
        set_error_handler(function ($errno, $errstr) use (&$triggered): bool {
            if ($errno === E_USER_DEPRECATED && str_contains($errstr, 'Passing null to Decimal::from() is deprecated')) {
                $triggered = true;
            }

            return true;
        });

        $decimal = Decimal::from(null);
        $this->assertSame('0', (string) $decimal);

        restore_error_handler();

        $this->assertTrue($triggered, 'Deprecation warning was not triggered');
    }

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

        // Case 5: From null
        $d4 = Decimal::parse(null);
        $this->assertSame('0', (string) $d4);

        // Case 6: Parse with value
        $this->assertSame('5', Decimal::parse('5')->toString());

        // Case 7: Trim input
        $this->assertSame('10.5', Decimal::from('  10.5  ')->toString());
    }

    public function test_it_solves_simple_floating_point_problem(): void
    {
        $a = Decimal::from('0.1');
        $b = Decimal::from('0.2');

        // In IEEE 754, 0.1 + 0.2 results in 0.30000000000000004
        $result = $a->plus($b);

        $this->assertSame('0.3', (string) $result);
        $this->assertSame(1, $result->getScale());
    }

    public function test_it_calculates_absolute_value(): void
    {
        $negative = Decimal::from('-42.75');
        $this->assertSame('42.75', $negative->abs()->toString());

        $positive = Decimal::from('42.75');
        $this->assertSame('42.75', $positive->abs()->toString());

        $zero = Decimal::from('0');
        $this->assertSame('0', $zero->abs()->toString());
    }

    public function test_it_handles_immutability(): void
    {
        $original = Decimal::from('10');

        $new = $original->plus('5');

        $this->assertSame('10', (string) $original);
        $this->assertSame('15', (string) $new);

        $this->assertNotSame($original, $new);
    }

    public function test_it_serializes_to_json_correctly(): void
    {
        $decimal = Decimal::from('123.45');

        $this->assertSame('123.45', $decimal->jsonSerialize());
        $this->assertSame('"123.45"', json_encode($decimal));
    }

    public function test_it_throws_exception_on_invalid_format(): void
    {
        $this->expectException(WrongDecimalFormatException::class);
        $this->expectExceptionMessage('Wrong decimal format given: [abc]');

        Decimal::from('abc');
    }

    public function test_it_rejects_invalid_prefixes_and_suffixes(): void
    {
        try {
            Decimal::from('abc1.23');
            $this->fail('Should have thrown WrongDecimalFormatException');
        } catch (WrongDecimalFormatException $e) {
            $this->assertStringContainsString('Wrong decimal format given: [abc1.23]', $e->getMessage());
        }

        try {
            Decimal::from('1.23abc');
            $this->fail('Should have thrown WrongDecimalFormatException');
        } catch (WrongDecimalFormatException $e) {
            $this->assertStringContainsString('Wrong decimal format given: [1.23abc]', $e->getMessage());
        }
    }

    public function test_it_truncates_values_correctly(): void
    {
        $this->assertSame('12.34', Decimal::from('12.349')->truncate(2)->toString());
        $this->assertSame('12', Decimal::from('12.99')->truncate(0)->toString());

        $this->assertSame('-12.34', Decimal::from('-12.349')->truncate(2)->toString());
        $this->assertSame('-12', Decimal::from('-12.99')->truncate(0)->toString());

        $this->assertSame('0', Decimal::from('0.99')->truncate(0)->toString());
        $this->assertSame('0', Decimal::from('0')->truncate(2)->toString());

        // Default scale
        $this->assertSame('10', Decimal::from('10.55')->truncate()->toString());
    }

    public function test_truncate_throws_exception_on_negative_scale(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Decimal::from('10.5')->truncate(-1);
    }

    public function test_it_rounds_up_values_correctly(): void
    {
        $this->assertSame('13', Decimal::from('12.01')->roundUp(0)->toString());
        $this->assertSame('12.35', Decimal::from('12.341')->roundUp(2)->toString());

        $this->assertSame('12.34', Decimal::from('12.340')->roundUp(2)->toString());

        $this->assertSame('-12', Decimal::from('-12.99')->roundUp(0)->toString());
        $this->assertSame('-12.34', Decimal::from('-12.349')->roundUp(2)->toString());
        $this->assertSame('-12.34', Decimal::from('-12.340')->roundUp(2)->toString());

        $this->assertSame('0', Decimal::from('0')->roundUp(0)->toString());
        $this->assertSame('0', Decimal::from('0')->roundUp(2)->toString());

        // Default scale
        $this->assertSame('11', Decimal::from('10.55')->roundUp()->toString());

        // Same or higher scale returns same object
        $d = Decimal::from('10.55');
        $this->assertSame($d, $d->roundUp(2));
        $this->assertSame($d, $d->roundUp(5));

        // Precision check
        $this->assertSame('11', Decimal::from('10.000000000000000000000000000000000000001')->roundUp(0)->toString());
        $this->assertSame('10.6', Decimal::from('10.55')->roundUp(1)->toString());
    }

    public function test_round_up_throws_exception_on_negative_scale(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Decimal::from('10.5')->roundUp(-1);
    }

    public function test_exception_messages(): void
    {
        $this->assertSame('Attempt to divide by zero', (new DivisionByZeroException())->getMessage());
        $this->assertSame('Invalid mathematical expression', (new InvalidExpressionException())->getMessage());
        $this->assertSame('Unknown operator: %', (new UnknownMathematicalOperatorException('%'))->getMessage());

        $this->assertSame('Wrong decimal format given: [array]', (new WrongDecimalFormatException(['a']))->getMessage());
        $this->assertSame('Wrong decimal format given: []', (new WrongDecimalFormatException(false))->getMessage());
    }
}
