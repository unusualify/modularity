<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Arr;

trait ProcessableTrait
{
    /**
     * @param array $columns
     * @param array $inputs
     * @return array
     */
    public function setColumnsProcessableTrait($columns, $inputs)
    {
        $traitName = get_class_short_name(__TRAIT__);

        $_columns = collect($inputs)->reduce(function ($acc, $curr) {
            if (preg_match('/process/', $curr['type'])) {
                $acc[] = $curr['name'];
            }

            return $acc;
        }, []);

        $columns[$traitName] = array_unique(array_merge($this->traitColumns[$traitName] ?? [], $_columns));

        return $columns;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @param array $schema
     * @return array
     */
    public function getFormFieldsProcessableTrait($object, $fields, $schema = [])
    {
        // set, cast, unset or manipulate the fields by using object, fields and schema
        if ($object->exists && classHasTrait($object, 'Unusualify\Modularity\Entities\Traits\Processable')) {
            foreach ($this->getColumns(__TRAIT__) as $column) {
                $fields[$column] = $this->getProcessId($object);

                $processInput = Arr::first($schema, fn ($item) => $item['name'] == $column);

                if($processInput && $processInput['type'] == 'process' && isset($processInput['schema'])){

                    $processableSchema = [];
                    foreach($processInput['schema'] as $input){
                        if(isset($input['name']) && !isset($fields[$input['name']])){
                            $processableSchema[] = $input;
                        }
                    }
                    if(count($processableSchema) > 0){
                        $processableSchema = collect($processableSchema)->mapWithKeys(fn($item) => [$item['name'] => $item])->toArray();
                        $processableFields = $this->getFormFields($object, $processableSchema, noSerialization: true);
                        $fields = array_merge($processableFields, $fields);
                    }

                }
                // dd($schema, $column, $fields);
            }
        }

        return $fields;
    }

    /**
     * Get the process for this model, or create it if it doesn't exist
     *
     * @param string $status Initial status for new process
     * @return \Modularity\Entities\Models\Process
     */
    public function getProcessId($object, string $status = 'preparing')
    {

        if ($object->exists && !$object->process()->exists()) {

            // Create a new process with the given status
            $process = $object->process()->create([
                'status' => $status,
                'name' => class_basename($object) . ' Process'
            ]);

            // // Refresh the relationship
            // $object->load('process');

            return $process->id;
        }

        return $object->process()->select('id')->value('id');
    }
}
