<?php

namespace Modules\SystemPayment\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Modules\SystemPricing\Entities\Price;
use Oobook\Priceable\Models\Currency;
use Unusualify\Modularity\Entities\Traits\HasFileponds;
use Unusualify\Modularity\Entities\Traits\ModelHelpers;

class Payment extends \Unusualify\Payable\Models\Payment
{
    use ModelHelpers, HasFileponds;

    /**
     * Get the paymentService that owns the Payment.
     */
    public function paymentService(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\SystemPayment\Entities\PaymentService::class, 'payment_service_id', 'id');
    }

    public function price(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Price::class, 'price_id', 'id');
    }

    public function currency(): HasOneThrough
    {
        return $this->hasOneThrough(
            Currency::class,
            Price::class,
            'id',
            'id',
            'price_id',
            'currency_id'
        );
    }

    public function paymentable() {}

    public function currencyId(): Attribute
    {
        return Attribute::make(

        );
    }

    /**
     * The currencyServices that belong to the Payment.
     */
    public function currencyServices(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\Modules\SystemPayment\Entities\PaymentCurrency::class);
    }

    /**
     * The currencies that belong to the Payment.
     */
    public function currencies(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\Modules\SystemPayment\Entities\PaymentCurrency::class);
    }
}
