<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;
use Modules\SystemPricing\Entities\Price;

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
        $priceSavingKey = Price::$priceSavingKey ?? 'price_value';

        if (isset($fields['payment'])) {
            $val = Arr::isAssoc($fields['payment']) ? $fields['payment'] : $fields['payment'][0];
            $price = Price::find($val['id']);

            if ($price->isUnpaid) {
                // Update existing unpaid record
                $price->update(Arr::only($val, [
                    'price_type_id',
                    'vat_rate_id',
                    'currency_id',
                    'role',
                    'valid_from',
                    'valid_till',

                    'discount_percentage',

                    $priceSavingKey,
                ]));
            } else {
                // Create new record with previous data for paid records
                $newPrice = $price->replicate();
                $newPrice->fill(Arr::only($val, [
                    'price_type_id',
                    'vat_rate_id',
                    'currency_id',
                    'role',
                    'valid_from',
                    'valid_till',

                    'discount_percentage',

                    $priceSavingKey,
                ]));
                $newPrice->save();
            }
        } elseif (! $object->paymentPrice || (isset($fields['force_payment_update']) && $fields['force_payment_update'])) {
            $session_currency = request()->getUserCurrency()->id;
            // dd($fields);
            $currencyId = isset($fields['currency_id'])
                ? $fields['currency_id']
                : $session_currency ?? $this->paymentTraitDefaultCurrencyId;

            $paymentRelations = $this->model->getPaymentRelations();

            if (count($paymentRelations) > 0) {

                $totalPrice = 0;
                $calculated = false;

                // dd($relations);

                foreach ($paymentRelations as $relationName) {

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
                                $price = $record->prices->filter(function($price) use ($currencyId){
                                    return $price->currency_id == $currencyId;
                                })->first();

                                if (! is_null($price)) {
                                    $calculated = true;
                                    $totalPrice += $price->raw_price;
                                }
                            }
                        }

                    }


                }

                if (! $object->paymentPrice && $calculated) {

                    $object->paymentPrice()->create([
                        'price_type_id' => 1,
                        'vat_rate_id' => 1,
                        'currency_id' => $currencyId,
                        $priceSavingKey => ($totalPrice / 100),
                        'role' => 'payment',
                    ]);
                } elseif ($object->paymentPrice && $object->paymentPrice->raw_price != $totalPrice) {

                    $object->paymentPrice->{$priceSavingKey} = $totalPrice / 100;
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
            $priceSavingKey = Price::$priceSavingKey;
            // $query = $object->paymentPrice;
            $defaultPriceAttributes = $object->paymentPrice()->getRelated()->defaultAttributes();

            $paymentPrice = $object->paymentPrice;


            if ($paymentPrice) {
                $serialized = $paymentPrice->toArray();
                $serialized['raw_price'] = (float) $serialized['raw_price'] / 100;
                $serialized[$priceSavingKey] = (float) $serialized[$priceSavingKey] / 100;
                $fields['payment'] = $serialized;
            } else {
                $fields['payment'] = [
                    array_merge_recursive_preserve($defaultPriceAttributes, [
                        $priceSavingKey => 0.00,
                        'raw_price' => 0.00,
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
