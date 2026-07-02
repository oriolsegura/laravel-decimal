<?php

declare(strict_types=1);

namespace OriolSegura\Decimal\Tests\Unit;

use Illuminate\Support\Collection;
use OriolSegura\Decimal\Decimal;
use PHPUnit\Framework\TestCase;
use stdClass;

class DecimalSumCollectionTest extends TestCase
{
    private function makeItem(string $property, string|int $value): stdClass
    {
        $item            = new stdClass();
        $item->$property = $value;

        return $item;
    }

    public function test_it_sums_a_field_given_as_a_property_name(): void
    {
        $lines = new Collection([
            $this->makeItem('amount', '10.50'),
            $this->makeItem('amount', '5.25'),
            $this->makeItem('amount', '0.25'),
        ]);

        $total = Decimal::collectionSum($lines, 'amount');

        $this->assertInstanceOf(Decimal::class, $total);
        $this->assertSame('16', $total->toString());
    }

    public function test_it_sums_a_field_given_as_a_callable(): void
    {
        $lines = new Collection([
            $this->makeItem('computedAmount', '1.111'),
            $this->makeItem('computedAmount', '2.222'),
        ]);

        $total = Decimal::collectionSum($lines, fn (stdClass $line) => $line->computedAmount);

        $this->assertSame('3.333', $total->toString());
    }

    public function test_it_returns_zero_for_an_empty_collection(): void
    {
        $total = Decimal::collectionSum(new Collection(), 'amount');

        $this->assertSame('0', $total->toString());
    }

    public function test_it_sums_mixed_value_types(): void
    {
        $lines = new Collection([
            $this->makeItem('amount', 10),
            $this->makeItem('amount', '5.5'),
        ]);

        $total = Decimal::collectionSum($lines, 'amount');

        $this->assertSame('15.5', $total->toString());
    }

    public function test_it_preserves_the_largest_scale_among_summed_values(): void
    {
        $lines = new Collection([
            $this->makeItem('amount', '1'),
            $this->makeItem('amount', '2.1234'),
        ]);

        $total = Decimal::collectionSum($lines, 'amount');

        $this->assertSame('3.1234', $total->toString());
    }
}
