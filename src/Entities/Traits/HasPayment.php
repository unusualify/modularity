<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Modules\SystemPayment\Entities\Payment;
use Modules\SystemPricing\Entities\Price;
use Money\Currency;
use Oobook\Priceable\Facades\PriceService;
use Unusualify\Modularity\Entities\Enums\PaymentStatus;

trait HasPayment
{
    // Will be defining the relation between the completed payment model and payable model
    use HasPriceable;

    public static function bootHasPayment(): void
    {

        self::retrieved(static function (Model $model) {
            if ($model->paymentPrice) {
                // $currency = new Currency($model->paymentPrice->currency->iso_4217);
                // $model->setAttribute('_price', \Oobook\Priceable\Facades\PriceService::formatAmount($model->paymentPrice->raw_amount, $currency));
                // $model->setAttribute('priceExcludingVatFormatted', \Oobook\Priceable\Facades\PriceService::formatAmount($model->paymentPrice->raw_amount, $currency));
                // $model->setAttribute('paymentStatus', match (true) {
                //     ! $model->paidPrices()->exists() => PaymentStatus::UNPAID,
                //     $model->payablePrice?->price_including_vat > 0 => PaymentStatus::PARTIALLY_PAID,
                //     default => PaymentStatus::PAID
                // });
                // $model->setAttribute('paymentStatusTranslated', match (true) {
                //     ! $model->paidPrices()->exists() => __('Unpaid'),
                //     $model->payablePrice?->total_amount > 0 => __('Partially Paid'),
                //     default => __('Paid')
                // });
            }
        });

        self::updating(static function (Model $model) {
            // if (isset($model->_price)) {
            //     $model->offsetUnset('_price');
            //     $model->offsetUnset('priceExcludingVatFormatted');
            //     $model->offsetUnset('paymentStatus');
            //     $model->offsetUnset('paymentStatusTranslated');
            // }
        });

        self::saving(static function (Model $model) {
            if (isset($model->_price)) {
                $model->offsetUnset('_price');
                $model->offsetUnset('priceExcludingVatFormatted');
                $model->offsetUnset('paymentStatus');
                $model->offsetUnset('paymentStatusTranslated');
            }
        });

    }

    public function initializeHasPayment(): void
    {
        $this->append([
            'is_paid',
            'is_partially_paid',
            'is_unpaid',
            'is_refunded',
            'payment_status_formatted',
        ]);
    }

    public function paymentPrice(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        $priceTable = (new Price)->getTable();
        $morphClass = addslashes($this->getMorphClass());

        return $this->morphOne(Price::class, 'priceable')
            ->whereRaw("{$priceTable}.created_at = (select max(created_at) from {$priceTable} where {$priceTable}.priceable_id = '{$this->id}' and {$priceTable}.priceable_type = '{$morphClass}' and {$priceTable}.role = 'payment')");

        // return $this->morphOne(Price::class, 'priceable')
        //     ->where('role', 'payment')
        //     ->latest('created_at');
    }

    public function initialPayablePrice(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        $priceTable = (new Price)->getTable();
        $morphClass = addslashes($this->getMorphClass());

        return $this->morphOne(Price::class, 'priceable')
            ->whereRaw("{$priceTable}.created_at = (select min(created_at) from {$priceTable} where {$priceTable}.priceable_id = '{$this->id}' and {$priceTable}.priceable_type = '{$morphClass}' and {$priceTable}.role = 'payment')");

    }

    public function payablePrice(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        $priceTable = (new Price)->getTable();
        $morphClass = addslashes($this->getMorphClass());

        return $this->morphOne(Price::class, 'priceable')
            // ->hasPayment(false)
            ->hasPayment(false)
            ->orWhereHas('payments', fn ($q) => $q->where('status', '!=', 'COMPLETED'))
            ->whereRaw("{$priceTable}.created_at = (select max(created_at) from {$priceTable} where {$priceTable}.priceable_id = '{$this->id}' and {$priceTable}.priceable_type = '{$morphClass}' and {$priceTable}.role = 'payment')");
    }

