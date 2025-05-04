<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Arr;

trait SpreadableTrait
{

    protected function setColumnsSpreadableTrait($columns, $inputs)
    {
        $traitName = get_class_short_name(__TRAIT__);

        $columns[$traitName] = collect($inputs ?? $this->inputs())->reduce(function ($acc, $input) {
            if (isset($input['name']) && preg_match('/^.*spread$/', $input['type'])) {
                $acc[] = $input['name'];
            }

            return $acc;
        }, []);

        return $columns;
    }
    protected function beforeSaveSpreadableTrait($object, $fields)
    {
        // Get the spreadable model instance
        $spreadableModel = $object->spreadable()->first();

        if (!$spreadableModel && $object->exists) {
            $object->spreadable()->create();
        }

        if (!$spreadableModel) {
            return;
        }

        $spreadableSavingKey = $object->getSpreadableSavingKey();
        $currentJson = $spreadableModel->content;
        $newJson = array_merge($currentJson, $fields[$spreadableSavingKey] ?? []);

        // Update the spreadable JSON
        foreach ($this->getSpreadableInputKeys($this->inputs()) as $key) {
            if (isset($fields[$key])) {
                $newJson[$key] = $fields[$key];
            }
        }

        $object->setAttribute($spreadableSavingKey, $newJson);
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return array
     */
    protected function prepareFieldsBeforeSaveSpreadableTrait($object, $fields)
    {
        // Get spreadable fields from the model
        $spreadableFields = $this->getSpreadableInputKeys($this->inputs());

        // Initialize _spread array if it doesn't exist
        $fields[$object->getSpreadableSavingKey()] = $fields[$object->getSpreadableSavingKey()] ?? $object->{$object->getSpreadableSavingKey()};

        // Process each field
        foreach ($fields as $key => $value) {
            // Check if the field is spreadable
            if (in_array($key, $spreadableFields)) {
                // Add to _spread array
                $fields[$object->getSpreadableSavingKey()][$key] = $value;

                // Remove from main fields array
                unset($fields[$key]);
            }
        }

        // dd($fields, $object);

        return $fields;
    }

    protected function getFormFieldsSpreadableTrait($object, $fields, $schema)
    {
        $schema = empty($schema) ? $this->model->getRouteInputs() : $schema;

        if ($object->spreadable()->exists()) {
            $columns = $this->getColumns(__TRAIT__);

            foreach ($columns as $column) {
                // $fields[$column] = $object->spreadable->content[$column] ?? null;
                $fields[$object->getSpreadableSavingKey()] = Arr::except($object->spreadable->content ?? [], $this->getSpreadableInputKeys($schema));
            }
        }

        return $fields;
    }

    /**
     * Get the spreadable fields from the model
     *
     * @return array
     */
    protected function getSpreadableInputKeys($schema)
    {
        // Filter and return fields that are marked as spreadable
        return collect($schema)
            ->filter(function ($input) {
                return isset($input['name']) && isset($input['spreadable']) && $input['spreadable'] === true;
            })
            ->pluck('name')
            ->toArray();
    }
}
