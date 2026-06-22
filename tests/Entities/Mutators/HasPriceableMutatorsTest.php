<?php

namespace Unusualify\Modularous\Tests\Entities\Mutators;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Modules\SystemPricing\Entities\Price;
use Money\Currency as MoneyCurrency;
use Oobook\Priceable\Facades\PriceService;
use Oobook\Priceable\Models\Currency as PriceableCurrency;
use Oobook\Priceable\Models\PriceType;
use Oobook\Priceable\Models\VatRate;
use Unusualify\Modularous\Entities\Mutators\HasPriceableMutators;
use Unusualify\Modularous\Entities\Traits\HasPriceable;
use Unusualify\Modularous\Tests\ModelTestCase;

class HasPriceableMutatorsTest extends ModelTestCase
{
    use RefreshDatabase;

    protected $model;

    protected $currency;

    protected $vatRate;

    protected $priceType;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_mutator_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $this->model = new TestMutatorModel(['name' => 'Test Mutator Model']);
        $this->model->save();

        $this->currency = PriceableCurrency::create([
            'name' => 'US Dollar',
            'iso_4217' => 'USD',
            'symbol' => '$',
        ]);

        $this->vatRate = VatRate::create([
            'name' => 'Standard VAT',
            'slug' => 'standard-vat',
            'rate' => 20.0,
        ]);

        $this->priceType = PriceType::create([
            'name' => 'Standard Price Type',
        ]);

