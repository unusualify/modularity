<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Arr;

trait PricesTrait
{
    protected $formatableColumns = [
        'id',
        'display_price',
        'currency_id',
        'vat_rate_id',
        'price_type_id',
    ];

    protected $defaultPriceData = [
        'currency_id' => 1,
        'vat_rate_id' => 1,
        'price_type_id' => 1,
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

        foreach ($this->getColumns(__TRAIT__) as $name) {
            if (isset($fields[$name])) {
                $existingPrices = $object->prices()->where('role', $name)->get();

                foreach ($fields[$name] as $priceData) {
                    $priceModel = isset($priceData['id'])
                        ? $existingPrices->where('id', $priceData['id'])->first()
                        : null;

                    $data = array_merge_recursive_preserve($this->defaultPriceData, $priceData + ['role' => $name]);

                    if ($priceModel) {
                        // Update existing price
                        $priceModel->update($data);
                    } else {
                        // Create a new price
                        $object->prices()->create(Arr::except($data, ['id']));
                    }
                }

                if ($existingPrices) {
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
        if ($object->has('prices')) {
            $pricesByRole = $object->prices->groupBy('role');

            foreach ($this->getColumns(__TRAIT__) as $role) {
                if (isset($pricesByRole[$role])) {
                    $fields[$role] = $pricesByRole[$role]->map(function ($price) {
                        return Arr::mapWithKeys(Arr::only($price->toArray(), $this->formatableColumns), function ($val, $key) {
                            if (preg_match('/display_price|price_excluding|price_including/', $key)) {
                                return [$key => (float) $val / 100];
                            }

                            return [$key => $val];
                        });
                    });
                } else {
                    $fields[$role] = [
                        array_merge_recursive_preserve($this->defaultPriceData, ['display_price' => 0.00]),
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

    public function getShowFieldsPricesTrait($object, $fields, $schema = [])
    {
        if ($object->has('prices')) {
            foreach ($this->getColumns(__TRAIT__) as $fieldName) {
                $fields[$fieldName . '_show'] = $object->price_formatted;
            }
        }

        return $fields;
    }
}
