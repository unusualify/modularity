<?php

namespace Unusualify\Modularity\Repositories\Logic;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Unusualify\Modularity\Traits\ManageTraits;

trait MethodTransformers
{
    use ManageTraits;

    /**
     * @var array
     */
    protected $countScope = [];

    /**
     * @return array
     */
    public function getColumns(?string $trait = null)
    {
        preg_match('/\/([A-Za-z]*)\.php/', debug_backtrace()[0]['file'], $matches);

        $traitName = $trait ? get_class_short_name($trait) : $matches[1];

        return $this->traitColumns[$traitName] ?? [];
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return array
     */
    public function cleanupFields($object, $fields)
    {
        if (property_exists($this->model, 'checkboxes')) {
            foreach ($this->model->checkboxes as $field) {
                if (! $this->shouldIgnoreFieldBeforeSave($field)) {
                    if (! isset($fields[$field])) {
                        $fields[$field] = false;
                    } else {
                        $fields[$field] = ! empty($fields[$field]);
                    }
                }
            }
        }

        if (property_exists($this->model, 'nullable')) {
            foreach ($this->model->nullable as $field) {
                if (! isset($fields[$field]) && ! $this->shouldIgnoreFieldBeforeSave($field)) {
                    $fields[$field] = null;
                }
            }
        }

        foreach ($fields as $key => $value) {
            if (! $this->shouldIgnoreFieldBeforeSave($key)) {
                // if (is_array($value) && empty($value)) {
                //     dd($value, $key, empty($value));
                //     $fields[$key] = null;
                // }
                // if ($value === '') {
                //     $fields[$key] = null;
                // }
            }
        }

        return $fields;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @return array
     */
    public function castFormFields($fields)
    {
        foreach ($this->chunkInputs($this->inputs()) as $input) {
            if (isset($input['ext'])) {
                switch ($input['ext']) {
                    case 'date':
                        // code...
                        $fields[$input['name']] = '';

                        break;

                    default:
                        // code...
                        break;
                }
            }
        }

        return $fields;
    }

    /**
     * @param array $columns
     * @param array $inputs
     * @return array
     */
    public function setColumns($columns, $inputs)
    {
        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $columns = $this->$method($columns, $inputs);
        }

        return $columns;
    }

    /**
     * @param string $traitClass
     * @param string $inputName
     * @return bool
     */
    public function traitHasInput($traitClass, $inputName)
    {
        return in_array($inputName, $this->getColumns($traitClass));
    }

    /**
     * @param array $traitClasses
     * @param string $inputName
     * @return bool
     */
    public function anyTraitHasInput($traitClasses, $inputName)
    {
        foreach ($traitClasses as $traitClass) {
            if ($this->traitHasInput($traitClass, $inputName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $slug
     * @param array $scope
     * @return int
     */
    public function getCountByStatusSlug($slug, $scope = [])
    {
        $this->countScope = $scope;

        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            if (($count = $this->$method($slug)) !== false) {
                return $count;
            }
        }

        switch ($slug) {
            case 'all':
                return $this->getCountForAll();
            case 'published':
                return $this->getCountForPublished();
            case 'draft':
                return $this->getCountForDraft();
            case 'trash':
                return $this->getCountForTrash();
        }

        return 0;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function prepareFieldsBeforeCreate($fields)
    {
        $fields = $this->cleanupFields(null, $fields);

        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $fields = $this->$method($fields);
        }

        return $fields;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return string[]
     */
    public function prepareFieldsBeforeSave($object, $fields)
    {
        $fields = $this->cleanupFields($object, $fields);

        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $fields = $this->$method($object, $fields);
        }

        return $fields;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return void
     */
    public function afterUpdateBasic($object, $fields)
    {
        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $this->$method($object, $fields);
        }
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return void
     */
    public function beforeSave($object, $fields)
    {
        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $this->$method($object, $fields);
        }
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return void
     */
    public function afterSave($object, $fields)
    {
        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $this->$method($object, $fields);
        }
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @return void
     */
    public function afterDelete($object)
    {
        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $this->$method($object);
        }
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @return void
     */
    public function afterForceDelete($object)
    {
        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $this->$method($object);
        }
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @return void
     */
    public function afterRestore($object)
    {
        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $this->$method($object);
        }
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return \Unusualify\Modularity\Models\Model
     */
    public function hydrate($object, $fields)
    {
        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $object = $this->$method($object, $fields);
        }

        return $object;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @return array
     */
    public function getFormFields($object, $schema = [], $noSerialization = false)
    {
        $chunkedInputs = $this->chunkInputs(all: true, schema: empty($schema) ? null : $schema);

        $this->traitColumns = $this->setColumns($this->traitColumns, $chunkedInputs);

        if (! $noSerialization) {
            $fields = $object->attributesToArray();
        } else {
            $fields = [];
        }

        // $fields = $this->castFormFields($fields);

        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $fields = $this->$method($object, $fields, $schema);
        }
        if (! empty($fields)) {
            // dd($schema, $fields);
            $fields = Collection::make($fields)->reduce(function ($acc, $value, $key) {
                Arr::set($acc, $key, $value);

                return $acc;
            }, []);
            // dd($fields);
        }

        return $fields;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $schema
     * @return array
     */
    public function getShowFields($object, $schema = [])
    {
        $chunkedInputs = $this->chunkInputs(all: true, schema: empty($schema) ? null : $schema);

        $this->traitColumns = $this->setColumns($this->traitColumns, $chunkedInputs);

        if (method_exists($object, 'setRelationsShowFormat')) {
            $object->setRelationsShowFormat();
        }

        $fields = $object->attributesToArray();

        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $fields = $this->$method($object, $fields, $schema);
        }

        return $fields;
    }

    /**
     * Get Table Default Filters on the Route Controller
     *
     * @return array
     */
    public function getTableFilters($scope = [])
    {
        $tableFilters = [];

        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $tableFilters = array_merge($tableFilters, $this->$method($scope));
        }

        return $tableFilters;
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function filter($query, array $scopes = [])
    {
        $likeOperator = $this->getLikeOperator();

        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $this->$method($query, $scopes);
        }

        $searchesFields = $scopes['searches'] ?? [];
        unset($scopes['searches'], $scopes['search']);
        foreach ($searchesFields as $field) {
            if (array_key_exists($field, $scopes)) {
                unset($scopes[$field]);
            }
        }

        if (isset($scopes['exceptIds'])) {
            $query->whereNotIn($this->model->getTable() . '.id', $scopes['exceptIds']);
            unset($scopes['exceptIds']);
        }

        $_scopes = $scopes;

        foreach ($_scopes as $column => $value) {
            $studlyColumn = studlyName($column);

            if (preg_match('/addRelation([A-Za-z]+)/', $column, $matches)) {
                $relationName = $this->getCamelCase($matches[1]);

                if (method_exists($this->getModel(), $relationName)) {
                    $related = $this->getModel()->{$relationName}();

                    if ($related instanceof \Illuminate\Database\Eloquent\Relations\MorphTo) {
                        // Handle morphTo relationship
                        $morphType = $related->getMorphType(); // Gets the type column (e.g., 'modelable_type')
                        $morphId = $related->getForeignKeyName(); // Gets the id column (e.g., 'modelable_id')

                        $morphFilters = array_reduce($value, function ($acc, $item) {
                            if (isset($item['type']) && isset($item['id'])) {
                                $acc[] = $item;
                            }

                            return $acc;
                        }, []);

                        if (count($morphFilters) > 0) {
                            $type = $morphFilters[0]['type'];
                            $values = array_map(function ($item) {
                                return $item['id'];
                            }, $morphFilters);
                            $query->where($morphType, $type)
                                ->whereIn($morphId, $values);
                        }

                    } else {
                        // Handle belongsTo relationship
                        if (method_exists(__CLASS__, $method = 'getForeignKey' . get_class_short_name($related))) {
                            $foreignKey = $this->$method($related, $scopes, $value);
                        }
                        $scopes[$foreignKey] = $value;
                        $this->addRelationFilterScope($query, $scopes, $foreignKey, $relationName);
                    }
                }

                unset($scopes[$column]);
            }
        }

        foreach ($scopes as $column => $values) {
            $studlyColumn = studlyName($column);

            $value = $values;
            $arguments = [];

            if (is_string($values)) {
                $exploded = explode('^', $values);
                $value = $exploded[0];

                if (count($exploded) > 1) {
                    // dd($exploded);
                    $arguments = explode(';', $exploded[1]);
                }
            }

            if ($this->model->hasScope($column) ) {
                if (! is_bool($value)) {
                    $query->{$this->getCamelCase($column)}($value);
                } else {
                    $query->{$this->getCamelCase($column)}();
                }
            } elseif (is_string($value) && $this->model->hasScope($value)) {
                // $query->{$this->getCamelCase($value)}(...$arguments);
                $query->{$this->getCamelCase($value)}(...$arguments);
            } else {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } elseif ($column[0] == '%') {
                    $value && ($value[0] == '!') ? $query->where(mb_substr($column, 1), "not $likeOperator", '%' . mb_substr($value, 1) . '%') : $query->where(mb_substr($column, 1), $likeOperator, '%' . $value . '%');
                } elseif (isset($value[0]) && $value[0] == '!') {
                    $query->where($column, '<>', mb_substr($value, 1));
                } elseif ($value !== '') {
                    $query->where($column, $value);
                }
            }
        }

        return $query;
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function filterBack($query, array $scopes = [])
    {
        $likeOperator = $this->getLikeOperator();

        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $this->$method($query, $scopes);
        }

        unset($scopes['search']);

        if (isset($scopes['exceptIds'])) {
            $query->whereNotIn($this->model->getTable() . '.id', $scopes['exceptIds']);
            unset($scopes['exceptIds']);
        }

        foreach ($scopes as $column => $value) {

            if (method_exists($this->model, 'scope' . ucfirst($column))) {
                $query->$column();
            } else {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } elseif ($column[0] == '%') {
                    $value && ($value[0] == '!') ? $query->where(mb_substr($column, 1), "not $likeOperator", '%' . mb_substr($value, 1) . '%') : $query->where(mb_substr($column, 1), $likeOperator, '%' . $value . '%');
                } elseif (isset($value[0]) && $value[0] == '!') {
                    $query->where($column, '<>', mb_substr($value, 1));
                } elseif ($value !== '') {
                    $query->where($column, $value);
                }
            }
        }

        return $query;
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function order($query, array $orders = [])
    {
        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $this->$method($query, $orders);
        }

        foreach ($orders as $column => $direction) {
            $query->orderBy($column, $direction);
        }

        return $query;
    }
}
