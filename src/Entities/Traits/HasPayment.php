<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
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
                $currency = new Currency($model->paymentPrice->currency->iso_4217);
                $model->setAttribute('_price', \Oobook\Priceable\Facades\PriceService::formatAmount($model->paymentPrice->display_price, $currency));
                $model->setAttribute('priceExcludingVatFormatted', \Oobook\Priceable\Facades\PriceService::formatAmount($model->paymentPrice->display_price, $currency));
                $model->setAttribute('paymentStatus', match (true) {
                    ! $model->paidPrices()->exists() => PaymentStatus::UNPAID,
                    $model->payablePrice?->price_including_vat > 0 => PaymentStatus::PARTIALLY_PAID,
                    default => PaymentStatus::PAID
                });
                $model->setAttribute('paymentStatusTranslated', match (true) {
                    ! $model->paidPrices()->exists() => __('Unpaid'),
                    $model->payablePrice?->price_including_vat > 0 => __('Partially Paid'),
                    default => __('Paid')
                });
            }
        });

        self::updating(static function (Model $model) {
            if (isset($model->_price)) {
                $model->offsetUnset('_price');
                $model->offsetUnset('priceExcludingVatFormatted');
                $model->offsetUnset('paymentStatus');
                $model->offsetUnset('paymentStatusTranslated');
            }
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

    public function paymentPrice(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(config('priceable.models.price'), 'priceable')
            ->where('role', 'payment')
            ->latest('created_at');
    }

    public function initialPayablePrice(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Price::class, 'priceable')
            ->where('role', 'payment')
            ->oldest('created_at');
    }

    public function payablePrice(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Price::class, 'priceable')
            ->where('role', 'payment')
            // ->hasPayment(false)
            ->hasPayment(false)
            ->orWhereHas('payments', fn ($q) => $q->where('status', '!=', 'COMPLETED'))
            ->latest('created_at');
    }

    public function paidPrices(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Price::class, 'priceable')
            ->where('role', 'payment')
            ->hasPayment(true, 'COMPLETED');
    }

    protected function totalCostExcludingVat(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->prices->sum('price_excluding_vat')
        );
    }

    protected function totalCostIncludingVat(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->prices->sum('price_including_vat')
        );
    }

    protected function totalCostExcludingVatFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => PriceService::formatAmount($this->totalCostExcludingVat, new Currency($this->initialPayablePrice->currency_iso_4217))
        );
    }

    protected function totalCostIncludingVatFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => PriceService::formatAmount($this->totalCostIncludingVat, new Currency($this->initialPayablePrice->currency_iso_4217))
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

                    try {
                        $price += $basePrice instanceof Model
                            ? $basePrice->price_excluding_vat
                            : $basePrice['price_excluding_vat'];
                    } catch (\Exception $e) {
                        dd($e, $item);
                    }
                });
            } else {
                $basePrice = $relation->basePrice;
                $price += $basePrice instanceof Model
                    ? $basePrice->price_excluding_vat
                    : $basePrice['price_excluding_vat'];
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
            get: fn ($value) => $this->payablePrice ? $this->payablePrice->price_excluding_vat : null,
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

    protected function isPartiallyPaid(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->payablePrice?->price_including_vat > 0,
        );
    }

    protected function isUnpaid(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ! $this->paidPrices()->exists(),
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
