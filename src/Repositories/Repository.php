<?php

namespace Unusualify\Modularity\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Unusualify\Modularity\Entities\Behaviors\Sortable;
use PDO;
use ReflectionClass;

use Unusualify\Modularity\Repositories\Traits\{DatesTrait, RelationTrait};
use Unusualify\Modularity\Traits\{ManageTraits, ManageNames};

abstract class Repository
{
    use ManageTraits, ManageNames, DatesTrait, RelationTrait;

    /**
     * @var \Unusualify\Modularity\Models\Model
     */
    protected $model;

    /**
     * @var string[]
     */
    protected $ignoreFieldsBeforeSave = [];

    /**
     * @var array
     */
    protected $countScope = [];

    /**
     * @var array
     */
    protected $fieldsGroups = [];

    /**
     * @var array
     */
    protected $traitColumns = [];


    /**
     * @var bool
     */
    // public $fieldsGroupsFormFieldNamesAutoPrefix = false;

    /**
     * @var string|null
     */
    // public $fieldsGroupsFormFieldNameSeparator = '_';

    /**
     * @param array $with
     * @param array $scopes
     * @param array $orders
     * @param int $perPage
     * @param bool $forcePagination
     * @return \Illuminate\Support\Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function get($with = [], $scopes = [], $orders = [], $perPage = 20, $forcePagination = false)
    {
        $query = $this->model->query();

        $query = $this->model->with($this->formatWiths($query, $with));

        if( isset($scopes['searches']) && isset($scopes['search']) && is_array($scopes['searches']) ){

            $this->searchIn($query, $scopes, 'search', $scopes['searches']);
            unset($scopes['searches']);
        }
        // dd(
        //     $scopes,
        //     $query->toSql(),
        //     $query1 = $this->filter($query, $scopes),
        //     $query1->toSql(),
        //     $query2 = $this->filterBack($query, $scopes),
        //     $query2->toSql(),

        // );
        $query = $this->filter($query, $scopes);
        // $query = $this->filterBack($query, $scopes);
        $query = $this->order($query, $orders);

        if (!$forcePagination && $this->model instanceof Sortable) {
            return $query->ordered()->get();
        }

        if ($perPage == -1) {
            return $query->paginate(0);
            return $query->get();
        }

        try {
            //code...
            // dd(
            //     $query->toSql(),

            // );
            return $query->paginate($perPage);

        } catch (\Throwable $th) {
            //throw $th;
            dd(
                $th,
                debug_backtrace()
                // $th,
                // $with,
                // $scopes,
                // $orders,
                // $perPage
            );
        }

    }

    /**
     * @param string $slug
     * @param array $scope
     * @return int
     */
    public function getCountByStatusSlug($slug, $scope = [])
    {
        $this->countScope = $scope;

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

        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            if (($count = $this->$method($slug)) !== false) {
                return $count;
            }
        }

