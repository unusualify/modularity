<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;
use Modules\SystemPricing\Entities\Price;
use Oobook\Priceable\Models\Currency;
use Unusualify\Modularity\Facades\CurrencyExchange;

trait PricesTrait
{
    protected $formatableColumns = [
        'id',
        'raw_amount',
        'currency_id',
        'vat_rate_id',
        'price_type_id',
        'discount_percentage',
    ];

    public function setColumnsPricesTrait($columns, $inputs)
    {
        $traitName = get_class_short_name(__TRAIT__);

        $columns[$traitName] = collect($inputs)->reduce(function ($acc, $curr) {
            if (preg_match('/price/', $curr['type'])) {
                $acc[] = $curr['name'];
            }

            return $acc;
        }, []);

        return $columns;
    }

    /**
     * @param \Unusualify\Modularity\Entities\Model $object
     * @param array $fields
     * @return void
     */
    public function afterSavePricesTrait($object, $fields)
    {
        if ($this->shouldIgnoreFieldBeforeSave('prices')) {
            return;
        }

        $onlyBaseCurrency = modularityConfig('services.currency_exchange.active');
        $baseCurrency = modularityConfig('services.currency_exchange.base_currency');
        $priceSavingKey = Price::$priceSavingKey;

        foreach ($this->getColumns(__TRAIT__) as $name) {
            if ($name !== 'payment' && isset($fields[$name])) {
                $existingPrices = $object->prices()->where('role', $name)->get();
                $defaultPriceAttributes = $object->prices()->getRelated()->defaultAttributes();

                foreach ($fields[$name] as $priceData) {
                    $priceModel = isset($priceData['id'])
                        ? $existingPrices->where('id', $priceData['id'])->first()
                        : null;
                    $data = array_merge_recursive_preserve($defaultPriceAttributes, $priceData + ['role' => $name]);

                    if ($onlyBaseCurrency) {
                        foreach ([1, 2, 3] as $key => $id) {
                            $_currency = Currency::find($id);
                            if ($_currency->iso_4217 !== $baseCurrency) {
                                $_data = array_merge($data, [
                                    $priceSavingKey => round(CurrencyExchange::convertTo($data[$priceSavingKey], $_currency->iso_4217), 2),
                                    'currency_id' => $_currency->id,
                                ]);

                                if ($existingPrices->where('currency_id', $_currency->id)->count() == 0) {
                                    $object->prices()->create(Arr::except($_data, ['id']));
                                } else {
                                    $existingPrices->where('currency_id', $_currency->id)->first()->update(Arr::except($_data, ['id']));
                                }
                            }
                        }
                    }

                    if ($priceModel) {
                        // Update existing price
                        $priceModel->update($data);
                    } else {
                        // Create a new price
                        $object->prices()->create(Arr::except($data, ['id']));
                    }

                }

                if ($existingPrices && ! $onlyBaseCurrency) {
                    $pricesToDelete = $existingPrices->whereNotIn('id', Arr::pluck($fields[$name], 'id'));
                    $pricesToDelete->each->delete();
                }
            }
        }

    }

    /**
     * @param \Unusualify\Modularity\Entities\Model $object
     * @param array $fields
     * @return array
     */
    public function getFormFieldsPricesTrait($object, $fields)
    {
        if (method_exists($object, 'prices') && $object->has('prices')) {
            $priceSavingKey = Price::$priceSavingKey;
            $onlyBaseCurrency = modularityConfig('services.currency_exchange.active');
            $priceModel = $object->prices()->getRelated();
            $defaultPriceAttributes = $priceModel->defaultAttributes();

            $query = $object->prices();

            if ($onlyBaseCurrency) {
                $query = $query->where('currency_id', Request::getUserCurrency()->id);
            }

            $prices = $query->get();
            $pricesByRole = $prices->groupBy('role');
            // dd($prices, $pricesByRole);

            foreach ($this->getColumns(__TRAIT__) as $role) {
                if (isset($pricesByRole[$role])) {
                    $fields[$role] = $pricesByRole[$role]->map(function ($price) use ($priceSavingKey) {
                        return Arr::mapWithKeys(Arr::only($price->toArray(), array_merge($this->formatableColumns, [$priceSavingKey])), function ($val, $key) use ($priceSavingKey) {
                            if (preg_match('/display_price|price_excluding|price_including|raw_amount|' . $priceSavingKey . '/', $key)) {
                                return [$key => (float) $val];
                            }

                            return [$key => $val];
                        });
                    });
                } else {
                    $fields[$role] = [
                        array_merge_recursive_preserve($defaultPriceAttributes, [
                            $priceSavingKey => 0.00,
                            'raw_amount' => 0.00,
                            'currency_id' => Request::getUserCurrency()->id]
                        ),
                    ];
                }
            }

            // foreach ($object->prices->groupBy('role') as $role => $pricesByRole) {
            //     $fields[$role] = $pricesByRole->map(function ($price) {
            //         return Arr::only($price->toArray(), $this->formatableColumns);
            //     });
            //     // foreach ($pricesByRole->groupBy('pivot.locale') as $locale => $filesByLocale) {
            //     //     $fields[$role][$locale] = $filesByLocale->map(function ($file) {
            //     //         return $file->mediableFormat();
            //     //     });
            //     // }
            // }
        }

        return $fields;
    }
}
