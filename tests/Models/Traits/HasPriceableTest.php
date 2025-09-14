<?php

namespace Unusualify\Modularity\Tests\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Modules\SystemPricing\Entities\Price;
use Oobook\Priceable\Facades\PriceService;
use Oobook\Priceable\Models\Currency as PriceableCurrency;
use Oobook\Priceable\Models\PriceType;
use Oobook\Priceable\Models\VatRate;
use Unusualify\Modularity\Entities\Traits\HasPriceable;
use Unusualify\Modularity\Tests\ModelTestCase;

class HasPriceableTest extends ModelTestCase
{
    use RefreshDatabase;

    protected $model;
    protected $currency;
    protected $vatRate;
    protected $priceType;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test model table
        Schema::create('test_priceable_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Create test model instance
        $this->model = new TestPriceableModel(['name' => 'Test Priceable Model']);
        $this->model->save();

        // Create test currency and VAT rate
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

        // Set default configuration
        Config::set([
            'priceable.defaults.currencies' => $this->currency->id,
            'priceable.defaults.vat_rates' => $this->vatRate->id,
            'priceable.defaults.price_type' => $this->priceType->id,
            'priceable.prices_are_including_vat' => false,
        ]);

        // Mock Request::getUserCurrency() for basePrice relationship
        // Request::shouldReceive('getUserCurrency')
        //     ->andReturn($this->currency);
    }

    public function test_trait_initialization()
    {
        // Test that the trait is properly used
        $this->assertTrue(in_array(
            HasPriceable::class,
            class_uses_recursive($this->model)
        ));

        // Test that both base trait and mutators are included
        $this->assertTrue(in_array(
            \Oobook\Priceable\Traits\HasPriceable::class,
            class_uses_recursive($this->model)
        ));

        $this->assertTrue(in_array(
            \Unusualify\Modularity\Entities\Mutators\HasPriceableMutators::class,
            class_uses_recursive($this->model)
        ));
    }

    public function test_appended_attributes()
    {
        $expectedAppends = [
            'base_price_vat_percentage',
            'base_price_has_discount',
            'base_price_subtotal_amount',
            'base_price_raw_amount',
            'base_price_raw_discount_amount',
            'base_price_discounted_raw_amount',
            'base_price_vat_amount',
            'base_price_vat_discount_amount',
            'base_price_discounted_vat_amount',
            'base_price_total_discount_amount',
            'base_price_total_amount',
            'base_price_vat_percentage_formatted',
            'base_price_discount_percentage_formatted',
            'base_price_subtotal_amount_formatted',
            'base_price_raw_amount_formatted',
            'base_price_vat_amount_formatted',
            'base_price_raw_discount_amount_formatted',
            'base_price_vat_discount_amount_formatted',
            'base_price_discounted_raw_amount_formatted',
            'base_price_discounted_vat_amount_formatted',
            'base_price_total_discount_amount_formatted',
            'base_price_total_amount_formatted',
            'base_price_formatted',
        ];

        foreach ($expectedAppends as $attribute) {
            $this->assertContains($attribute, $this->model->getAppends());
        }
    }

