<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Unusualify\Modularity\Entities\Assignment;

trait AssignmentTrait
{
    /**
     * @param array $columns
     * @param array $inputs
     * @return array
     */
    public function setColumnsAssignmentTrait($columns, $inputs)
    {
        $traitName = get_class_short_name(__TRAIT__);

        $_columns = collect($inputs)->reduce(function ($acc, $curr) {
            if (preg_match('/assignment/', $curr['type'])) {
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
    public function prepareFieldsBeforeCreateAssignmentTrait($fields)
    {
        // set, cast or unset related fields in order to prepare if necessary

        return $fields;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return string[]
     */
    public function prepareFieldsBeforeSaveAssignmentTrait($object, $fields)
    {
        // set, cast or unset related fields in order to prepare if necessary

        return $fields;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return void
     */
    public function afterUpdateBasicAssignmentTrait($object, $fields)
    {
        // do something after the updateBasic method of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return void
     */
    public function beforeSaveAssignmentTrait($object, $fields)
    {
        // do something before the create, update or duplicate methods of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return void
     */
    public function afterSaveAssignmentTrait($object, $fields)
    {
        // do something after the create, update or duplicate methods of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @return void
     */
    public function afterDeleteAssignmentTrait($object)
    {
        // do something after the delete method of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @return void
     */
    public function afterForceDeleteAssignmentTrait($object)
    {
        // do something after the forceDelete, bulkForceDelete methods of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @return void
     */
    public function afterRestoreAssignmentTrait($object)
    {
        // do something after the restore, bulkRestore methods of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return \Unusualify\Modularity\Models\Model
     */
    public function hydrateAssignmentTrait($object, $fields)
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
    public function getFormFieldsAssignmentTrait($object, $fields, $schema = [])
    {
        // set, cast, unset or manipulate the fields by using object, fields and schema

        $columns = $this->getColumns(__TRAIT__);

        foreach ($columns as $column) {
            $fields[$column] = $object->getKey();
        }

        return $fields;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @param array $schema
     * @return array
     */
    public function getShowFieldsAssignmentTrait($object, $fields, $schema = [])
    {
        // set, cast, unset or manipulate the fields by using object, fields and schema

        return $fields;
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $scopes
     * @return void
     */
    public function filterAssignmentTrait($query, &$scopes)
    {
        // set, cast, unset or manipulate the scopes by using query and scopes
        $scopes['isAssignee'] = true;
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $orders
     * @return void
     */
    public function orderAssignmentTrait($query, &$orders)
    {
        // set, cast, unset or manipulate the orders by using query and orders
        // $orders['order'] = true;
    }

    public function getAssignments($id)
    {
        $assignments = Assignment::where('assignable_id', $id)
            ->where('assignable_type', get_class($this->model))
            ->orderBy('created_at', 'desc')
            ->get();

        return $assignments;
    }
}
