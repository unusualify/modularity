<?php

namespace Modules\SystemPricing\Entities\Mutators;

use Illuminate\Database\Eloquent\Casts\Attribute;


trait PriceMutators
{
    /**
     * This will make sure that the submitted amount in Nova
     * is multiplied by 100 so we can store it in cents.
     * @param [type] $amount [description]
     */
    // protected function setRawPriceAttribute(float $amount)
    // {
    //     $this->attributes['raw_price'] = $amount * 100;
    // }

    public function initializePriceMutators()
    {
        $this->append(
            'discounted_raw_amount',
            'vat_multiplier',
        );
    }

    protected function rawAmount(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $value,
            set: fn ($value) => $value * 100,
        );
    }

    protected function vatPercentage(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->raw_amount > 0 ? ($this->vat_amount / $this->raw_amount) * 100 : $this->vatRate->rate
        );
    }

    protected function vatMultiplier(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->vat_percentage / 100,
        );
    }

    protected function discountMultiplier(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->discount_percentage / 100,
        );
    }

    protected function hasDiscount(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->discount_percentage > 0,
        );
    }

    protected function subtotalAmount(): Attribute
    {
        return new Attribute(
            get: fn ($value) => (int) ($this->raw_amount),
        );
    }

    protected function discountedRawAmount(): Attribute
    {
        return new Attribute(
            get: fn ($value) => (int) round($this->raw_amount * (1 - $this->discount_multiplier) / 100) * 100,
        );
    }

    protected function discountedVatAmount(): Attribute
    {
        return new Attribute(
            get: fn ($value) => (int) ($this->discounted_raw_amount * $this->vat_multiplier),
        );
    }

    protected function rawDiscountAmount(): Attribute
    {
        return new Attribute(
            get: fn ($value) => (int) ($this->raw_amount - $this->discounted_raw_amount),
        );
    }

    protected function vatDiscountAmount(): Attribute
    {
        return new Attribute(
            get: fn ($value) => (int) ($this->vat_amount - $this->discounted_vat_amount),
        );
    }

    protected function totalDiscountAmount(): Attribute
    {
        return new Attribute(
            // get: fn ($value) => (int) (($this->raw_price + $this->vat_amount) * $this->discount_multiplier),
            get: fn ($value) => (int) ($this->raw_discount_amount + $this->vat_discount_amount),
        );
    }

    protected function totalVatAmount(): Attribute
    {
        return new Attribute(
            get: fn ($value) => (int) ($this->vat_amount - $this->vat_amount_discount),
        );
    }

    protected function totalAmountExcludingVat() : Attribute
    {
        return new Attribute(
            get: fn($value) => (int) $this->discounted_raw_amount
        );
    }

    protected function totalAmount(): Attribute
    {
        return new Attribute(
            get: fn ($value) => (int) ($this->discounted_raw_amount + $this->discounted_vat_amount),
        );
    }

    protected function currencyISO4217(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->currency->iso_4217,
        );
    }

    protected function currencyISO4217Number(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->currency->iso_4217_number,
        );
    }

    protected function isPaid(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->payment('COMPLETED')->exists(),
        );
    }

    protected function isUnpaid(): Attribute
    {
        return new Attribute(
            get: fn ($value) => ! $this->isPaid,
        );
    }
}
