<?php

namespace Unusualify\Modularity\Entities\Traits;

use Unusualify\Priceable\Traits\HasPriceable;
use Illuminate\Database\Eloquent\Model;
use Money\Currency;

trait HasPayment
{
  // Will be defining the relation between the completed payment model and payable model

  public function price() : \Illuminate\Database\Eloquent\Relations\MorphOne
  {
    return $this->morphOne(config('priceable.models.price'), 'priceable');
  }

  public static function bootHasPayment(): void {

    self::retrieved(static function (Model $model) {
        if($model->price){
            // dd($model->price->currency);
            $currency = new Currency($model->price->currency->iso_4217);
            // dd($currency);
            $model->setAttribute('_price', \Unusualify\Priceable\Facades\PriceService::formatAmount($model->price->display_price, $currency));
        }
        // dd($model);
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
