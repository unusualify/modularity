<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;
use Oobook\Priceable\Models\Price;

trait PaymentTrait
{
    use PricesTrait;

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
        if (isset($fields['payment'])) {
            $val = Arr::isAssoc($fields['payment']) ? $fields['payment'] : $fields['payment'][0];
            // $val['display_price'] = $val['display_price'] * 100;
            $paymentPrice = Price::find($val['id']);
            $paymentPrice->update(Arr::only($val, ['price_type_id', 'vat_rate_id', 'currency_id', 'display_price', 'role', 'valid_from', 'valid_till']));

        } elseif (! $object->paymentPrice) {
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
                if (! $object->paymentPrice && $calculated) {

                    $object->paymentPrice()->create([
                        'price_type_id' => 1,
                        'vat_rate_id' => 1,
                        'currency_id' => $currencyId,
                        'display_price' => ($totalPrice / 100),
                        'role' => 'payment',
                    ]);
                } elseif ($object->paymentPrice && $object->paymentPrice->display_price != $totalPrice) {

                    $object->paymentPrice->display_price = $totalPrice / 100;
                    $object->paymentPrice->currency_id = $currencyId;
                    // dd($object->price, $totalPrice, $currencyId);
                    $object->paymentPrice()->save($object->paymentPrice);
                }

            }
        }
    }

    public function getFormFieldsPaymentTrait($object, $fields)
    {
        if (method_exists($object, 'paymentPrice') && $object->has('paymentPrice')) {
            $query = $object->paymentPrice;

            $paymentPrice = $object->paymentPrice;

            if ($paymentPrice) {
                $serialized = $paymentPrice->toArray();
                // dd($serialized);
                $serialized['display_price'] = (float) $serialized['display_price'] / 100;
                $fields['payment'] = $serialized;
            } else {
                $fields['payment'] = [
                    array_merge_recursive_preserve($this->defaultPriceData, [
                        'display_price' => 0.00,
                        'currency_id' => Request::getUserCurrency()->id]
                    ),
                ];
            }

        }

        return $fields;
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
