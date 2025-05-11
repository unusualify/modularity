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
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $scopes
     * @return void
     */
    public function filterAssignmentTrait($query, &$scopes)
    {
        // set, cast, unset or manipulate the scopes by using query and scopes
        $scopes['everAssignedToYourRoleOrHasAuthorization'] = true;
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