        Config::set([
            'priceable.defaults.currencies' => $this->currency->id,
            'priceable.defaults.vat_rates' => $this->vatRate->id,
            'priceable.defaults.price_type' => $this->priceType->id,
            'priceable.prices_are_including_vat' => false,
        ]);
    }

    protected function createBasePrice(array $overrides = [])
    {
        $priceSavingKey = Price::$priceSavingKey;

        $price = $this->model->prices()->create(array_merge([
            'role' => 'base',
            $priceSavingKey => 100.00,
            'currency_id' => $this->currency->id,
            'vat_rate_id' => $this->vatRate->id,
            'price_type_id' => $this->priceType->id,
        ], $overrides));

        $this->model->refresh();

        return $price;
    }

    protected function usdFormat($amount): string
    {
        return PriceService::formatAmount($amount, new MoneyCurrency('USD'));
    }

    public function test_trait_is_used_by_model()
    {
        $this->assertContains(
            HasPriceableMutators::class,
            class_uses_recursive($this->model)
        );
    }

    public function test_initialize_has_priceable_mutators_does_not_append_attributes()
    {
        // The initializer currently appends nothing (body is commented out).
        $this->assertEmpty($this->model->getAppends());
    }

    public function test_has_language_based_price_always_returns_false()
    {
        // The accessor short-circuits with an early `return false;`.
        $this->assertFalse($this->model->has_language_based_price);

        $this->createBasePrice();

        $this->assertFalse($this->model->has_language_based_price);
    }

    public function test_value_attributes_return_defaults_without_base_price()
    {
        $this->assertNull($this->model->base_price_vat_percentage);
        $this->assertFalse($this->model->base_price_has_discount);
        $this->assertNull($this->model->base_price_subtotal_amount);
        $this->assertNull($this->model->base_price_raw_amount);
        $this->assertNull($this->model->base_price_raw_discount_amount);
        $this->assertNull($this->model->base_price_discounted_raw_amount);
        $this->assertNull($this->model->base_price_vat_amount);
        $this->assertNull($this->model->base_price_vat_discount_amount);
        $this->assertNull($this->model->base_price_discounted_vat_amount);
        $this->assertNull($this->model->base_price_total_discount_amount);
        $this->assertNull($this->model->base_price_total_amount);
    }

    public function test_formatted_attributes_return_defaults_without_base_price()
    {
        $this->assertSame('', $this->model->base_price_discount_percentage_formatted);
        $this->assertSame('', $this->model->base_price_vat_percentage_formatted);
        $this->assertNull($this->model->base_price_subtotal_amount_formatted);
        $this->assertNull($this->model->base_price_raw_amount_formatted);
        $this->assertNull($this->model->base_price_discounted_raw_amount_formatted);
        $this->assertNull($this->model->base_price_vat_amount_formatted);
        $this->assertNull($this->model->base_price_discounted_vat_amount_formatted);
        $this->assertNull($this->model->base_price_raw_discount_amount_formatted);
        $this->assertNull($this->model->base_price_vat_discount_amount_formatted);
        $this->assertNull($this->model->base_price_total_discount_amount_formatted);
        $this->assertNull($this->model->base_price_total_amount_formatted);
        $this->assertNull($this->model->base_price_formatted);
    }

    public function test_value_attributes_with_base_price_and_discount()
    {
        $this->createBasePrice([
            'discount_percentage' => 10.0,
        ]);

        $this->assertEquals(20.0, $this->model->base_price_vat_percentage);
        $this->assertTrue($this->model->base_price_has_discount);
        $this->assertEquals(10000, $this->model->base_price_subtotal_amount);
        $this->assertEquals(10000, $this->model->base_price_raw_amount);
        $this->assertEquals(2000, $this->model->base_price_vat_amount);
        $this->assertEquals(10800, $this->model->base_price_total_amount);

        $this->assertEquals(1000, $this->model->base_price_raw_discount_amount);
        $this->assertEquals(9000, $this->model->base_price_discounted_raw_amount);
        $this->assertEquals(200, $this->model->base_price_vat_discount_amount);
        $this->assertEquals(1800, $this->model->base_price_discounted_vat_amount);
        $this->assertEquals(1200, $this->model->base_price_total_discount_amount);
    }

    public function test_formatted_amount_attributes_use_price_service_with_currency()
    {
        $this->createBasePrice([
            'discount_percentage' => 10.0,
        ]);

        $this->assertEquals(
            $this->usdFormat($this->model->base_price_subtotal_amount),
            $this->model->base_price_subtotal_amount_formatted
        );
        $this->assertEquals(
            $this->usdFormat($this->model->base_price_raw_amount),
            $this->model->base_price_raw_amount_formatted
        );
        $this->assertEquals(
            $this->usdFormat($this->model->base_price_discounted_raw_amount),
            $this->model->base_price_discounted_raw_amount_formatted
        );
        $this->assertEquals(
            $this->usdFormat($this->model->base_price_vat_amount),
            $this->model->base_price_vat_amount_formatted
        );
        $this->assertEquals(
            $this->usdFormat($this->model->base_price_discounted_vat_amount),
            $this->model->base_price_discounted_vat_amount_formatted
        );
        $this->assertEquals(
            $this->usdFormat($this->model->base_price_raw_discount_amount),
            $this->model->base_price_raw_discount_amount_formatted
        );
        $this->assertEquals(
            $this->usdFormat($this->model->base_price_vat_discount_amount),
            $this->model->base_price_vat_discount_amount_formatted
        );
        $this->assertEquals(
            $this->usdFormat($this->model->base_price_total_discount_amount),
            $this->model->base_price_total_discount_amount_formatted
        );
        $this->assertEquals(
            $this->usdFormat($this->model->base_price_total_amount),
            $this->model->base_price_total_amount_formatted
        );
    }

    public function test_percentage_formatted_attributes_with_values()
    {
        $this->createBasePrice([
            'discount_percentage' => 10.0,
        ]);

        $this->assertEquals('10%', $this->model->base_price_discount_percentage_formatted);
        $this->assertEquals('20%', $this->model->base_price_vat_percentage_formatted);
    }

    public function test_percentage_formatted_attributes_empty_when_no_discount()
    {
        $this->createBasePrice([
            'discount_percentage' => 0.0,
        ]);

        $this->assertSame('', $this->model->base_price_discount_percentage_formatted);
        $this->assertEquals('20%', $this->model->base_price_vat_percentage_formatted);
    }

    public function test_base_price_formatted_appends_vat_suffix_by_default()
    {
        $this->createBasePrice();

        $expected = $this->usdFormat($this->model->base_price_raw_amount) . ' +' . __('VAT');

        $this->assertEquals($expected, $this->model->base_price_formatted);
        $this->assertStringContainsString('+' . __('VAT'), $this->model->base_price_formatted);
    }

    public function test_base_price_formatted_omits_vat_suffix_when_prices_include_vat()
    {
        Config::set('priceable.prices_are_including_vat', true);

        $this->createBasePrice();

        $expected = $this->usdFormat($this->model->base_price_raw_amount);

        $this->assertEquals($expected, $this->model->base_price_formatted);
        $this->assertStringNotContainsString('+' . __('VAT'), $this->model->base_price_formatted);
    }
}

class TestMutatorModel extends Model
{
    use HasPriceable;

    public static $mutateHasPriceable = true;

    protected $table = 'test_mutator_models';

    protected $fillable = ['name'];

    public static $priceSavingKey = 'price_value';
}
