<?php

namespace Modules\SystemPricing\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Modules\SystemPayment\Entities\Payment;
use Unusualify\Modularity\Entities\Traits\ModelHelpers;
class Price extends \Oobook\Priceable\Models\Price
{
    use ModelHelpers;

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
            get: fn ($value) => $this->payment('COMPLETED')->exists(),
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

    public function payment($status = null): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Payment::class)
            ->when($status, fn($q) => is_array($status) ? $q->whereIn('status', $status) : $q->whereStatus($status));
    }

    public function payments($status = null): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // status : 'PENDING','CANCELLED','COMPLETED','FAILED','REFUNDED'
        return $this->hasMany(Payment::class)
            ->when($status, fn($q) => is_array($status) ? $q->whereIn('status', $status) : $q->whereStatus($status));
    }

    public function completedPayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->payments('COMPLETED');
    }
    public function failedPayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->payments('FAILED');
    }

    public function refundedPayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->payments('REFUNDED');
    }

    public function scopeHasPayment($query, $has = true, $status = null)
    {
        return $has
            ? $query->whereHas('payments', fn($q) => $q->when($status, fn($q) => is_array($status) ? $q->whereIn('status', $status) : $q->whereStatus($status)))
            : $query->whereDoesntHave('payments', fn($q) => $q->when($status, fn($q) => is_array($status) ? $q->whereIn('status', $status) : $q->whereStatus($status)));
    }
}
