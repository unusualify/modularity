<?php

namespace Unusualify\Modularity\Observers;

// use Oobook\Priceable\Models\Price;
use Modules\SystemPricing\Entities\Price;

class PriceableObserver
{
    public function saving($price)
    {
        $priceSavingKey = Price::$priceSavingKey;

        $price->discount_percentage ??= 0;

        $value = $price->{$priceSavingKey} ?? 0;
        $multiplier = $price->vatRate->multiplier();

        if (config('priceable.prices_are_including_vat')) { // $value is with vat and substract the vat

            /**
             * The added price is including the VAT. We need to calculate
             * the price without the VAT.
             */
            $price->raw_price = $value / $multiplier;
            $price->vat_amount = $value - $price->raw_price;
        } else {
            $price->raw_price = $value;
            $price->vat_amount = (($value * $multiplier) - $value) * 100;

        }
        $price->offsetUnset($priceSavingKey);
    }

    public function retrieved($price)
    {
        $priceSavingKey = Price::$priceSavingKey;
        $priceSavingValue = $price->raw_price;

        if (config('priceable.prices_are_including_vat')) { // $value is with vat and substract the vat
            $priceSavingValue = $price->raw_price * $price->vat_amount;
        }

        // dd($priceSavingValue);
        $price->setAttribute($priceSavingKey, $priceSavingValue);
    }
}
