<?php

declare(strict_types=1);

namespace OriolSegura\Decimal\Tests\Feature;

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
    }
}
