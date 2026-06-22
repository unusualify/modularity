<?php

namespace Unusualify\Modularous\Tests\Entities\Observers;

use Illuminate\Support\Facades\Config;
use Modules\SystemPricing\Entities\Price;
use Oobook\Priceable\Models\VatRate;
use Unusualify\Modularous\Entities\Observers\PriceableObserver;
use Unusualify\Modularous\Tests\ModelTestCase;

class PriceableObserverTest extends ModelTestCase
{
    protected PriceableObserver $observer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->observer = new PriceableObserver;

        Config::set('priceable.prices_are_including_vat', false);
    }

    /**
     * Build an unsaved Price with the given price_value and VAT rate.
     * The vatRate relation is injected so saving() never hits the database.
     */
    protected function makePrice(float $priceValue, float $vatRate, float $discountPercentage = 0): Price
    {
        $price = new Price;
        $price->setRawAttributes([
            Price::$priceSavingKey => $priceValue,
            'discount_percentage' => $discountPercentage,
        ]);
        $price->setRelation('vatRate', new VatRate(['rate' => $vatRate]));

        return $price;
    }

    // -------------------------------------------------------------------------
    // saving()
    // -------------------------------------------------------------------------

    public function test_saving_sets_raw_and_vat_amounts_when_excluding_vat()
    {
        // $price_value 100 → raw_amount 10000 cents; 20% VAT on top.
        $price = $this->makePrice(100, 20.0);

        $this->observer->saving($price);

        $this->assertEquals(10000, $price->raw_amount);
        $this->assertEquals(2000, $price->vat_amount);   // 10000 * 0.20
    }

    public function test_saving_sets_raw_and_vat_amounts_when_including_vat()
    {
        Config::set('priceable.prices_are_including_vat', true);

        // $price_value 120 → newRawValue 12000 cents (gross incl. 20% VAT).
        // raw_amount = 12000 / 1.20 = 10000; vat_amount = 12000 - 10000 = 2000.
        $price = $this->makePrice(120, 20.0);

        $this->observer->saving($price);

        $this->assertEquals(10000, $price->raw_amount);
        $this->assertEquals(2000, $price->vat_amount);
    }

    public function test_saving_with_zero_price_value_produces_zero_amounts()
    {
        // Price constructor always injects price_value => 0.00 via defaultAttributes(),
        // so isset($price->price_value) is always true. A zero price_value correctly
        // results in raw_amount = 0 and vat_amount = 0.
        $price = new Price;
        $price->setRelation('vatRate', new VatRate(['rate' => 20.0]));

        $this->observer->saving($price);

        $this->assertEquals(0, $price->raw_amount);
        $this->assertEquals(0, $price->vat_amount);
    }

    public function test_saving_unsets_price_saving_key_attribute()
    {
        $price = $this->makePrice(100, 20.0);

        $this->observer->saving($price);

        $this->assertFalse($price->offsetExists(Price::$priceSavingKey));
    }

    public function test_saving_defaults_discount_percentage_to_zero_when_null()
    {
        $price = new Price;
        $price->setRawAttributes([Price::$priceSavingKey => 100]);
        $price->setRelation('vatRate', new VatRate(['rate' => 20.0]));

        $this->observer->saving($price);

        $this->assertEquals(0, $price->discount_percentage);
    }

    public function test_saving_with_zero_vat_rate()
    {
        $price = $this->makePrice(50, 0.0);

        $this->observer->saving($price);

        $this->assertEquals(5000, $price->raw_amount);
        $this->assertEquals(0, $price->vat_amount);
    }

    // -------------------------------------------------------------------------
    // retrieved()
    // -------------------------------------------------------------------------

    public function test_retrieved_sets_price_saving_key_from_raw_amount_when_excluding_vat()
    {
        // Excluding VAT: price_value = raw_amount / 100.
        $price = new Price;
        $price->setRawAttributes(['raw_amount' => 10000, 'vat_amount' => 2000]);

        $this->observer->retrieved($price);

        $this->assertEquals(100.0, $price->getAttribute(Price::$priceSavingKey));
    }

    public function test_retrieved_sets_price_saving_key_from_total_amount_when_including_vat()
    {
        Config::set('priceable.prices_are_including_vat', true);

        // Including VAT: price_value = (raw_amount + vat_amount) / 100.
        $price = new Price;
        $price->setRawAttributes(['raw_amount' => 10000, 'vat_amount' => 2000]);

        $this->observer->retrieved($price);

        $this->assertEquals(120.0, $price->getAttribute(Price::$priceSavingKey));
    }

    // -------------------------------------------------------------------------
    // replicating()
    // -------------------------------------------------------------------------

    public function test_replicating_unsets_price_saving_key()
    {
        $price = new Price;
        $price->setRawAttributes([Price::$priceSavingKey => 100]);

        $this->observer->replicating($price);

        $this->assertFalse($price->offsetExists(Price::$priceSavingKey));
    }
}
