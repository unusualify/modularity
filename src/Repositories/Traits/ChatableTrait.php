<?php

namespace Unusualify\Modularity\Repositories\Traits;

trait ChatableTrait
{
    /**
     * @param array $columns
     * @param array $inputs
     * @return array
     */
    public function setColumnsChatableTrait($columns, $inputs)
    {
        $traitName = get_class_short_name(__TRAIT__);

        $columns[$traitName] = [];

        return $columns;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function prepareFieldsBeforeCreateChatableTrait($fields)
    {
        // set, cast or unset related fields in order to prepare if necessary

        return $fields;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return string[]
     */
    public function prepareFieldsBeforeSaveChatableTrait($object, $fields)
    {
        // set, cast or unset related fields in order to prepare if necessary

        return $fields;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return void
     */
    public function afterUpdateBasicChatableTrait($object, $fields)
    {
        // do something after the updateBasic method of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return void
     */
    public function beforeSaveChatableTrait($object, $fields)
    {
        // do something before the create, update or duplicate methods of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return void
     */
    public function afterSaveChatableTrait($object, $fields)
    {
        // do something after the create, update or duplicate methods of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @return void
     */
    public function afterDeleteChatableTrait($object)
    {
        // do something after the delete method of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @return void
     */
    public function afterForceDeleteChatableTrait($object)
    {
        // do something after the forceDelete, bulkForceDelete methods of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @return void
     */
    public function afterRestoreChatableTrait($object)
    {
        // do something after the restore, bulkRestore methods of repository
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return \Unusualify\Modularity\Models\Model
     */
    public function hydrateChatableTrait($object, $fields)
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
    public function getFormFieldsChatableTrait($object, $fields, $schema = [])
    {
        // set, cast, unset or manipulate the fields by using object, fields and schema
        // dd($fields);
        return $fields;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @param array $schema
     * @return array
     */
    public function getShowFieldsChatableTrait($object, $fields, $schema = [])
    {
        // set, cast, unset or manipulate the fields by using object, fields and schema

        return $fields;
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $scopes
     * @return void
     */
    public function filterChatableTrait($query, &$scopes)
    {
        // set, cast, unset or manipulate the scopes by using query and scopes
        // $scopes['filter'] = true;
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $orders
     * @return void
     */
    public function orderChatableTrait($query, &$orders)
    {
        // set, cast, unset or manipulate the orders by using query and orders
        // $orders['order'] = true;
    }
}
