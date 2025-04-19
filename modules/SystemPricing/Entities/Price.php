<?php

namespace Modules\SystemPricing\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Modules\SystemPayment\Entities\Payment;
use Unusualify\Modularity\Entities\Traits\ModelHelpers;

class Price extends \Oobook\Priceable\Models\Price
{
    use ModelHelpers;

    public static $priceSavingKey = 'price_value';

    protected $casts = [
        'discount_percentage' => 'double',
        'valid_from' => 'datetime',
        'valid_till' => 'datetime',
    ];

    protected $appends = [
        'total_price',
    ];

    /**
     * For a price we need to make sure we always have
     * a VAT rate and a Currency. Selecting them everytime
     * in Nova is a hassle, therefor we set some default
     * that come from the config.
     * @return array Array with default attributes
     */
    public function defaultAttributes(): array
    {
        return [
            'vat_rate_id' => config('priceable.defaults.vat_rates'),
            'currency_id' => config('priceable.defaults.currencies'),
            'price_type_id' => config('priceable.defaults.price_type'),

            'discount_percentage' => 0.00,
        ];
    }

    /**
     * This will make sure that the submitted amount in Nova
     * is multiplied by 100 so we can store it in cents.
     * @param [type] $amount [description]
     */
    // protected function setRawPriceAttribute(float $amount)
    // {
    //     $this->attributes['raw_price'] = $amount * 100;
    // }

    protected function rawPrice(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $value,
            set: fn ($value) => $value * 100,
        );
    }

    protected function vatPercentage(): Attribute
    {
        return new Attribute(
            get: fn ($value) => ($this->vat_amount / $this->raw_price) * 100,
        );
    }

    protected function hasDiscount(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->discount_percentage > 0,
        );
    }

    protected function rawDiscount(): Attribute
    {
        return new Attribute(
            get: fn ($value) => (int) (($this->raw_price) * ($this->discount_percentage / 100)),
        );
    }

    protected function subTotalPrice(): Attribute
    {
        return new Attribute(
            get: fn ($value) => (int) (($this->raw_price + $this->vat_amount)),
        );
    }

    protected function totalDiscount(): Attribute
    {
        return new Attribute(
            get: fn ($value) => (int) (($this->raw_price + $this->vat_amount) * ($this->discount_percentage / 100)),
        );
    }

    protected function totalPrice(): Attribute
    {
        return new Attribute(
            get: fn ($value) => (int) (($this->raw_price + $this->vat_amount) - $this->total_discount),
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
            ->when($status, fn ($q) => is_array($status) ? $q->whereIn('status', $status) : $q->whereStatus($status));
    }

    public function payments($status = null): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // status : 'PENDING','CANCELLED','COMPLETED','FAILED','REFUNDED'
        return $this->hasMany(Payment::class)
            ->when($status, fn ($q) => is_array($status) ? $q->whereIn('status', $status) : $q->whereStatus($status));
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
            ? $query->whereHas('payments', fn ($q) => $q->when($status, fn ($q) => is_array($status) ? $q->whereIn('status', $status) : $q->whereStatus($status)))
            : $query->whereDoesntHave('payments', fn ($q) => $q->when($status, fn ($q) => is_array($status) ? $q->whereIn('status', $status) : $q->whereStatus($status)));
    }
}
