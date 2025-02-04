<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Request;
use Modules\SystemPricing\Entities\Price;
use Money\Currency;
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
                $model->setAttribute('basePrice_show', $basePriceFormatted);
            }
        });
        static::saving(function ($model) {
            if (isset($model->basePrice_show)) {
                $model->offsetUnset('basePrice_show');
            }
        });
    }

    public function prices(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Price::class, 'priceable');
    }

    public function basePrice(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        // dd(Request::getUserCurrency());
        // return $this->prices()->where('currency_id', Request::getUserCurrency()->id);
        return $this->morphOne(Price::class, 'priceable')
            ->where('currency_id', Request::getUserCurrency()->id);
    }

    protected function basePriceRaw(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice->display_price,
        );
    }

    protected function basePriceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => \Oobook\Priceable\Facades\PriceService::formatAmount($this->basePriceRaw) . (config('priceable.prices_are_including_vat') ? '' : ' +' . __('VAT')),
        );
    }

    protected function basePriceFormattedWithoutVat(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => \Oobook\Priceable\Facades\PriceService::formatAmount($this->basePriceRaw),
        );
    }
}
