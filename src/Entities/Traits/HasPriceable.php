<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Request;
use Modules\SystemPricing\Entities\Price;
use Money\Currency;
use Oobook\Priceable\Traits\HasPriceable as TraitsHasPriceable;

trait HasPriceable
{
    use TraitsHasPriceable;

    /**
     * Boot the trait.
     *
     * Sets up event listeners for model creation, updating, retrieval, and deletion.
     *
     * @return void
     */
    public static function bootHasPriceable()
    {
        // parent::bootHasPriceable();
    }

    public function initializeHasPriceable()
    {
        $this->append(
            'base_price_raw', // price excluding vat
            'base_price_vat_percentage', // price vat percentage
            'base_price_vat_amount', // price vat amount
            'base_price_has_discount', // price has discount
            'base_price_raw_discount', // price raw discount
            'base_price_sub_total', // price sub total
            'base_price_total_discount', // price total discount
            'base_price_total', // price total

            'base_price_formatted', // price excluding vat formatted (+ VAT)

            'base_price_raw_formatted', // price excluding vat formatted
            'base_price_vat_percentage_formatted', // price vat percentage formatted
            'base_price_vat_amount_formatted', // price vat amount formatted
            'base_price_discount_percentage_formatted', // price discount percentage formatted
            'base_price_raw_discount_formatted', // price raw discount formatted
            'base_price_sub_total_formatted', // price sub total formatted
            'base_price_total_discount_formatted', // price total discount formatted
            'base_price_total_formatted' // price total formatted
        );
    }

    public function prices(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Price::class, 'priceable');
    }

    public function basePrice(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        // dd(Request::getUserCurrency());
        // return $this->prices()->where('currency_id', Request::getUserCurrency()->id);
        return $this->morphOne(Price::class, 'priceable')
            ->where('currency_id', Request::getUserCurrency()->id);
    }

    protected function basePriceRaw(): Attribute
    {
        return Attribute::make(
            // get: fn ($value) => $this->basePrice ? $this->basePrice->price_excluding_vat : null,
            get: fn ($value) => $this->basePrice ? $this->basePrice->raw_price : null,
        );
    }

    protected function basePriceVatPercentage(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice ? $this->basePrice->vat_percentage : null,
        );
    }

    protected function basePriceVatAmount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice ? $this->basePrice->vat_amount : null,
        );
    }

    protected function basePriceHasDiscount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice ? $this->basePrice->has_discount : false,
        );
    }

    protected function basePriceRawDiscount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice ? $this->basePrice->raw_discount : null,
        );
    }

    protected function basePriceSubTotal(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice ? $this->basePrice->sub_total_price : null,
        );
    }

    protected function basePriceTotalDiscount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice ? $this->basePrice->total_discount : null,
        );
    }

    protected function basePriceTotal(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice ? $this->basePrice->total_price : null,
        );
    }

    protected function basePriceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->base_price_raw
                ? \Oobook\Priceable\Facades\PriceService::formatAmount($this->base_price_raw) . (config('priceable.prices_are_including_vat') ? '' : ' +' . __('VAT'))
                : null,
        );
    }

    protected function basePriceRawFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->base_price_raw
                ? \Oobook\Priceable\Facades\PriceService::formatAmount($this->base_price_raw)
                : null,
        );
    }

    protected function basePriceVatPercentageFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice && $this->basePrice->vat_percentage > 0 ? $this->basePrice->vat_percentage . '%' : '',
        );
    }

    protected function basePriceVatAmountFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->base_price_vat_amount
                ? \Oobook\Priceable\Facades\PriceService::formatAmount($this->base_price_vat_amount)
                : null,
        );
    }

    protected function basePriceDiscountPercentageFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice && $this->basePrice->discount_percentage > 0 ? $this->basePrice->discount_percentage . '%' : '',
        );
    }

    protected function basePriceRawDiscountFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->base_price_raw_discount
                ? \Oobook\Priceable\Facades\PriceService::formatAmount($this->base_price_raw_discount)
                : null,
        );
    }

    protected function basePriceSubTotalFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->base_price_sub_total
                ? \Oobook\Priceable\Facades\PriceService::formatAmount($this->base_price_sub_total)
                : null,
        );
    }

    protected function basePriceTotalDiscountFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->base_price_total_discount
                ? \Oobook\Priceable\Facades\PriceService::formatAmount($this->base_price_total_discount)
                : null,
        );
    }

    protected function basePriceTotalFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->base_price_total
                ? \Oobook\Priceable\Facades\PriceService::formatAmount($this->base_price_total)
                : null,
        );
    }
}