        return 0;
    }

    /**
     * @return int
     */
    public function getCountForAll()
    {
        $query = $this->model->newQuery();
        return $this->filter($query, $this->countScope)->count();
    }

    /**
     * @return int
     */
    public function getCountForPublished()
    {
        $query = $this->model->newQuery();
        return $this->filter($query, $this->countScope)->published()->count();
    }

    /**
     * @return int
     */
    public function getCountForDraft()
    {
        $query = $this->model->newQuery();
        return $this->filter($query, $this->countScope)->draft()->count();
    }

    /**
     * @return int
     */
    public function getCountForTrash()
    {
        $query = $this->model->newQuery();
        return $this->filter($query, $this->countScope)->onlyTrashed()->count();
    }

    /**
     * @param $id
     * @param array $with
     * @param array $withCount
     * @return \Unusualify\Modularity\Models\Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getById($id, $with = [], $withCount = [])
    {
        $query = $this->model->query();

        return $query->with($this->formatWiths($query, $with))->withCount($withCount)->findOrFail($id);
    }

    /**
     * @param string $column
     * @param array $orders
     * @param null $exceptId
     * @return \Illuminate\Support\Collection
     */
    public function listAll($column = 'name', $orders = [], $exceptId = null)
    {
        $query = $this->model->newQuery();

        if ($exceptId) {
            $query = $query->where($this->model->getTable() . '.id', '<>', $exceptId);
        }

        if ($this->model instanceof Sortable) {
            $query = $query->ordered();
        } elseif (!empty($orders)) {
            $query = $this->order($query, $orders);
        }

        if ($this->model->isTranslatable()) {
            $query = $query->withTranslation();
        }

        return $query->get()->pluck($column, 'id');
    }

    /**
     * @param string $column
     * @param array $orders
     * @param null $exceptId
     * @return \Illuminate\Support\Collection
     */
    public function list($column = 'name', $with = [], $orders = [], $exceptId = null)
    {
        $query = $this->model->newQuery();

        if(count($with) > 0)
            $query = $query->with( $this->formatWiths($query, $with) );


        if ($exceptId) {
            $query = $query->where($this->model->getTable() . '.id', '<>', $exceptId);
        }

        if ($this->model instanceof Sortable) {
            $query = $query->ordered();
        } elseif (!empty($orders)) {
            $query = $this->order($query, $orders);
        }

        if ( method_exists($this->getModel(), 'isTranslatable') && $this->model->isTranslatable()) {
            $query = $query->withTranslation();

            return $query->get()->map(fn($item) => [
                'id' => $item->id,
                $column => $item->{$column}
            ]);
        }

        return $query->get(['id', $column]);
    }

    /**
     * @param $search
     * @param array $fields
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function cmsSearch($search, $fields = [])
    {
        $query = $this->model->latest();

        $translatedAttributes = $this->model->translatedAttributes ?? [];

        foreach ($fields as $field) {
            if (in_array($field, $translatedAttributes)) {
                $query->orWhereHas('translations', function ($q) use ($field, $search) {
                    $q->where($field, $this->getLikeOperator(), "%{$search}%");
                });
            } else {
                $query->orWhere($field, $this->getLikeOperator(), "%{$search}%");
            }
        }

        return $query->get();
    }

    /**
     * @param $attributes
     * @param $fields
     * @return \Unusualify\Modularity\Models\Model
     */
    public function firstOrCreate($attributes, $fields = [])
    {
        return $this->model->where($attributes)->first() ?? $this->create($attributes + $fields);
    }

    /**
     * @param string[] $fields
     * @return \Unusualify\Modularity\Models\Model
     */
    public function create($fields)
    {
        $this->traitColumns = $this->setColumns($this->traitColumns, $this->chunkInputs(all:true));

        return DB::transaction(function () use ($fields) {
            $original_fields = $fields;

            $fields = $this->prepareFieldsBeforeCreate($fields);

            $object = $this->model->create(Arr::except($fields, $this->getReservedFields()));

            $this->beforeSave($object, $original_fields);

            $fields = $this->prepareFieldsBeforeSave($object, $fields);

            $object->save();

            $this->afterSave($object, $fields);

            return $object;
        }, 3);
    }

    /**
     * @param array $fields
     * @return \Unusualify\Modularity\Models\Model
     */
    public function createForPreview($fields)
    {
        $fields = $this->prepareFieldsBeforeCreate($fields);

        $object = $this->model->newInstance(Arr::except($fields, $this->getReservedFields()));

        return $this->hydrate($object, $fields);
    }

    /**
     * @param array $attributes
     * @param array $fields
     * @return \Unusualify\Modularity\Models\Model|void
     */
    public function updateOrCreate($attributes, $fields)
    {
        $object = $this->model->where($attributes)->first();

        if (!$object) {
            return $this->create($fields);
        }

        $this->update($object->id, $fields);
    }

    /**
     * @param mixed $id
     * @param array $fields
     * @return void
     */
    public function update($id, $fields)
    {
        $this->traitColumns = $this->setColumns($this->traitColumns, $this->chunkInputs(all:true));

        DB::transaction(function () use ($id, $fields) {
            $object = $this->model->findOrFail($id);

            $this->beforeSave($object, $fields);

            $fields = $this->prepareFieldsBeforeSave($object, $fields);

            $object->fill(Arr::except($fields, $this->getReservedFields()));

            $object->save();

            $this->afterSave($object, $fields);
        }, 3);
    }

    /**
     * @param mixed $id
     * @param array $values
     * @param array $scopes
     * @return mixed
     */
    public function updateBasic($id, $values, $scopes = [])
    {
        return DB::transaction(function () use ($id, $values, $scopes) {
            // apply scopes if no id provided
            if (is_null($id)) {
                $query = $this->model->query();

                foreach ($scopes as $column => $value) {
                    $query->where($column, $value);
                }

                $query->update($values);

                $query->get()->each(function ($object) use ($values) {
                    $this->afterUpdateBasic($object, $values);
                });

                return true;
            }

            // apply to all ids if array of ids provided
            if (is_array($id)) {
                $query = $this->model->whereIn('id', $id);
                $query->update($values);

                $query->get()->each(function ($object) use ($values) {
                    $this->afterUpdateBasic($object, $values);
                });

                return true;
            }

            if (($object = $this->model->find($id)) != null) {
                $object->update($values);
                $this->afterUpdateBasic($object, $values);
                return true;
            }

            return false;
        }, 3);
    }

    /**
     * @param array $ids
     * @return void
     */
    public function setNewOrder($ids)
    {
        DB::transaction(function () use ($ids) {
            $this->model->setNewOrder($ids);
        }, 3);
    }

    /**
     * @param mixed $id
     * @return mixed
     */
    public function duplicate($id, $titleColumnKey = 'title', $schema)
    {
        if (($duplicated = $this->model->find($id)) === null) {
            return false;
        }

        $this->traitColumns = $this->setColumns($this->traitColumns, $this->chunkInputs(all:true));


        return DB::transaction(function () use ($duplicated, $schema) {

            $fields = $this->getFormFields($duplicated, $schema);

            $original_fields = $fields;

            $fields = $this->prepareFieldsBeforeCreate($fields);

            $object = $this->model->create(Arr::except($fields, $this->getReservedFields()));

            $this->beforeSave($object, $original_fields);

            $fields = $this->prepareFieldsBeforeSave($object, $fields);

            $object->save();

            $this->afterSave($object, $fields);

            return $object;
        }, 3);


        // if (($revision = $object->revisions()->orderBy('created_at', 'desc')->first()) === null) {
        //     return false;
        // }

        // $revisionInput = json_decode($revision->payload, true);
        // $baseInput = collect($revisionInput)->only([
        //     $titleColumnKey,
        //     'slug',
        //     'languages',
        // ])->filter()->toArray();

        // $newObject = $this->create($object);

        // $this->update($newObject->id, $revisionInput);

        // return $newObject;
    }

    /**
     * @param mixed $id
     * @return mixed
     */
    public function delete($id)
    {
        return DB::transaction(function () use ($id) {
            if (($object = $this->model->find($id)) === null) {
                return false;
            }

            if (!method_exists($object, 'canDeleteSafely') || $object->canDeleteSafely()) {
                $object->delete();
                $this->afterDelete($object);
                return true;
            }
            return false;
        }, 3);
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function bulkDelete($ids)
    {
        return DB::transaction(function () use ($ids) {
            try {
                Collection::make($ids)->each(function ($id) {
                    $this->delete($id);
                });
            } catch (Exception $e) {
                Log::error($e);
                if (config('app.debug')) {
                    throw $e;
                }
                return false;
            }

            return true;
        }, 3);
    }

    /**
     * @param mixed $id
     * @return mixed
     */
    public function forceDelete($id)
    {
        return DB::transaction(function () use ($id) {
            if (($object = $this->model->onlyTrashed()->find($id)) === null) {
                return false;
            } else {
                $object->forceDelete();
                $this->afterForceDelete($object);
                return true;
            }
        }, 3);
    }

    /**
     * @param mixed $id
     * @return mixed
     */
    public function bulkForceDelete($ids)
    {
        return DB::transaction(function () use ($ids) {
            try {
                $query = $this->model->onlyTrashed()->whereIn('id', $ids);
                $objects = $query->get();

                $query->forceDelete();

                $objects->each(function ($object) {
                    $this->afterForceDelete($object);
                });
            } catch (Exception $e) {
                Log::error($e);
                return false;
            }

            return true;
        }, 3);
    }

    /**
     * @param mixed $id
     * @return mixed
     */
    public function restore($id)
    {
        return DB::transaction(function () use ($id) {
            if (($object = $this->model->withTrashed()->find($id)) != null) {
                $object->restore();
                $this->afterRestore($object);
                return true;
            }

            return false;
        }, 3);
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function bulkRestore($ids)
    {
        return DB::transaction(function () use ($ids) {
            try {
                $query = $this->model->withTrashed()->whereIn('id', $ids);
                $objects = $query->get();

                $query->restore();

                $objects->each(function ($object) {
                    $this->afterRestore($object);
                });
            } catch (Exception $e) {
                Log::error($e);
                return false;
            }

            return true;
        }, 3);
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
                if (!$this->shouldIgnoreFieldBeforeSave($field)) {
                    if (!isset($fields[$field])) {
                        $fields[$field] = false;
                    } else {
                        $fields[$field] = !empty($fields[$field]);
                    }
                }
            }
        }

        if (property_exists($this->model, 'nullable')) {
            foreach ($this->model->nullable as $field) {
                if (!isset($fields[$field]) && !$this->shouldIgnoreFieldBeforeSave($field)) {
                    $fields[$field] = null;
                }
            }
        }

        foreach ($fields as $key => $value) {
            if (!$this->shouldIgnoreFieldBeforeSave($key)) {
                if (is_array($value) && empty($value)) {
                    $fields[$key] = null;
                }
                if ($value === '') {
                    $fields[$key] = null;
                }
            }
        }

        return $fields;
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
    public function getFormFields($object, $schema = [])
    {
        $this->traitColumns = $this->setColumns($this->traitColumns, $this->chunkInputs(all:true));

        // dd($this->traitColumns);

        $fields = $object->attributesToArray();

        // $fields = $this->castFormFields($fields);

        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $fields = $this->$method($object, $fields, $schema);
        }

        return $fields;
    }

    /**
     * @param array $columns
     * @param array $inputs
     *
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
     * @param string $trait
     *
     * @return array
     */
    public function getColumns(string $trait = null)
    {
        preg_match('/\/([A-Za-z]*)\.php/', debug_backtrace()[0]['file'], $matches);

        $traitName = $trait ? get_class_short_name($trait) : $matches[1];

        return $this->traitColumns[$traitName] ?? [];
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @return array
     */
    public function castFormFields($fields)
    {
        foreach ($this->chunkInputs($this->inputs()) as $input) {
            if(isset($input['ext'])){
                switch ($input['ext']) {
                    case 'date':
                        # code...
                        $fields[$input['name']] = '';
                        break;

                    default:
                        # code...
                        break;
                }
            }
        }


        return $fields;
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $scopes
     * @return \Illuminate\Database\Query\Builder
     */
    public function filter($query, array $scopes = [])
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

        $_scopes = $scopes;
        foreach ($_scopes as $column => $value) {
            $studlyColumn = studlyName($column);
            if (preg_match('/addRelation([A-Za-z]+)/', $column, $matches)) {
                $relationName = $this->getCamelCase($matches[1]);

                if(method_exists($this->getModel(), $relationName)){
                    $foreignKey = $this->getForeignKeyFromName($relationName);
                    // $tableName = $this->getTableNameFromName($relationName);

                    $scopes[$foreignKey] = $value;
                    $this->addRelationFilterScope($query, $scopes, $foreignKey, $relationName);
                }

                unset($scopes[$column]);
            }
        }
        foreach ($scopes as $column => $value) {
            $studlyColumn = studlyName($column);

            if (method_exists($this->model, 'scope' . $studlyColumn)) {
                $query->{$this->getCamelCase($column)}();
            } else {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } elseif ($column[0] == '%') {
                    $value && ($value[0] == '!') ? $query->where(substr($column, 1), "not $likeOperator", '%' . substr($value, 1) . '%') : $query->where(substr($column, 1), $likeOperator, '%' . $value . '%');
                } elseif (isset($value[0]) && $value[0] == '!') {
                    $query->where($column, '<>', substr($value, 1));
                } elseif ($value !== '') {
                    $query->where($column, $value);
                }
            }
        }

        return $query;
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $scopes
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
            // dd(
            //     $column,
            //     ucfirst($column),
            //     method_exists($this->model, 'scope' . ucfirst($column))
            // );
            if (method_exists($this->model, 'scope' . ucfirst($column))) {
                $query->$column();
            } else {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } elseif ($column[0] == '%') {
                    $value && ($value[0] == '!') ? $query->where(substr($column, 1), "not $likeOperator", '%' . substr($value, 1) . '%') : $query->where(substr($column, 1), $likeOperator, '%' . $value . '%');
                } elseif (isset($value[0]) && $value[0] == '!') {
                    $query->where($column, '<>', substr($value, 1));
                } elseif ($value !== '') {
                    $query->where($column, $value);
                }
            }
        }

        return $query;
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $orders
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

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @param string $relationship
     * @param string $formField
     * @param string $attribute
     * @return void
     */
    public function updateOneToMany($object, $fields, $relationship, $formField, $attribute)
    {
        if (isset($fields[$formField])) {
            foreach ($fields[$formField] as $id) {
                $object->$relationship()->updateOrCreate([$attribute => $id]);
            }

            foreach ($object->$relationship as $relationshipObject) {
                if (!in_array($relationshipObject->$attribute, $fields[$formField])) {
                    $relationshipObject->delete();
                }
            }
        } else {
            $object->$relationship()->delete();
        }
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @param string $relationship
     * @return void
     */
    public function updateMultiSelect($object, $fields, $relationship)
    {
        $object->$relationship()->sync($fields[$relationship] ?? []);
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $scopes
     * @param string $scopeField
     * @param string $scopeRelation
     * @return void
     */
    public function addRelationFilterScope($query, &$scopes, $scopeField, $scopeRelation)
    {
        if (isset($scopes[$scopeField])) {
            $id = $scopes[$scopeField];
            $query->whereHas($scopeRelation, function ($query) use ($id, $scopeField) {
                $query->where($scopeField, $id);
            });
            unset($scopes[$scopeField]);
        }
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $scopes
     * @param string $scopeField
     * @return void
     */
    public function addLikeFilterScope($query, &$scopes, $scopeField)
    {
        if (isset($scopes[$scopeField]) && is_string($scopes[$scopeField])) {
            $query->where($scopeField, $this->getLikeOperator(), '%' . $scopes[$scopeField] . '%');
            unset($scopes[$scopeField]);
        }
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $scopes
     * @param string $scopeField
     * @param string[] $orFields
     */
    public function searchIn($query, &$scopes, $scopeField, $orFields = [])
    {
        if (isset($scopes[$scopeField]) && is_string($scopes[$scopeField])) {
            $query->where(function ($query) use (&$scopes, $scopeField, $orFields) {
                foreach ($orFields as $field) {
                    $query->orWhere($field, $this->getLikeOperator(), '%' . $scopes[$scopeField] . '%');
                    unset($scopes[$field]);
                }
            });
        }
    }

    /**
     * @return bool
     */
    public function isUniqueFeature()
    {
        return false;
    }

    /**
     * @param array $ignore
     * @return void
     */
    public function addIgnoreFieldsBeforeSave($ignore = [])
    {
        $this->ignoreFieldsBeforeSave = is_array($ignore)
        ? array_merge($this->ignoreFieldsBeforeSave, $ignore)
        : array_merge($this->ignoreFieldsBeforeSave, [$ignore]);
    }

    /**
     * @param string $ignore
     * @return bool
     */
    public function shouldIgnoreFieldBeforeSave($ignore)
    {
        return in_array($ignore, $this->ignoreFieldsBeforeSave);
    }

    /**
     * @return string[]
     */
    public function getReservedFields()
    {
        return [
            'medias',
            'browsers',
            'repeaters',
            'blocks',
        ];
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param string $relation
     * @param \Unusualify\Modularity\Models\Model|\Unusualify\Modularity\Repositories\ModuleRepository|null $modelOrRepository
     * @return mixed
     */
    protected function getModelRepository($relation, $modelOrRepository = null)
    {
        if (!$modelOrRepository) {
            if (class_exists($relation) && (new $relation) instanceof Model) {
                $modelOrRepository = str_after_last($relation, '\\');
            } else {
                $morphedModel = Relation::getMorphedModel($relation);
                if (class_exists($morphedModel) && (new $morphedModel) instanceof Model) {
                    $modelOrRepository = (new ReflectionClass($morphedModel))->getShortName();
                } else {
                    $modelOrRepository = ucfirst(Str::singular($relation));
                }
            }
        }

        $repository = class_exists($modelOrRepository)
        ? App::make($modelOrRepository)
        : $modelOrRepository;

        if ($repository instanceof ModuleRepository) {
            return $repository;
        }

        $class = Config::get('twill.namespace') . "\\Repositories\\" . ucfirst($modelOrRepository) . "Repository";

        if (class_exists($class)) {
            return App::make($class);
        }

        $capsule = TwillCapsules::getCapsuleForModel($modelOrRepository);

        if (blank($capsule)) {
            throw new Exception("Repository class not found for model '{$modelOrRepository}'");
        }

        return App::make($capsule->getRepositoryClass());
    }

    /**
     * @return string
     */
    protected function getLikeOperator()
    {
        if (DB::connection()->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            return 'ILIKE';
        }

        return 'LIKE';
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->model->$method(...$parameters);
    }

    /**
     * @param string $behavior
     * @return boolean
     */
    public function hasBehavior($behavior)
    {
        $hasBehavior = classHasTrait($this, 'Unusualify\Modularity\Repositories\Traits\\' . ucfirst($behavior) . 'Trait');
        // dd($behavior, $hasBehavior, Str::startsWith($behavior, 'translation'));
        if (Str::startsWith($behavior, 'translation')) {
            $hasBehavior = $hasBehavior && $this->model->isTranslatable();
        }

        return $hasBehavior;
    }

    /**
     * @return boolean
     */
    public function isTranslatable($column)
    {
        return method_exists($this->model, 'isTranslatable') && $this->model->isTranslatable($column);
    }
    /**
     * @return boolean
     */
    public function isSoftDeletable()
    {
        return method_exists($this->model, 'isSoftDeletable') && $this->model->isSoftDeletable();
    }

    /**
     * Post::with('user:id,username')->get();
     * Post::query()
     *   ->with(['user' => function ($query) {
     *      $query->select('id', 'username');
     *   }])
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param array $with for instance ['roles' => ['select', 'id', 'name']]
     * @return array
     */
    public function formatWiths($query, $with)
    {
        return array_map(function($item) {
            return is_array($item)
                    ? function($query) use($item){
                        // dd($item);
                        // $db->table('roles')->pluck('id');
                        // $query->addSelect('id');
                        if(is_array($item[0])){
                            foreach ($item as $key => $args) {
                                $query->{array_shift($args)}(...$args);
                            }
                        }else{
                            $query->{array_shift($item)}(...$item);
                        }
                        $query->without('pivot');
                    }
                    : $item;
        }, $with);
    }

    public function _modelRelations($relations = null): array
    {

        $relationNamespace = app('model.relation.namespace');

        $relationClassesPattern = app('model.relation.pattern');

        if($relations){
            if(is_array($relations)){
                $relationNamespaces = implode('|', Arr::map($relations, function($relationName) use($relationNamespace){
                    return $relationNamespace . "\\" . $relationName;
                }));
                $relationClassesPattern = "|" . preg_quote($relationNamespaces, "|") . "|";

            }else if(is_string($relations)){
                $relationClassesPattern = "|" . preg_quote($relationNamespace . "\\" . $relations, "|") . "|";
            }
        }

        $builtInMethods = app('model.builtin.methods');

        try {
            //code...
            $builtInMethods = app('model.builtin.methods');
        } catch (\Throwable $th) {
            dd($this, debug_backtrace());
        }

        $reflector = new \ReflectionClass($this->getModel());

        if(get_class_short_name($this->getModel()) == 'Surveyx')
            dd(
                $builtInMethods,
                collect($reflector->getMethods(\ReflectionMethod::IS_PUBLIC))->reduce(function($carry, $method) use($relationClassesPattern, $builtInMethods){

                    if(!in_array($method->name, $builtInMethods) && $method->getNumberOfParameters() < 1){
                        if($method->hasReturnType()){
                            if(preg_match($relationClassesPattern, ($returnType = $method->getReturnType()))){
                                $carry[$method->name] = get_class_short_name((string) $returnType);
                            }
                        }else {
                            try {
                                $return = $method->invoke($this->getModel());

                                if( $return instanceof Relation){
                                    $carry[$method->name] = get_class_short_name($return);
                                }
                            } catch (\Throwable $th) {
                                //throw $th;
                            }
                        }
                    }

                    return $carry;
                }, [])
            );


        return collect($reflector->getMethods(\ReflectionMethod::IS_PUBLIC))->reduce(function($carry, $method) use($relationClassesPattern, $builtInMethods){

            if(!in_array($method->name, $builtInMethods) && $method->getNumberOfParameters() < 1){
                if($method->hasReturnType()){
                    if(preg_match($relationClassesPattern, ($returnType = $method->getReturnType()))){
                        $carry[$method->name] = get_class_short_name((string) $returnType);
                    }
                }else {
                    try {
                        $return = $method->invoke($this->getModel());

                        if( $return instanceof Relation){
                            $carry[$method->name] = get_class_short_name($return);
                        }
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }
            }

            return $carry;
        }, []);

        // dd(collect($reflector->getMethods(\ReflectionMethod::IS_PUBLIC)));
        return collect($reflector->getMethods(\ReflectionMethod::IS_PUBLIC))
            ->filter(function($method) use($relationClassesPattern){
                return !empty($returnType = $method->getReturnType())
                    ? preg_match("{$relationClassesPattern}", $returnType)
                    : tryOperation(fn() => $this->{$method->name}()) instanceof Relation;
                // if(!empty($returnType = $method->getReturnType())){
                //     return preg_match("{$relationClassesPattern}", $returnType);
                // }else{
                //     return tryOperation(fn() => $this->{$method->name}()) instanceof Relation;
                // }

            })
            ->pluck('name')
            ->all();

        return collect($reflector->getMethods(\ReflectionMethod::IS_PUBLIC))
            ->filter(fn($method) => !empty( $returnType = $method->getReturnType())
                ? preg_match("{$relationClassesPattern}", $returnType)
                : tryOperation(fn() => $this->{$method->name}()) instanceof Relation
                // : ( ($return = tryOperation(fn() => $this->{$method->name}())) != false ?  $return instanceof Relation : false )
            )
            ->pluck('name')
            ->all();
    }

    public function definedRelations($relations = null): array
    {
        if(method_exists($this->model, 'definedRelations')){
            return $this->model->definedRelations($relations);
        }

        // return [];

        $relationNamespace = "Illuminate\Database\Eloquent\Relations";

        $relationClassesPattern = "|" . preg_quote($relationNamespace, "|") . "|";

        if($relations){
            if(is_array($relations)){
                $relationNamespaces = implode('|', Arr::map($relations, function($relationName) use($relationNamespace){
                    return $relationNamespace . "\\" . $relationName;
                }));
                $relationClassesPattern = "|" . preg_quote($relationNamespaces, "|") . "|";

            }else if(is_string($relations)){
                $relationClassesPattern = "|" . preg_quote($relationNamespace . "\\" . $relations, "|") . "|";
            }
        }

        $reflector = new \ReflectionClass($this->model());

        // dd(
        //     $this->model,
        //     $this->model->getRelations(),
        //     $reflector->isUserDefined(),
        //     get_class_methods($reflector)
        // );

        return collect($reflector->getMethods(\ReflectionMethod::IS_PUBLIC))->reduce(function($carry, $method) use($relationClassesPattern){

            if($method->getNumberOfParameters() < 1){
                if($method->hasReturnType()){
                    if(preg_match($relationClassesPattern, ($returnType = $method->getReturnType()))){
                        // $carry[$method->name] = get_class_short_name((string) $returnType);
                        $carry[] = $method->name;
                    }
                }else {
                    // try {
                    //     $return = $method->invoke($this->getModel());

                    //     if( $return instanceof Relation){
                    //         // dd( $return, $relationClassesPattern );
                    //         if(preg_match($relationClassesPattern, ($returnType = $method->getReturnType()))){
                    //             // $carry[$method->name] = get_class_short_name((string) $returnType);
                    //             $carry[] = $method->name;
                    //         }
                    //         // $carry[$method->name] = get_class_short_name($return);
                    //     }
                    // } catch (\Throwable $th) {
                    //     //throw $th;
                    // }
                }
            }

            return $carry;
        }, []);

        return collect($reflector->getMethods(\ReflectionMethod::IS_PUBLIC))
            ->filter(fn(\ReflectionMethod $method) =>  $method->hasReturnType() && preg_match("{$relationClassesPattern}", $method->getReturnType()) )
            ->pluck('name')
            ->all();
    }
}
