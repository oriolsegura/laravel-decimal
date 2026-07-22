<?php

declare(strict_types=1);

namespace OriolSegura\Decimal\Tests\Feature;

use Illuminate\Contracts\Support\Arrayable;
use Orchestra\Testbench\TestCase;
use OriolSegura\Decimal\Decimal;

use function OriolSegura\sum;

class DecimalHelpersTest extends TestCase
{
    public function test_fn_sum(): void
    {
        $array = [
            Decimal::from(fake()->randomNumber()),
            Decimal::from(fake()->randomNumber()),
            Decimal::from(fake()->randomNumber()),
            Decimal::from(fake()->randomNumber()),
            Decimal::from(fake()->randomNumber()),
        ];

        $this->assertEquals(
            Decimal::zero()->sum(...$array),
            sum($array),
        );

        $this->assertEquals(
            Decimal::from(54)->sum(...$array),
            sum($array, initial: 54),
        );

        $collection = collect([
            Decimal::from(fake()->randomNumber()),
            Decimal::from(fake()->randomNumber()),
            Decimal::from(fake()->randomNumber()),
            Decimal::from(fake()->randomNumber()),
            Decimal::from(fake()->randomNumber()),
        ]);

        $this->assertEquals(
            Decimal::zero()->sum(...$collection),
            sum($collection),
        );

        $dto = new OrderTotalDto($array);

        $this->assertEquals(
            Decimal::zero()->sum(...$dto->toArray()),
            sum($dto),
        );
    }
}

readonly class OrderTotalDto implements Arrayable
{
    public function __construct(
        private array $items,
    ) {}

    public function toArray(): array
    {
        return $this->items;
    }
}
