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
     * @param array $fields
     * @return array
     */
    public function prepareFieldsBeforeCreateProcessableTrait($fields)
    {
        // set, cast or unset related fields in order to prepare if necessary

        return $fields;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return string[]
     */
    public function prepareFieldsBeforeSaveProcessableTrait($object, $fields)
    {
        // set, cast or unset related fields in order to prepare if necessary

        return $fields;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return void
     */
    public function afterUpdateBasicProcessableTrait($object, $fields)
    {
        // do something after the updateBasic method of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return void
     */
    public function beforeSaveProcessableTrait($object, $fields)
    {
        // do something before the create, update or duplicate methods of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return void
     */
    public function afterSaveProcessableTrait($object, $fields)
    {
        // do something after the create, update or duplicate methods of repository
        if (classHasTrait($object, 'Unusualify\Modularity\Entities\Traits\Processable')) {
            // foreach ($this->getColumns(__TRAIT__) as $column) {
            //     if(isset($fields[$column])){
            //         $object->processable()->updateOrCreate([
            //             'process_id' => $object->process_id,
            //         ], $fields[$column]);
            //     }
            // }
        }
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @return void
     */
    public function afterDeleteProcessableTrait($object)
    {
        // do something after the delete method of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @return void
     */
    public function afterForceDeleteProcessableTrait($object)
    {
        // do something after the forceDelete, bulkForceDelete methods of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @return void
     */
    public function afterRestoreProcessableTrait($object)
    {
        // do something after the restore, bulkRestore methods of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return \Unusualify\Modularity\Models\Model
     */
    public function hydrateProcessableTrait($object, $fields)
    {
        // change, cast or set related fields in order to hydrate the object Model

        return $object;
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
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @param array $schema
     * @return array
     */
    public function getShowFieldsProcessableTrait($object, $fields, $schema = [])
    {
        // set, cast, unset or manipulate the fields by using object, fields and schema

        return $fields;
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $scopes
     * @return void
     */
    public function filterProcessableTrait($query, &$scopes)
    {
        // set, cast, unset or manipulate the scopes by using query and scopes
        // $scopes['filter'] = true;
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $orders
     * @return void
     */
    public function orderProcessableTrait($query, &$orders)
    {
        // set, cast, unset or manipulate the orders by using query and orders
        // $orders['order'] = true;
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
