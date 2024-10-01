<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Facades\Session;

trait PaymentTrait
{
    /**
     * paymentTraitRelationName
     *
     * @var undefined
     */
    public $paymentTraitRelationName = null;

    /**
     * paymentTraitDefaultCurrencyId
     *
     * @var int
     */
    public $paymentTraitDefaultCurrencyId = 2;

    /**
     * requiredTrait
     *
     * @var string
     */
    protected $requiredTrait = 'Oobook\Priceable\Traits\HasPriceable';

    /**
     * snapshotTrait
     *
     * @var string
     */
    protected $snapshotTrait = 'Oobook\Snapshot\Traits\HasSnapshot';

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return void
     */
    protected function afterSavePaymentTrait($object, $fields)
    {
        $session_currency = Session::get('currency_id');

        $currencyId = isset($fields['currency_id'])
            ? $fields['currency_id']
            : $session_currency ?? $this->paymentTraitDefaultCurrencyId;


        if ( $this->paymentTraitRelationName ) {
            $relatedClass = $object->{$this->paymentTraitRelationName}()->getRelated();

            $requirementMet = false;

            if ( classHasTrait($relatedClass, $this->requiredTrait) ) {
                $requirementMet = true;
            } else if( classHasTrait($relatedClass, $this->snapshotTrait)
                && classHasTrait($relatedClass->source()->getRelated(), $this->requiredTrait)
            ){
                $requirementMet = true;
            }

            if ($requirementMet) {
                // dd($object->{$this->paymentTraitRelationName});
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

    /**
     * Form Schema to use on vue frontend side
     *
     * @return void
     */
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
