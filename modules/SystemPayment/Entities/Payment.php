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

    protected $fillable = [
        'payment_service_id',
        'price_id',
        'order_id',
        'amount',
        'currency_id',
        'currency',
        'status',
        'email',
        'installment',
        'parameters',
        'response',
    ];

    protected $appends = [
        'invoice_file',
        'amount_formatted',
    ];

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

    public function priceCurrency(): HasOneThrough
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

    protected function amountFormatted(): Attribute
    {
        $moneyCurrency = new \Money\Currency($this->priceCurrency->iso_4217);

        return Attribute::make(
            get: fn ($value) => \Oobook\Priceable\Facades\PriceService::formatAmount($this->amount, $moneyCurrency),
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

    protected function invoiceFile(): Attribute
    {
        $file = $this->fileponds()->where('role', 'invoice')->first();

        return Attribute::make(
            get: fn ($value) => $file ? $file->mediableFormat() : null,
        );
    }
}
