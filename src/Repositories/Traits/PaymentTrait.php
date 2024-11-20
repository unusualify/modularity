<?php

namespace Unusualify\Modularity\Repositories\Traits;

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
    public $paymentTraitDefaultCurrencyId = 1;

    /**
     * requiredTrait
     *
     * @var string
     */
    protected $requiredTrait = 'Unusualify\Modularity\Entities\Traits\HasPriceable';

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
        $session_currency = request()->getUserCurrency()->id;

        $currencyId = isset($fields['currency_id'])
            ? $fields['currency_id']
            : $session_currency ?? $this->paymentTraitDefaultCurrencyId;

        if ($this->paymentTraitRelationName) {
            $relations = is_array($this->paymentTraitRelationName)
                ? $this->paymentTraitRelationName
                : [$this->paymentTraitRelationName];

            $totalPrice = 0;
            $calculated = false;

            // dd($relations);

            foreach ($relations as $relationName) {

                $relatedClass = $object->{$relationName}()->getRelated();

                $requirementMet = false;

                if (classHasTrait($relatedClass, $this->requiredTrait)) {
                    $requirementMet = true;
                } elseif (classHasTrait($relatedClass, $this->snapshotTrait)
                    && classHasTrait($relatedClass->source()->getRelated(), $this->requiredTrait)
                ) {
                    $requirementMet = true;
                }

                if ($requirementMet) {
                    // dd($object->{$this->paymentTraitRelationName});
                    // dd($object, $relationName, $object->{$relationName});
                    $records = $object->{$relationName};
                    if ($records instanceof \Illuminate\Database\Eloquent\Collection) {

                        foreach ($records as $record) {
                            $price = $record->prices()->where('currency_id', $currencyId)->first();

                            if (! is_null($price)) {
                                $calculated = true;
                                $totalPrice += $price->display_price;
                            }
                        }
                    }

                }
            }
            // dd($object, $calculated, $totalPrice);
            if (! $object->price && $calculated) {

                $object->price()->create([
                    'price_type_id' => 1,
                    'vat_rate_id' => 1,
                    'currency_id' => $currencyId,
                    'display_price' => ($totalPrice / 100),
                ]);
            } elseif ($object->price && $object->price->display_price != $totalPrice) {

                $object->price->display_price = $totalPrice / 100;
                $object->price->currency_id = $currencyId;
                // dd($object->price, $totalPrice, $currencyId);
                $object->price()->save($object->price);
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
                'type' => 'hidden',
            ],
            [
                'name' => 'payment_service',
                'label' => 'Payment',
                'type' => 'payment-service',
                // 'connector' => 'SystemPayment:PaymentService|repository:listAll',
            ],
        ];
    }
}