    public function test_prices_relationship()
    {
        // Test the morphMany relationship
        $relationship = $this->model->prices();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $relationship);
        $this->assertEquals(Price::class, get_class($relationship->getRelated()));
    }

    public function test_base_price_relationship()
    {
        // Create prices with different currencies
        $usdPrice = $this->model->prices()->create([
            'role' => 'base',
            'raw_amount' => 100000, // $1000.00
            'price_including_vat' => 120000, // $1200.00
            'currency_id' => $this->currency->id,
            'vat_rate_id' => $this->vatRate->id,
            'price_type_id' => $this->priceType->id,
        ]);

        // Create another currency and price
        $eurCurrency = PriceableCurrency::create([
            'name' => 'Euro',
            'iso_4217' => 'EUR',
            'symbol' => '€',
        ]);

        $eurPrice = $this->model->prices()->create([
            'role' => 'base',
            'raw_amount' => 90000, // €900.00
            'price_including_vat' => 108000, // €1080.00
            'currency_id' => $eurCurrency->id,
            'vat_rate_id' => $this->vatRate->id,
            'price_type_id' => $this->priceType->id,
        ]);

        // Test that basePrice returns the price for the user's currency (USD)
        $basePrice = $this->model->basePrice;
        $this->assertNotNull($basePrice);
        $this->assertEquals($usdPrice->id, $basePrice->id);
        $this->assertEquals($this->currency->id, $basePrice->currency_id);
    }

    public function test_scope_has_base_price()
    {
        // Create model without base price
        $modelWithoutPrice = new TestPriceableModel(['name' => 'No Price Model']);
        $modelWithoutPrice->save();

        // Create model with base price
        $modelWithPrice = new TestPriceableModel(['name' => 'With Price Model']);
        $modelWithPrice->save();

        $modelWithPrice->prices()->create([
            'role' => 'base',
            'raw_amount' => 50000,
            'price_including_vat' => 60000,
            'currency_id' => $this->currency->id,
            'vat_rate_id' => $this->vatRate->id,
            'price_type_id' => $this->priceType->id,
        ]);

        // Test scope
        $modelsWithBasePrice = TestPriceableModel::hasBasePrice()->get();
        $this->assertCount(1, $modelsWithBasePrice);
        $this->assertEquals($modelWithPrice->id, $modelsWithBasePrice->first()->id);
    }

    public function test_scope_order_by_currency_price()
    {
        TestPriceableModel::truncate();
        $priceSavingKey = Price::$priceSavingKey;

        // Create multiple models with different prices
        $model1 = new TestPriceableModel(['name' => 'Model 1']);
        $model1->save();
        $model1->prices()->create([
            'role' => 'base',
            $priceSavingKey => 300000, // $3000.00
            'currency_id' => $this->currency->id,
            'vat_rate_id' => $this->vatRate->id,
            'price_type_id' => $this->priceType->id,
        ]);

        sleep(1);

        $model2 = new TestPriceableModel(['name' => 'Model 2']);
        $model2->save();
        $model2->prices()->create([
            'role' => 'base',
            $priceSavingKey => 100000, // $1000.00
            'currency_id' => $this->currency->id,
            'vat_rate_id' => $this->vatRate->id,
            'price_type_id' => $this->priceType->id,
        ]);

        sleep(1);

        $model3 = new TestPriceableModel(['name' => 'Model 3']);
        $model3->save();
        $model3->prices()->create([
            'role' => 'base',
            $priceSavingKey => 200000, // $2000.00
            'currency_id' => $this->currency->id,
            'vat_rate_id' => $this->vatRate->id,
            'price_type_id' => $this->priceType->id,
        ]);

        // Test ascending order
        $modelsAsc = TestPriceableModel::orderByCurrencyPrice($this->currency->id, 'asc', 'base')->get();
        $this->assertEquals($model2->id, $modelsAsc[0]->id); // $1000
        $this->assertEquals($model3->id, $modelsAsc[1]->id); // $2000
        $this->assertEquals($model1->id, $modelsAsc[2]->id); // $3000

        // Test descending order
        $modelsDesc = TestPriceableModel::orderByCurrencyPrice($this->currency->id, 'desc')->get();
        $this->assertEquals($model1->id, $modelsDesc[0]->id); // $3000
        $this->assertEquals($model3->id, $modelsDesc[1]->id); // $2000
        $this->assertEquals($model2->id, $modelsDesc[2]->id); // $1000
    }

    public function test_scope_order_by_currency_price_with_role()
    {
        TestPriceableModel::truncate();
        $priceSavingKey = Price::$priceSavingKey;

        // Create model with multiple price roles
        $model = new TestPriceableModel(['name' => 'Multi Role Model']);
        $model->save();

        $model->prices()->create([
            'role' => 'base',
            $priceSavingKey => 100000,
            'currency_id' => $this->currency->id,
            'vat_rate_id' => $this->vatRate->id,
            'price_type_id' => $this->priceType->id,
        ]);

        $model->prices()->create([
            'role' => 'premium',
            $priceSavingKey => 200000,
            'currency_id' => $this->currency->id,
            'vat_rate_id' => $this->vatRate->id,
            'price_type_id' => $this->priceType->id,
        ]);

        // Test ordering by specific role
        $modelsWithPremiumRole = TestPriceableModel::orderByCurrencyPrice($this->currency->id, 'asc', 'premium')->get();
        $this->assertCount(1, $modelsWithPremiumRole);
        $this->assertEquals($model->id, $modelsWithPremiumRole->first()->id);
    }

    public function test_scope_order_by_base_price()
    {
        TestPriceableModel::truncate();
        $priceSavingKey = Price::$priceSavingKey;

        // Create models with different base prices
        $model1 = new TestPriceableModel(['name' => 'Model 1']);
        $model1->save();
        $model1->prices()->create([
            'role' => 'base',
            $priceSavingKey => 150000,
            'currency_id' => $this->currency->id,
            'vat_rate_id' => $this->vatRate->id,
            'price_type_id' => $this->priceType->id,
        ]);

        $model2 = new TestPriceableModel(['name' => 'Model 2']);
        $model2->save();
        $model2->prices()->create([
            'role' => 'base',
            $priceSavingKey => 50000,
            'currency_id' => $this->currency->id,
            'vat_rate_id' => $this->vatRate->id,
            'price_type_id' => $this->priceType->id,
        ]);

        // Test ordering by base price (should use user currency)
        $modelsOrdered = TestPriceableModel::orderByBasePrice('asc')->get();
        $this->assertEquals($model2->id, $modelsOrdered->first()->id); // Lower price first
        $this->assertEquals($model1->id, $modelsOrdered->last()->id); // Higher price last
    }

    public function test_base_price_mutator_attributes_without_price()
    {
        // Test attributes when no base price exists
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

        // Test formatted attributes
        $this->assertEmpty($this->model->base_price_discount_percentage_formatted);
        $this->assertEmpty($this->model->base_price_vat_percentage_formatted);
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

    public function test_base_price_mutator_attributes_with_price()
    {
        // Create a base price with discount
        $priceSavingKey = Price::$priceSavingKey;
        $basePrice = $this->model->prices()->create([
            'role' => 'base',
            $priceSavingKey => 100.00, // $100.00
            'discount_percentage' => 10.0, // 10% discount
            'currency_id' => $this->currency->id,
            'vat_rate_id' => $this->vatRate->id,
            'price_type_id' => $this->priceType->id,
        ]);

        // Refresh model to load relationship
        $this->model->refresh();

        // Test basic attributes
        $this->assertEquals(20.0, $this->model->base_price_vat_percentage);
        $this->assertTrue($this->model->base_price_has_discount);
        $this->assertEquals(10000, $this->model->base_price_raw_amount); // $100.00 in cents
        $this->assertEquals(2000, $this->model->base_price_vat_amount); // 20% VAT
        $this->assertEquals(10800, $this->model->base_price_total_amount); // $120.00 total

        // Test discount attributes
        $this->assertEquals(1000, $this->model->base_price_raw_discount_amount); // 10% of $100
        $this->assertEquals(9000, $this->model->base_price_discounted_raw_amount); // $90.00
        $this->assertEquals(200, $this->model->base_price_vat_discount_amount); // VAT on discount
        $this->assertEquals(1800, $this->model->base_price_discounted_vat_amount); // VAT on discounted price
        $this->assertEquals(1200, $this->model->base_price_total_discount_amount); // Total discount

        // Test formatted attributes
        $this->assertEquals('10%', $this->model->base_price_discount_percentage_formatted);
        $this->assertEquals('20%', $this->model->base_price_vat_percentage_formatted);
        $this->assertNotNull($this->model->base_price_raw_amount_formatted);
        $this->assertNotNull($this->model->base_price_total_amount_formatted);
        $this->assertNotNull($this->model->base_price_formatted);
    }

    public function test_base_price_formatted_attributes_with_price_service()
    {
        // Create a base price
        $priceSavingKey = Price::$priceSavingKey;
        $basePrice = $this->model->prices()->create([
            'role' => 'base',
            $priceSavingKey => 150.00, // $150.00
            'currency_id' => $this->currency->id,
            'vat_rate_id' => $this->vatRate->id,
            'price_type_id' => $this->priceType->id,
        ]);

        $this->model->refresh();

        // Test that formatted attributes use PriceService
        $expectedRawFormatted = PriceService::formatAmount($this->model->base_price_raw_amount);
        $expectedTotalFormatted = PriceService::formatAmount($this->model->base_price_total_amount);

        $this->assertEquals($expectedRawFormatted, $this->model->base_price_raw_amount_formatted);
        $this->assertEquals($expectedTotalFormatted, $this->model->base_price_total_amount_formatted);

        // Test base_price_formatted with VAT indication
        $expectedBaseFormatted = $expectedRawFormatted . ' +' . __('VAT');
        $this->assertEquals($expectedBaseFormatted, $this->model->base_price_formatted);
    }

    public function test_base_price_formatted_with_vat_included_config()
    {
        // Set configuration to prices including VAT
        Config::set('priceable.prices_are_including_vat', true);

        $priceSavingKey = Price::$priceSavingKey;
        $basePrice = $this->model->prices()->create([
            'role' => 'base',
            $priceSavingKey => 120.00, // $120.00 including VAT
            'currency_id' => $this->currency->id,
            'vat_rate_id' => $this->vatRate->id,
            'price_type_id' => $this->priceType->id,
        ]);

        $this->model->refresh();

        // Test that base_price_formatted doesn't include "+VAT" when prices include VAT
        $expectedFormatted = PriceService::formatAmount($this->model->base_price_raw_amount);
        $this->assertEquals($expectedFormatted, $this->model->base_price_formatted);
        $this->assertStringNotContainsString('+' . __('VAT'), $this->model->base_price_formatted);
    }

    public function test_base_price_mutator_attributes_with_no_discount()
    {
        // Create a base price without discount
        $priceSavingKey = Price::$priceSavingKey;
        $basePrice = $this->model->prices()->create([
            'role' => 'base',
            $priceSavingKey => 200.00, // $200.00
            'discount_percentage' => 0.0, // No discount
            'currency_id' => $this->currency->id,
            'vat_rate_id' => $this->vatRate->id,
            'price_type_id' => $this->priceType->id,
        ]);

        $this->model->refresh();

        // Test discount-related attributes
        $this->assertFalse($this->model->base_price_has_discount);
        $this->assertEquals('', $this->model->base_price_discount_percentage_formatted);
        $this->assertEquals(0, $this->model->base_price_raw_discount_amount);
        $this->assertEquals(20000, $this->model->base_price_discounted_raw_amount); // Same as raw amount
        $this->assertEquals(0, $this->model->base_price_vat_discount_amount);
        $this->assertEquals(4000, $this->model->base_price_discounted_vat_amount); // Same as vat amount
        $this->assertEquals(0, $this->model->base_price_total_discount_amount);
    }

    public function test_integration_with_original_priceable_trait()
    {
        // Create a base price
        $priceSavingKey = Price::$priceSavingKey;
        $basePrice = $this->model->prices()->create([
            'role' => 'base',
            $priceSavingKey => 100.00,
            'currency_id' => $this->currency->id,
            'vat_rate_id' => $this->vatRate->id,
            'price_type_id' => $this->priceType->id,
        ]);

        $this->model->refresh();

        // Test that original trait methods still work
        $this->assertTrue($this->model->hasPrice());
        $this->assertNotNull($this->model->price());

        // Test price attribute from original trait
        $this->assertNotNull($this->model->price);
        $this->assertNotNull($this->model->price_formatted);
    }

    public function test_multiple_prices_with_different_roles()
    {
        $priceSavingKey = Price::$priceSavingKey;

        // Create multiple prices with different roles
        $basePrice = $this->model->prices()->create([
            'role' => 'base',
            $priceSavingKey => 100.00,
            'currency_id' => $this->currency->id,
            'vat_rate_id' => $this->vatRate->id,
            'price_type_id' => $this->priceType->id,
        ]);

        $premiumPrice = $this->model->prices()->create([
            'role' => 'premium',
            $priceSavingKey => 200.00,
            'currency_id' => $this->currency->id,
            'vat_rate_id' => $this->vatRate->id,
            'price_type_id' => $this->priceType->id,
        ]);

        $salePrice = $this->model->prices()->create([
            'role' => 'sale',
            $priceSavingKey => 80.00,
            'discount_percentage' => 20.0,
            'currency_id' => $this->currency->id,
            'vat_rate_id' => $this->vatRate->id,
            'price_type_id' => $this->priceType->id,
        ]);

        $this->model->refresh();

        // Test that mutator attributes use the base price (based on user currency)
        $this->assertEquals(10000, $this->model->base_price_raw_amount); // Base price
        $this->assertFalse($this->model->base_price_has_discount); // Base price has no discount

        // Test that all prices are accessible through relationship
        $this->assertCount(3, $this->model->prices);
        $this->assertTrue($this->model->prices->contains('role', 'base'));
        $this->assertTrue($this->model->prices->contains('role', 'premium'));
        $this->assertTrue($this->model->prices->contains('role', 'sale'));
    }

    public function test_complex_pricing_scenario()
    {
        $priceSavingKey = Price::$priceSavingKey;

        // Create a complex price with discount and high VAT
        $highVatRate = VatRate::create([
            'name' => 'High VAT',
            'slug' => 'high-vat',
            'rate' => 25.0, // 25% VAT
        ]);

        $complexPrice = $this->model->prices()->create([
            'role' => 'base',
            $priceSavingKey => 500.00, // $500.00
            'discount_percentage' => 15.0, // 15% discount
            'currency_id' => $this->currency->id,
            'vat_rate_id' => $highVatRate->id,
            'price_type_id' => $this->priceType->id,
        ]);

        $this->model->refresh();

        // Test complex calculations
        $this->assertEquals(25.0, $this->model->base_price_vat_percentage);
        $this->assertTrue($this->model->base_price_has_discount);
        $this->assertEquals(50000, $this->model->base_price_raw_amount); // $500.00
        $this->assertEquals(12500, $this->model->base_price_vat_amount); // 25% of $500
        $this->assertEquals(53125, $this->model->base_price_total_amount); // $531.25

        // Test discount calculations
        $this->assertEquals(7500, $this->model->base_price_raw_discount_amount); // 15% of $500
        $this->assertEquals(42500, $this->model->base_price_discounted_raw_amount); // $425.00
        $this->assertEquals(1875, $this->model->base_price_vat_discount_amount); // VAT on discount
        $this->assertEquals(10625, $this->model->base_price_discounted_vat_amount); // VAT on discounted price
        $this->assertEquals(9375, $this->model->base_price_total_discount_amount); // Total discount

        // Test formatted percentages
        $this->assertEquals('15%', $this->model->base_price_discount_percentage_formatted);
        $this->assertEquals('25%', $this->model->base_price_vat_percentage_formatted);
    }

    public function test_edge_cases_and_null_handling()
    {
        // Test with zero values
        $priceSavingKey = Price::$priceSavingKey;
        $zeroPrice = $this->model->prices()->create([
            'role' => 'base',
            $priceSavingKey => 0.00,
            'discount_percentage' => 0.0,
            'currency_id' => $this->currency->id,
            'vat_rate_id' => $this->vatRate->id,
            'price_type_id' => $this->priceType->id,
        ]);

        $this->model->refresh();

        // Test zero price handling
        $this->assertEquals(0, $this->model->base_price_raw_amount);
        $this->assertEquals(0, $this->model->base_price_vat_amount);
        $this->assertEquals(0, $this->model->base_price_total_amount);
        $this->assertFalse($this->model->base_price_has_discount);
        $this->assertEquals('', $this->model->base_price_discount_percentage_formatted);
        $this->assertEquals('20%', $this->model->base_price_vat_percentage_formatted); // VAT rate still shows
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}

// Basic test model that uses HasPriceable trait
class TestPriceableModel extends Model
{
    use HasPriceable;

    protected $table = 'test_priceable_models';
    protected $fillable = ['name'];

    public static $priceSavingKey = 'price_value';
}
