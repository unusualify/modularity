<?php

namespace Unusualify\Modularity\Entities\Mutators;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasPriceableMutators
{
    public function initializeHasPriceableMutators()
    {
        $this->append(
            'base_price_vat_percentage', // price vat percentage
            'base_price_has_discount', // price has discount
            'base_price_subtotal_amount', // price sub total

            'base_price_raw_amount', // price excluding vat
            'base_price_raw_discount_amount', // price raw discount
            'base_price_discounted_raw_amount', // price raw discount

            'base_price_vat_amount', // price vat amount
            'base_price_vat_discount_amount', // price vat discount
            'base_price_discounted_vat_amount', // price vat discount

            'base_price_total_discount_amount', // price total discount
            'base_price_total_amount', // price total

            'base_price_vat_percentage_formatted', // price vat percentage formatted
            'base_price_discount_percentage_formatted', // price discount percentage formatted

            'base_price_subtotal_amount_formatted', // price sub total formatted
            'base_price_raw_amount_formatted', // price excluding vat formatted
            'base_price_vat_amount_formatted', // price vat amount formatted
            'base_price_raw_discount_amount_formatted', // price raw discount formatted
            'base_price_vat_discount_amount_formatted', // price raw discount formatted
            'base_price_discounted_raw_amount_formatted', // price raw discount formatted
            'base_price_discounted_vat_amount_formatted', // price vat discount formatted
            'base_price_total_discount_amount_formatted', // price total discount formatted
            'base_price_total_amount_formatted', // price total formatted

            'base_price_formatted' // price excluding vat formatted (+ VAT)
        );
    }

    protected function basePriceVatPercentage(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice ? $this->basePrice->vat_percentage : null,
        );
    }

    protected function basePriceHasDiscount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice ? $this->basePrice->has_discount : false,
        );
    }

    protected function basePriceSubtotalAmount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice ? $this->basePrice->subtotal_amount : null,
        );
    }

    protected function basePriceRawAmount(): Attribute
    {
        return Attribute::make(
            // get: fn ($value) => $this->basePrice ? $this->basePrice->price_excluding_vat : null,
            get: fn ($value) => $this->basePrice ? $this->basePrice->raw_amount : null,
        );
    }

    protected function basePriceRawDiscountAmount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice ? $this->basePrice->raw_discount_amount : null,
        );
    }

    protected function basePriceDiscountedRawAmount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice ? $this->basePrice->discounted_raw_amount : null,
        );
    }

    protected function basePriceVatAmount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice ? $this->basePrice->vat_amount : null,
        );
    }

    protected function basePriceVatDiscountAmount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice ? $this->basePrice->vat_discount_amount : null,
        );
    }

    protected function basePriceDiscountedVatAmount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice ? $this->basePrice->discounted_vat_amount : null,
        );
    }

    protected function basePriceTotalDiscountAmount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice ? $this->basePrice->total_discount_amount : null,
        );
    }

    protected function basePriceTotalAmount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice ? $this->basePrice->total_amount : null,
        );
    }

    // Formatted attributes

    protected function basePriceDiscountPercentageFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice && $this->basePrice->discount_percentage > 0 ? $this->basePrice->discount_percentage . '%' : '',
        );
    }

    protected function basePriceVatPercentageFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice && $this->basePrice->vat_percentage > 0 ? $this->basePrice->vat_percentage . '%' : '',
        );
    }

    protected function basePriceSubtotalAmountFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->base_price_subtotal_amount
                ? \Oobook\Priceable\Facades\PriceService::formatAmount($this->base_price_subtotal_amount)
                : null,
        );
    }

    protected function basePriceRawAmountFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->base_price_raw_amount
                ? \Oobook\Priceable\Facades\PriceService::formatAmount($this->base_price_raw_amount)
                : null,
        );
    }

    protected function basePriceDiscountedRawAmountFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->base_price_discounted_raw_amount
                ? \Oobook\Priceable\Facades\PriceService::formatAmount($this->base_price_discounted_raw_amount)
                : null,
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

    protected function basePriceDiscountedVatAmountFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->base_price_discounted_vat_amount
                ? \Oobook\Priceable\Facades\PriceService::formatAmount($this->base_price_discounted_vat_amount)
                : null,
        );
    }

    protected function basePriceRawDiscountAmountFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->base_price_raw_discount_amount
                ? \Oobook\Priceable\Facades\PriceService::formatAmount($this->base_price_raw_discount_amount)
                : null,
        );
    }

    protected function basePriceVatDiscountAmountFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->base_price_vat_discount_amount
                ? \Oobook\Priceable\Facades\PriceService::formatAmount($this->base_price_vat_discount_amount)
                : null,
        );
    }

    protected function basePriceTotalDiscountAmountFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->base_price_total_discount_amount
                ? \Oobook\Priceable\Facades\PriceService::formatAmount($this->base_price_total_discount_amount)
                : null,
        );
    }

    protected function basePriceTotalAmountFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->base_price_total_amount
                ? \Oobook\Priceable\Facades\PriceService::formatAmount($this->base_price_total_amount)
                : null,
        );
    }

    protected function basePriceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->base_price_raw_amount
                ? \Oobook\Priceable\Facades\PriceService::formatAmount($this->base_price_raw_amount) . (config('priceable.prices_are_including_vat') ? '' : ' +' . __('VAT'))
                : null,
        );
    }
}
