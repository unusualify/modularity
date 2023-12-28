<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Unusualify\Modularity\Entities\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Module;

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
        'price_type_id' => 1
    ];

    /**
     * @param \Unusualify\Modularity\Entities\Model $object
     * @param array $fields
     * @return \Unusualify\Modularity\Entities\Model
     */
    public function hydratePricesTrait($object, $fields)
    {
        // if ($this->shouldIgnoreFieldBeforeSave('files')) {
        //     return $object;
        // }

        // $filesCollection = Collection::make();
        // $filesFromFields = $this->getPrices($fields);

        // $filesFromFields->each(function ($file) use ($object, $filesCollection) {
        //     $newFile = File::withTrashed()->find($file['id']);
        //     $pivot = $newFile->newPivot($object, Arr::except($file, ['id']), 'fileables', true);
        //     $newFile->setRelation('pivot', $pivot);
        //     $filesCollection->push($newFile);
        // });

        // $object->setRelation('files', $filesCollection);

        return $object;
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

        // dd(
        //     $object->prices,
        //     $this->getPriceColumns()
        // );

        foreach( $this->getPriceColumns() as $name ) {
            if(isset($fields[$name])){
                $existingPrices = $object->prices()->where('role', $name)->get();

                foreach ($fields[$name] as $priceData) {
                    $priceModel = isset($priceData['id']) ? $existingPrices->where('id', $priceData['id'])->first() : null;

                    $data = array_merge_recursive_preserve($this->defaultPriceData, $priceData + ['role' => $name]);

                    if ($priceModel) {
                        // Update existing price
                        $priceModel->update($data);
                    } else {
                        // Create a new price
                        $object->prices()->create($data);
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

            foreach ($this->getPriceColumns() as $role) {
                if(isset($pricesByRole[$role])){
                    $fields[$role] = $pricesByRole[$role]->map(function ($price) {
                        return Arr::only($price->toArray(), $this->formatableColumns);
                    });
                }else {
                    $fields[$role] = [
                        array_merge_recursive_preserve($this->defaultPriceData, ['display_price' => ''])
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

    public function getPriceColumns()
    {
        return collect($this->inputs())->reduce(function($acc, $curr){
            if(preg_match('/price/', $curr['type'])){
                $acc[] = $curr['name'];
            }

            return $acc;
        }, []);
    }
}
