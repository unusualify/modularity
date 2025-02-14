<?php

namespace Unusualify\Modularity\Repositories\Traits;

trait SpreadableTrait
{

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

        //TODO: Check if the key exists in getSpreadableReserveredKeywords if it does don't return the key
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

    protected function afterSaveSpreadableTrait($object, $fields)
    {
        // Get the spreadable model instance
        $spreadModel = $object->spreadable()->first();

        // Update with individual spreadable fields if they are in $fields
        $newContent = [];
        foreach ($this->getSpreadableInputKeys($object) as $key) {
            if (isset($fields[$key])) {
                $newContent[$key] = $fields[$key];
            }
        }

        $newContent = array_merge(
            $spreadModel->content,
            $newContent
        );

        $object->spreadable()->update([
            'content' => $newContent
        ]);

        // $this->cleanSpreadableAttributes($object);
        // // dd($object);
        // $object->setAttribute('_spread', $newJson);

    }

}
