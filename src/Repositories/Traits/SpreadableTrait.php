<?php

namespace Unusualify\Modularity\Repositories\Traits;

trait SpreadableTrait
{
    /**
     * Prepare the spreadable JSON data by removing all protected attributes
     * from the model's attributes.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $object
     * @return array
     */
    protected function prepareSpreadableJson($object): array
    {
        // Get all model attributes
        $attributes = $object->getAttributes();
        // Build list of protected keys (assume that the model has methods getColumns, definedRelations, and getMutatedAttributes)
        $protectedKeys = array_merge(
            $object->getColumns(),         // Columns from the model (provided by ManageEloquent, for example)
            $object->definedRelations(),   // Relationship names defined on the model
            array_keys($object->getMutatedAttributes()),
            ['spreadable', '_spread']      // Hard-coded reserved keys
        );
        // Return attributes minus the protected ones
        return array_diff_key($attributes, array_flip($protectedKeys));
    }

    /**
     * Recursively scan the model's route inputs for fields marked as spreadable.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $object
     * @return array
     */
    public function getSpreadableInputKeys($object): array
    {
        $spreadableFields = [];

        // Recursive helper closure to scan the fields.
        $findSpreadableFields = function($fields) use (&$spreadableFields, &$findSpreadableFields) {
            foreach ($fields as $field) {
                if (isset($field['spreadable']) && $field['spreadable'] === true) {
                    $spreadableFields[] = $field['name'];
                }
                if (isset($field['schema'])) {
                    $findSpreadableFields($field['schema']);
                }
                if (is_array($field) && !isset($field['type'])) {
                    $findSpreadableFields($field);
                }
            }
        };

        // Assume the model provides a getRouteInputs() method that returns the input definitions.
        $findSpreadableFields($object->getRouteInputs());

        return array_unique($spreadableFields);
    }

    /**
     * Rebuild the _spread attribute from the spreadable fillable inputs.
     * Also removes these keys from the main $fields array.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $object
     * @param  array  $fields   All fillable input data (passed by reference)
     * @return array           The rebuilt spread data
     */
    protected function updateSpreadableFromFillable($object, array $fields): array
    {
        $spreadableKeys = $this->getSpreadableInputKeys($object);
        $spreadData = [];

        foreach ($spreadableKeys as $key) {
            if (array_key_exists($key, $fields)) {
                $spreadData[$key] = $fields[$key];
                // Remove the attribute from the main fields array to prevent duplicate storage
                unset($fields[$key]);
            }
        }

        // Update the model's _spread attribute
        $object->setAttribute('_spread', $spreadData);

        return $spreadData;
    }

    /**
     * Remove all spreadable attributes (including _spread and spreadable)
     * from the model's attributes.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $object
     * @return void
     */
    public function cleanSpreadableAttributes($object): void
    {
        $spreadableKeys = array_merge(
            $this->getSpreadableInputKeys($object),
            ['_spread', 'spreadable']
        );

        foreach ($spreadableKeys as $key) {
            $object->offsetUnset($key);
        }
    }


    protected function beforeSaveSpreadableTrait($object, $fields)
    {
        // Get the spreadable model instance
        $spreadableModel = $object->spreadable()->first();
        // dd($fields);
        $currentJson = $spreadableModel->content;
        $newJson = array_merge(
            $currentJson,
            isset($fields['_spread']) ? $fields['_spread'] : []
        );

        // Update with individual spreadable fields if they are in $fields
        foreach ($this->getSpreadableInputKeys($object) as $key) {
            if (isset($fields[$key])) {
                $newJson[$key] = $fields[$key];
            }
        }
        $this->cleanSpreadableAttributes($object);
        // dd($object);
        $object->setAttribute('_spread', $newJson);

    }

    /**
     * Prepare the fields before saving by extracting spreadable data.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $object
     * @param  array  $fields
     * @return array
     */
    protected function prepareFieldsBeforeSaveSpreadableTrait($object, $fields)
    {
        $spreadableFields = $this->getSpreadableInputKeys($object);

        // Initialize _spread if not already set
        $fields['_spread'] = $fields['_spread'] ?? $object->_spread;

        // Process each field to see if it is spreadable
        foreach ($fields as $key => $value) {
            if (in_array($key, $spreadableFields)) {
                // Add the field to _spread and remove it from main fields
                $fields['_spread'][$key] = $value;
                unset($fields[$key]);
            }
        }
        return $fields;
    }
    protected function prepareFieldsBeforeCreateSpreadableTrait($fields){
        if(isset($fields['_spread'])){
            return $fields;
        }
        return $this->prepareFieldsBeforeSaveSpreadableTrait($this->model, $fields);
    }


}
