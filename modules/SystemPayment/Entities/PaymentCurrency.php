<?php

namespace Modules\SystemPayment\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;

class PaymentCurrency extends \Modules\SystemPricing\Entities\Currency
{
    protected $fillable = [
        'payment_service_id',
        'name',
        'symbol',
        'iso_4217',
        'iso_4217_number',
    ];

    protected $appends = [
        'has_credit_card_payment_service',
    ];

    /**
     * The paymentServices that belong to the Currency.
     */
    public function paymentServices(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\Modules\SystemPayment\Entities\PaymentService::class);
    }

    public function paymentService(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\SystemPayment\Entities\PaymentService::class, 'payment_service_id', 'id');
    }

    protected function hasCreditCardPaymentService() : Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->relationLoaded('paymentService')
                ? $this->paymentService->published && $this->paymentService->is_internal
                : $this->paymentService()->published()->isInternal()->count() > 0,
        );
    }

    protected function hasBuiltInForm(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->paymentService ? $this->paymentService->hasBuiltInForm : false,
        );
    }
}
