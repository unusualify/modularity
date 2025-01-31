<?php

namespace Modules\SystemPricing\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Modules\SystemPayment\Entities\Payment;

class Price extends \Oobook\Priceable\Models\Price
{
    protected function vatRateNumber(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->vatRate->rate,
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
            get: fn ($value) => $this->payments()->exists(),
        );
    }

    protected function isUnpaid(): Attribute
    {
        return new Attribute(
            get: fn ($value) => ! $this->isPaid,
        );
    }

    public function vatRate(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(config('priceable.models.vat'));
    }

    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(config('priceable.models.currency'));
    }

    public function priceable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function payment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeHasPayment($query, $has = true)
    {
        return $has
            ? $query->whereHas('payments')
            : $query->whereDoesntHave('payments');
    }
}
