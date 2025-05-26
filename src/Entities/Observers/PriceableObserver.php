<?php

namespace Unusualify\Modularity\Entities\Observers;

// use Oobook\Priceable\Models\Price;
use Modules\SystemPricing\Entities\Price;

class PriceableObserver
{
    public function saving($price)
    {
        $priceSavingKey = Price::$priceSavingKey;

        $price->discount_percentage ??= 0;

        $issetPriceSavingKey = isset($price->{$priceSavingKey});

        if ($issetPriceSavingKey) {
            $newRawValue = $price->{$priceSavingKey} * 100;
            $multiplier = $price->vatRate->multiplier();

            if (config('priceable.prices_are_including_vat')) { // $value is with vat and substract the vat

                /**
                 * The added price is including the VAT. We need to calculate
                 * the price without the VAT.
                 */
                $price->raw_amount = $newRawValue / $multiplier;
                $price->vat_amount = $newRawValue - $price->raw_amount;
            } else {
                $price->raw_amount = $newRawValue;
                $price->vat_amount = (($newRawValue * $multiplier) - $newRawValue);

            }
        }
        $price->offsetUnset($priceSavingKey);
    }

    public function retrieved($price)
    {
        $priceSavingKey = Price::$priceSavingKey;
        $priceSavingValue = $price->raw_amount;

        if (config('priceable.prices_are_including_vat')) { // $value is with vat and substract the vat
            $priceSavingValue = $price->raw_amount + $price->vat_amount;
        }

        $price->setAttribute($priceSavingKey, $priceSavingValue / 100);
    }

    public function replicating($price)
    {
        $price->offsetUnset(Price::$priceSavingKey ?? 'price_value');
    }
}
