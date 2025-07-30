<?php

namespace Unusualify\Modularity\Repositories\Traits;

trait DemandableTrait
{
    /**
     * @param array $columns
     * @param array $inputs
     * @return array
     */
    public function setColumnsDemandableTrait($columns, $inputs)
    {
        $traitName = get_class_short_name(__TRAIT__);

        $columns[$traitName] = [];

        return $columns;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function prepareFieldsBeforeCreateDemandableTrait($fields)
    {
        // set, cast or unset related fields in order to prepare if necessary

        return $fields;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return string[]
     */
    public function prepareFieldsBeforeSaveDemandableTrait($object, $fields)
    {
        // set, cast or unset related fields in order to prepare if necessary

        return $fields;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return void
     */
    public function afterUpdateBasicDemandableTrait($object, $fields)
    {
        // do something after the updateBasic method of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return void
     */
    public function beforeSaveDemandableTrait($object, $fields)
    {
        // do something before the create, update or duplicate methods of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return void
     */
    public function afterSaveDemandableTrait($object, $fields)
    {
        // do something after the create, update or duplicate methods of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @return void
     */
    public function afterDeleteDemandableTrait($object)
    {
        // do something after the delete method of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @return void
     */
    public function afterForceDeleteDemandableTrait($object)
    {
        // do something after the forceDelete, bulkForceDelete methods of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @return void
     */
    public function afterRestoreDemandableTrait($object)
    {
        // do something after the restore, bulkRestore methods of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return \Unusualify\Modularity\Models\Model
     */
    public function hydrateDemandableTrait($object, $fields)
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
    public function getFormFieldsDemandableTrait($object, $fields, $schema = [])
    {
        // set, cast, unset or manipulate the fields by using object, fields and schema

        return $fields;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @param array $schema
     * @return array
     */
    public function getShowFieldsDemandableTrait($object, $fields, $schema = [])
    {
        // set, cast, unset or manipulate the fields by using object, fields and schema

        return $fields;
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $scopes
     * @return void
     */
    public function filterDemandableTrait($query, &$scopes)
    {
        // set, cast, unset or manipulate the scopes by using query and scopes
        // $scopes['filter'] = true;
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $orders
     * @return void
     */
    public function orderDemandableTrait($query, &$orders)
    {
        // set, cast, unset or manipulate the orders by using query and orders
        // $orders['order'] = true;
    }
}
