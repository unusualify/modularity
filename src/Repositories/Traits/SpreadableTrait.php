<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;
use Oobook\Priceable\Models\Price;

trait SpreadableTrait
{
    protected function beforeSaveSpreadTrait($object, $fields)
    {
        // Get the spreadable model instance
        // dd($object->_spread);
        // $spreadableModel = $object->spreadable;
        $spreadableModel = $object->spreadable()->first();

        // if (!$spreadableModel) {
        //     return;
        // }
        $currentJson = $spreadableModel->json;
        $newJson = array_merge($currentJson, $fields['_spread'] ?? []);
        // Update the spreadable JSON

        foreach($this->getSpreadableInputKeys() as $key){
            if(isset($fields[$key])){
                $newJson[$key] = $fields[$key];
            }
        }
        // $object->_spread = $newJson;
        // dd($object->getFillable());
        $object->setAttribute('_spread', $newJson);
        // dd('here');
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return array
     */
    protected function prepareFieldsBeforeSaveSpreadTrait($object, $fields)
    {
        // Get current JSON data
        // $currentJson = json_decode($object->spreadable->json ?? '{}', true);

        // Get spreadable fields from the model
        $spreadableFields = $this->getSpreadableInputKeys($object);

        // Initialize _spread array if it doesn't exist
        $fields['_spread'] = $fields['_spread'] ?? $object->_spread;

        // Process each field
        foreach ($fields as $key => $value) {
            // Check if the field is spreadable
            if (in_array($key, $spreadableFields)) {
                // Add to _spread array
                // dd($key,$value);
                $fields['_spread'][$key] = $value;

                // Remove from main fields array
                unset($fields[$key]);

                // Update current JSON if the field exists
                // if (isset($currentJson[$key])) {
                //     $currentJson[$key] = $value;
                // }
            }
        }

        return $fields;
    }



    /**
     * Get the spreadable fields from the model
     * @return array
     */
    protected function getSpreadableInputKeys()
    {
        // Filter and return fields that are marked as spreadable

        return collect($this->model->getRouteInputs())
            ->filter(function ($field) {
                return isset($field['spreadable']) && $field['spreadable'] === true;
            })
            ->pluck('name')
            ->toArray();
    }
}