    public function paidPrices(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Price::class, 'priceable')
            ->where('role', 'payment')
            ->hasPayment(true, 'COMPLETED');
    }

    public function payment(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        $priceTable = (new Price)->getTable();
        $paymentTable = (new Payment)->getTable();
        $morphClass = $this->getMorphClass();

        return $this->hasOneThrough(
            Payment::class,
            Price::class,
            'priceable_id',   // Foreign key on Price table
            'price_id',       // Foreign key on Payment table
            'id',             // Local key on this model
            'id'              // Local key on Price model
        )->where("{$priceTable}.priceable_type", $morphClass)
            ->where("{$priceTable}.role", 'payment')
            ->latest("{$paymentTable}.created_at");
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        $priceTable = (new Price)->getTable();
        $morphClass = $this->getMorphClass();

        return $this->hasManyThrough(
            Payment::class,
            Price::class,
            'priceable_id',   // Foreign key on Price table
            'price_id',       // Foreign key on Payment table
            'id',             // Local key on this model
            'id'              // Local key on Price model
        )->where("{$priceTable}.priceable_type", $morphClass)
            ->where("{$priceTable}.role", 'payment');
    }

    protected function totalCostExcludingVat(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->prices->sum('raw_amount')
        );
    }

    protected function totalCostIncludingVat(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->prices->sum('total_amount')
        );
    }

    protected function totalCostExcludingVatFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->totalCostExcludingVat
                ? PriceService::formatAmount($this->totalCostExcludingVat, new Currency($this->initialPayablePrice->currency_iso_4217))
                : null
        );
    }

    protected function totalCostIncludingVatFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->totalCostIncludingVat
                ? PriceService::formatAmount($this->totalCostIncludingVat, new Currency($this->initialPayablePrice->currency_iso_4217))
                : null
        );
    }

    protected function initialPriceExcludingVat(): Attribute
    {
        $price = 0;

        foreach ($this->getPaymentRelations() as $relation) {
            $relation = $this->$relation;

            if ($relation instanceof Collection) {
                $relation = $relation->each(function ($item) use (&$price) {
                    $basePrice = $item->basePrice ?? $item->base_price;

                    if ($basePrice) {
                        try {
                            $price += $basePrice instanceof Model
                                ? $basePrice->raw_amount
                                : $basePrice['raw_amount'];
                        } catch (\Exception $e) {
                            dd($e, $item);
                        }
                    }
                });
            } elseif ($relation instanceof Model) {
                $basePrice = $relation->basePrice;
                $price += $basePrice instanceof Model
                    ? $basePrice->raw_amount
                    : $basePrice['raw_amount'];
            }
        }

        return Attribute::make(
            get: fn ($value) => $price
        );
    }

    protected function initialPriceExcludingVatFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => PriceService::formatAmount($this->initialPriceExcludingVat, new Currency($this->initialPayablePrice->currency_iso_4217))
        );
    }

    protected function payablePriceExcludingVat(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->payablePrice ? $this->payablePrice->raw_amount : null,
        );
    }

    protected function payablePriceExcludingVatFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => isset($this->payablePriceExcludingVat)
                ? PriceService::formatAmount($this->payablePriceExcludingVat, new Currency($this->payablePrice->currency_iso_4217)) . ' +' . __('VAT')
                : null,
        );
    }

    protected function payablePriceIncludingVat(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->payablePrice ? $this->payablePrice->price_including_vat : null,
        );
    }

    protected function payablePriceIncludingVatFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => isset($this->payablePriceIncludingVat)
                ? PriceService::formatAmount($this->payablePriceIncludingVat, new Currency($this->payablePrice->currency_iso_4217))
                : null,
        );
    }

    protected function isPaid(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->paidPrices()->exists(),
        );
    }

    protected function isUnpaid(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->payablePrice()->exists(),
        );
    }

    protected function isPartiallyPaid(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->is_paid && $this->is_unpaid,
        );
    }

    protected function isRefunded(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->payment()->where('status', 'REFUNDED')->exists(),
        );
    }

    protected function paymentStatusFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => match (true) {
                $this->is_refunded => "<v-chip color='error'>" . __('Refunded') . '</v-chip>',
                $this->is_paid => "<v-chip color='success'>" . __('Paid') . '</v-chip>',
                $this->is_partially_paid => "<v-chip color='warning'>" . __('Partially Paid') . '</v-chip>',
                $this->is_unpaid => "<v-chip color='error'>" . __('Unpaid') . '</v-chip>',
                default => '<v-chip>' . __('Not Ready') . '</v-chip>',
            },
        );
    }

    // protected function paymentStatus(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn ($value) => match(true) {
    //             !$this->paidPrices()->exists() => __('Unpaid'),
    //             $this->payablePrice?->price_including_vat > 0 => __('Partially Paid'),
    //             default => __('Paid')
    //         },
    //     );
    // }

    final public function getPaymentRelations(): array
    {
        return $this->hasPaymentRelations
                ? (is_string($this->hasPaymentRelations) ? [$this->hasPaymentRelations] : $this->hasPaymentRelations)
                : [];
    }
}
