<?php

namespace Modules\SystemPayment\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Modules\SystemPricing\Entities\Price;
use Oobook\Priceable\Models\Currency;
use Unusualify\Modularity\Entities\Traits\HasCreator;
use Unusualify\Modularity\Entities\Traits\HasFileponds;
use Unusualify\Modularity\Entities\Traits\HasSpreadable;
use Unusualify\Modularity\Entities\Traits\Core\ModelHelpers;
use Unusualify\Modularity\Relations\PaymentableRelation;

class Payment extends \Unusualify\Payable\Models\Payment
{
    use ModelHelpers, HasFileponds, HasCreator, HasSpreadable;

    protected $fillable = [
        'payment_service_id',
        'payment_gateway',
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
        'bank_receipts',
        'invoice_file',
        'amount_formatted',
        'invoices',

        'status_label',
        'status_color',
        'status_icon',
        'status_vuetify_icon',
        'status_vuetify_chip',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('paymentable_morph_keys', function (\Illuminate\Database\Eloquent\Builder $builder) {
            $paymentTable = (new static)->getTable();
            $pricesTable = (new Price)->getTable();

            // Ensure base columns plus our subselects are always present
            $builder->addSelect($paymentTable . '.*')
                ->addSelect([
                    'paymentable_type' => Price::select('priceable_type')
                        ->whereColumn($pricesTable . '.id', $paymentTable . '.price_id')
                        ->limit(1),
                    'paymentable_id' => Price::select('priceable_id')
                        ->whereColumn($pricesTable . '.id', $paymentTable . '.price_id')
                        ->limit(1),
                ]);
        });
    }

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

    /**
     * Behaves like a real morphTo by providing the morph keys via subselects.
     */
    public function paymentable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo('paymentable');
    }
    // /**
    //  * Behaves like a real morphTo by providing the morph keys via subselects.
    //  */
    // public function paymentable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    // {
    //     return new PaymentableRelation($this);
    // }

    protected function serviceClass(): Attribute
    {
        $serviceClass = null;
        $paymentGateway = null;
        try {
            $paymentGateway = $this->paymentService->key;
            $serviceClass = \Unusualify\Payable\Payable::getServiceClass($paymentGateway);
        } catch (\Exception $e) {
            if ($e->getMessage() == 'Service class not found for slug: ' . $paymentGateway && $this->paymentService->transferrable) {
                $serviceClass = new class extends \Unusualify\Payable\Services\PaymentService
                {
                    public function __construct()
                    {
                        $this->mode = 'test';
                        $this->config = [];
                    }

                    public function hydrateParams(array|object $params): array
                    {
                        return $params;
                    }
                };
            } else {
                throw $e;
            }
        }

        return Attribute::make(
            get: fn ($value) => $serviceClass,
        );
    }

    protected function amountFormatted(): Attribute
    {
        $currency = Currency::find($this->currency_id);
        $moneyCurrency = new \Money\Currency($currency->iso_4217);

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

    protected function bankReceipts(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->fileponds()->where('role', 'receipts')->get()->map(fn ($file) => $file->mediableFormat()),
        );
    }

    protected function invoiceFile(): Attribute
    {
        $file = $this->fileponds()->where('role', 'invoice')->first();

        return Attribute::make(
            get: fn ($value) => $file ? $file->mediableFormat() : null,
        );
    }

    protected function invoices(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->fileponds()->where('role', 'invoice')->get()->map(fn ($file) => $file->mediableFormat()),
        );
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->status->label(),
        );
    }

    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->status->color(),
        );
    }

    protected function statusIcon(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->status->icon(),
        );
    }

    protected function statusVuetifyIcon(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->status ? "<v-icon icon='{$this->status_icon}' color='{$this->status_color}'/>" : null,
        );
    }

    protected function statusVuetifyChip(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->status
                ? "<v-chip variant='text' color='{$this->status_color}' prepend-icon='{$this->status_icon}'>{$this->status_label}</v-chip>"
                : null
        );
    }
}
