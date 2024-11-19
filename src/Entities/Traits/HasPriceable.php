<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Support\Facades\Request;
use Money\Currency;
use Oobook\Priceable\Models\Price;
use Oobook\Priceable\Traits\HasPriceable as TraitsHasPriceable;

trait HasPriceable
{
    use TraitsHasPriceable;

    /**
     * Boot the trait.
     *
     * Sets up event listeners for model creation, updating, retrieval, and deletion.
     *
     * @return void
     */
    public static function bootHasPriceable()
    {
        // parent::bootHasPriceable();

        static::retrieved(function ($model) {
            if (in_array('Oobook\Priceable\Traits\HasPriceable', class_uses_recursive($model)) && $model->price) {
                $basePrice = $model->basePrice;
                $basePriceFormatted = null;
                if ($basePrice) {
                    $currency = new Currency($basePrice->currency->iso_4217);
                    $basePriceFormatted = \Oobook\Priceable\Facades\PriceService::formatAmount($basePrice->display_price, $currency);
                }
                $model->setAttribute('base_price_show', $basePriceFormatted);
            }
        });
        static::saving(function ($model) {
            if (isset($model->base_price_show)) {
                $model->offsetUnset('base_price_show');
            }
        });
    }

    public function basePrice(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        // dd(Request::getUserCurrency());
        // return $this->prices()->where('currency_id', Request::getUserCurrency()->id);
        return $this->morphOne(Price::class, 'priceable')
            ->where('currency_id', Request::getUserCurrency()->id);
    }
}
