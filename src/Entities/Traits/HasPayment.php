<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Model;
use Money\Currency;

trait HasPayment
{
    // Will be defining the relation between the completed payment model and payable model
    use HasPriceable;

    public function paymentPrice(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(config('priceable.models.price'), 'priceable')
            ->where('role', 'payment');
    }

    public static function bootHasPayment(): void
    {

        self::retrieved(static function (Model $model) {
            if ($model->paymentPrice) {
                $currency = new Currency($model->paymentPrice->currency->iso_4217);
                $model->setAttribute('_price', \Oobook\Priceable\Facades\PriceService::formatAmount($model->paymentPrice->display_price, $currency));
            }
        });

        self::updating(static function (Model $model) {
            if ($model->_price) {
                $model->offsetUnset('_price');
            }
        });

        self::saving(static function (Model $model) {
            if ($model->_price) {
                $model->offsetUnset('_price');
            }
        });

    }
}
