<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Unusualify\Priceable\Models\Price;
use Unusualify\Priceable\Traits\HasPriceable;

trait PaymentTrait
{
  public $paymentTraitRelationName = null;
  public $paymentTraitDefaultCurrencyId = 2;

  protected function afterSavePaymentTrait($object, $fields)
  {
      $currencyId =  isset($fields['currency_id']) ? $fields['currency_id'] : $this->paymentTraitDefaultCurrencyId;

      if($this->paymentTraitRelationName){
        $records = $object->{$this->paymentTraitRelationName};

        $totalPrice = 0;
        foreach ($records as $record) {
          $price = $record->prices()->where('currency_id', $currencyId)->first();
          if(!is_null($price))
            $totalPrice += $price->display_price;
        }
        if (!$object->price) {
          $object->price()->create([
            'price_type_id' => 1,
            'vat_rate_id' => 1,
            'currency_id' => 1,
            'display_price' => ($totalPrice / 100)
          ]);
        }
      }
  }
}