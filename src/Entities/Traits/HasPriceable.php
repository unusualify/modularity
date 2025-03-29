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

    public function initializeHasPriceable()
    {
        $this->append(
            'base_price_raw', // price excluding vat
            'base_price_total', // price including vat
            'base_price_formatted', // price excluding vat formatted (+ VAT)
            'base_price_without_vat_formatted', // price excluding vat formatted
            'base_price_total_formatted' // price including vat formatted
        );
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
            get: fn ($value) => $this->basePrice ? $this->basePrice->price_excluding_vat : null,
        );
    }

    protected function basePriceTotal(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->basePrice ? $this->basePrice->price_including_vat : null,
        );
    }

    protected function basePriceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->base_price_raw
                ? \Oobook\Priceable\Facades\PriceService::formatAmount($this->base_price_raw) . (config('priceable.prices_are_including_vat') ? '' : ' +' . __('VAT'))
                : null,
        );
    }

    protected function basePriceWithoutVatFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->base_price_raw
                ? \Oobook\Priceable\Facades\PriceService::formatAmount($this->base_price_raw)
                : null,
        );
    }

    protected function basePriceTotalFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->base_price_total
                ? \Oobook\Priceable\Facades\PriceService::formatAmount($this->base_price_total)
                : null,
        );
    }
}
