<?php

namespace OriolSegura\Decimal\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use OriolSegura\Decimal\Decimal;

class DecimalCastTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->decimal('price_decimal', total: 20, places: 4)->nullable();
            $table->double('price_double')->nullable();
        });
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    public function test_it_casts_stored_value_to_decimal_object(): void
    {
        $product = Product::create([
            'price_decimal' => '19.99',
        ]);

        // Assert that the price is cast to a Decimal instance
        $this->assertInstanceOf(Decimal::class, $product->price_decimal);

        // Assert that the value is correct
        $this->assertSame('19.99', (string) $product->price_decimal);

        // Assert that the scale is correct
        $this->assertSame(2, $product->price_decimal->getScale());
    }

    public function test_it_can_perform_operations_directly_on_eloquent_attributes(): void
    {
        $product = Product::create([
            'price_decimal' => '10.00',
        ]);

        // Perform an addition operation directly on the price attribute
        $newPrice = $product->price_decimal->add('5.50');

        // Assert that the result is a Decimal instance
        $this->assertInstanceOf(Decimal::class, $newPrice);

        // Assert that the value is correct
        $this->assertSame('15.5', (string) $newPrice);

        // Assert that the scale is correct
        $this->assertSame(1, $newPrice->getScale());
    }

    public function test_it_saves_decimal_object_back_to_database(): void
    {
        $product = Product::create([
            'price_decimal' => '10.00',
        ]);

        $product->price_decimal = Decimal::from('25.50');
        $product->save();

        $this->assertDatabaseHas('products', [
            'id'            => $product->id,
            'price_decimal' => '25.5000',
        ]);
    }

    public function test_it_handles_null_values(): void
    {
        $product = Product::create([
            'price_decimal' => null,
        ]);

        $this->assertNull($product->price_decimal);
    }

    public function it_serializes_to_json_correctly(): void
    {
        $product = Product::create([
            'price_decimal' => '99.99',
        ]);

        $json = $product->toJson();
        $this->assertStringContainsString('"price_decimal":"99.99"', $json);

        $array = $product->toArray();
        $this->assertSame('99.99', $array['price_decimal']);
    }

    public function test_it_proves_superiority_over_native_doubles(): void
    {
        $product = Product::create([
            'price_decimal' => '0.1',
            'price_double'  => 0.1,
        ]);

        // IEEE 754 cannot represent 0.1 exactly, so adding 0.2 does not give 0.3, but 0.30000000000000004
        $doubleResult = $product->price_double + 0.2;
        $this->assertNotEquals(0.3, $doubleResult);
        $this->assertEquals(0.30000000000000004, $doubleResult);

        $decimalResult = $product->price_decimal->add('0.2');
        $this->assertEquals('0.3', (string) $decimalResult);
        $this->assertSame(1, $decimalResult->getScale());
    }
}

class Product extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'price_decimal' => Decimal::class,
        'price_double'  => 'double',
    ];
}
