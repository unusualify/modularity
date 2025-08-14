<?php

namespace Modules\SystemPricing\Entities;

use Modules\SystemPayment\Entities\Payment;
use Modules\SystemPricing\Entities\Mutators\PriceMutators;
use Unusualify\Modularity\Entities\Traits\ModelHelpers;

class Price extends \Oobook\Priceable\Models\Price
{
    use ModelHelpers,
        PriceMutators;

    public static $priceSavingKey = 'price_value';

    protected $casts = [
        'discount_percentage' => 'double',
        'valid_from' => 'datetime',
        'valid_till' => 'datetime',
    ];

    protected $appends = [
        'total_amount',
    ];

    /**
     * For a price we need to make sure we always have
     * a VAT rate and a Currency. Selecting them everytime
     * in Nova is a hassle, therefor we set some default
     * that come from the config.
     *
     * @return array Array with default attributes
     */
    public function defaultAttributes(): array
    {
        $pricingSavingKey = Price::$priceSavingKey;

        return [
            'vat_rate_id' => config('priceable.defaults.vat_rates'),
            'currency_id' => config('priceable.defaults.currencies'),
            'price_type_id' => config('priceable.defaults.price_type'),

            'discount_percentage' => 0.00,
            $pricingSavingKey => 0.00,
        ];
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

    public function updateOrNewPayment($payload)
    {
        $payment = $this->payments()->whereIn('status', ['PENDING', 'FAILED'])->latest()->first();

        if($payment){
            $payment->update($payload);
        } else {
            $payment = $this->payment()->create($payload);
        }

        return $payment;
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
