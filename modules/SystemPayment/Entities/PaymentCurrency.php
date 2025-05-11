<?php

namespace Modules\SystemPayment\Entities;

class PaymentCurrency extends \Modules\SystemPricing\Entities\Currency
{
    protected $fillable = [
        'payment_service_id',
        'name',
        'symbol',
        'iso_4217',
        'iso_4217_number',
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
}
