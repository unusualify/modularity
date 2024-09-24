<?php

namespace Unusualify\Modularity\Repositories\Traits;

trait PaymentTrait
{
    public $paymentTraitRelationName = null;
    public $paymentTraitDefaultCurrencyId = 2;
    protected $requiredTrait = 'Oobook\Priceable\Traits\HasPriceable';

    protected function afterSavePaymentTrait($object, $fields)
    {
        $currencyId = isset($fields['currency_id']) ? $fields['currency_id'] : $this->paymentTraitDefaultCurrencyId;

        if ($this->paymentTraitRelationName) {
            $relatedClass = $object->{$this->paymentTraitRelationName}()->getRelated();
            if (classHasTrait($relatedClass, $this->requiredTrait)) {

                $records = $object->{$this->paymentTraitRelationName};
                $totalPrice = 0;

                if ($records instanceof \Illuminate\Database\Eloquent\Collection) {
                    foreach ($records as $record) {
                        $price = $record->prices()->where('currency_id', $currencyId)->first();
                        if (!is_null($price))
                            $totalPrice += $price->display_price;
                    }
                }

                if (!$object->price && !empty($records)) {

                    $object->price()->create([
                        'price_type_id' => 1,
                        'vat_rate_id' => 1,
                        'currency_id' => $currencyId,
                        'display_price' => ($totalPrice / 100)
                    ]);
                }else if ($object->price->display_price != $totalPrice) {

                    $object->price->display_price = $totalPrice;
                    $object->price->currency_id = $currencyId;
                    // dd($object->price, $totalPrice, $currencyId);
                    $object->price()->save($object->price);
                }
            }
        }
    }

    public function getPaymentFormSchema()
    {
        return [
            [
                'name' => 'price_id',
                'label' => 'price_id',
                'type' => 'hidden'
            ],
            [
                'name' => 'payment_service',
                'label' => 'Payment',
                'type' => 'payment-service',
                // 'connector' => 'SystemPayment:PaymentService|repository:listAll',
            ]
        ];
    }
}
