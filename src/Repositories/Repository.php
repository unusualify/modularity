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
use PDO;
use ReflectionClass;
use Spatie\Activitylog\Facades\LogBatch;
use Unusualify\Modularity\Entities\Behaviors\Sortable;
use Unusualify\Modularity\Repositories\Traits\DatesTrait;
use Unusualify\Modularity\Repositories\Traits\DispatchEvents;
use Unusualify\Modularity\Repositories\Traits\MethodTransformers;
use Unusualify\Modularity\Repositories\Traits\RelationTrait;
use Unusualify\Modularity\Traits\ManageNames;

abstract class Repository
{
    use DatesTrait, ManageNames, MethodTransformers, RelationTrait, DispatchEvents;

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

        if (isset($scopes['searches']) && isset($scopes['search']) && is_array($scopes['searches'])) {
            $translatedAttributes = $this->model->translatedAttributes ?? [];

            $searches = Arr::where($scopes['searches'], function (string|int $value, int $key) use ($translatedAttributes) {
                return ! in_array($value, $translatedAttributes);
            });

            $this->searchIn($query, $scopes, 'search', $searches);

            $scope['searches'] = Arr::where($scopes['searches'], function (string|int $value, int $key) use ($translatedAttributes) {
                return in_array($value, $translatedAttributes);
            });
            // unset($scopes['searches']);
        }

        $query = $this->filter($query, $scopes);
        // $query = $this->filterBack($query, $scopes);

        $query = $this->order($query, $orders);

        if (! $forcePagination && $this->model instanceof Sortable) {
            return $query->ordered()->get();
        }

        if ($perPage == -1) {
            return $query->paginate(0);

            return $query->get();
        }

        try {

            return $query->paginate($perPage);

        } catch (\Throwable $th) {
            dd(
                $query->toSql(),
                $th,
                debug_backtrace()
            );
        }

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
     * @param array $with
     * @param array $withCount
     * @return \Unusualify\Modularity\Models\Model
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getById($id, $with = [], $withCount = [])
    {
        $query = $this->model->query();

        if (classHasTrait($this->model, 'Illuminate\Database\Eloquent\SoftDeletes')) {
            return $query->withTrashed()->with($this->formatWiths($query, $with))->withCount($withCount)->findOrFail($id);
        } else {
            return $query->with($this->formatWiths($query, $with))->withCount($withCount)->findOrFail($id);
        }
    }

    /**
     * @param string $column
     * @param array $orders
     * @param null $exceptId
     * @return \Illuminate\Support\Collection
     */
    public function listAll($with = [], $scopes = [], $orders = [], $exceptId = null)
    {
        $query = $this->model->query();

        $query = $this->model->with($this->formatWiths($query, $with));

        if (isset($scopes['searches']) && isset($scopes['search']) && is_array($scopes['searches'])) {

            $this->searchIn($query, $scopes, 'search', $scopes['searches']);
            unset($scopes['searches']);
        }

        $query = $this->filter($query, $scopes);
        // $query = $this->filterBack($query, $scopes);

        $query = $this->order($query, $orders);

        try {
            return $query->get();
        } catch (\Throwable $th) {
            // throw $th;
            dd(
                $th,
                debug_backtrace()
            );
        }

    }

    /**
     * @param string $column
     * @param array $orders
     * @param null $exceptId
     * @return \Illuminate\Support\Collection
     */
    public function list($column = 'name', $with = [], $scopes = [], $orders = [], $appends = [], $exceptId = null)
    {
        $query = $this->model->newQuery();

        if (count($with) > 0) {
            $query = $query->with($this->formatWiths($query, $with));
        }

        if ($exceptId) {
            $query = $query->where($this->model->getTable() . '.id', '<>', $exceptId);
        }

        $query = $this->filter($query, $scopes);

        if ($this->model instanceof Sortable) {
            $query = $query->ordered();
        } elseif (! empty($orders)) {
            $query = $this->order($query, $orders);
        }

        $defaultColumns = is_array($column) ? $column : [$column];

        $columns = ['id', ...$defaultColumns];
        $oldColumns = $columns;

        $tableColumns = $this->getModel()->getColumns();
        $translatedColumns = [];

        if (method_exists($this->getModel(), 'isTranslatable') && $this->model->isTranslatable()) {
            $query = $query->withTranslation();
            $translatedAttributes = $this->getTranslatedAttributes();

            $columns = array_diff($columns, $translatedAttributes);
            $defaultColumns = array_diff($defaultColumns, $translatedAttributes);
            $translatedColumns = array_values(array_intersect($oldColumns, $translatedAttributes));
            $absentColumns = array_diff($defaultColumns, $tableColumns);

            if (in_array('name', $absentColumns)) {
                $titleColumnKey = $this->getModel()->getRouteTitleColumnKey();
                if (in_array($titleColumnKey, $translatedAttributes)) {
                    $columns = array_filter($columns, fn ($col) => $col !== 'name');
                    $translatedColumns[] = $titleColumnKey;
                } else {
                    $columns = array_filter($columns, fn ($col) => $col !== 'name');
                    $columns[] = "{$this->getModel()->getRouteTitleColumnKey()} as name";
                }
            }

        }

        $relationships = collect($with)->map(function ($r) {
            $r = explode('.', $r)[0];

            return $r;
        })->toArray();

        $foreignableRelationships = collect($relationships)->filter(function ($r) {
            return in_array($this->getModel()->getRelationType($r), ['BelongsTo', 'MorphTo']);
        })->values()->toArray();

        foreach ($foreignableRelationships as $r) {
            $columns[] = $this->getModel()->{$r}()->getForeignKeyName();
        }

        $with = array_merge($this->getModel()->getWith(), $with);

        // dd($columns, $appends, $with, $columns, $translatedColumns);

        try {
            // code...
            return $query->get($columns)->map(fn ($item) => [
                ...collect($appends)->mapWithKeys(function ($append) use ($item) {
                    return [$append => $item->{$append}];
                })->toArray(),
                ...collect($with)->mapWithKeys(function ($r) use ($item) {
                    $r = explode('.', $r)[0];

                    return [$r => $item->{$r}];
                })->toArray(),
                ...(collect($columns)->mapWithKeys(fn ($column) => [$column => $item->{$column}])->toArray()),
                ...(collect($translatedColumns)->mapWithKeys(fn ($column) => [$column => $item->{$column}])->toArray()),
            ]);
        } catch (\Throwable $th) {
            dd(
                $this->getModel()->getRouteTitleColumnKey(),
                static::class,
                $columns,
                $appends,
                $with,
                $translatedColumns,
                $foreignableRelationships,
                $relationships,
                $foreignableRelationships,
                $th,
                array_reduce(debug_backtrace(), 'backtrace_formatter', [])
            );
        }

        // try {
        //     return $query->get($columns);
        // } catch (\Throwable $th) {
        //     if (method_exists($this->model, 'getColumns')) {
        //         $appends = $this->model->getAppends();
        //         $differentElements = array_diff($columns, $this->model->getColumns());
        //         // if absent columns exist in appends, we can return the result with the absent columns
        //         if (empty(array_diff($differentElements, $appends))) {
        //             // All differentElements exist in appends
        //             // You can proceed with your logic here if needed
        //             return $query->get()->map(fn ($item) => collect($columns)->map(fn ($c) => $item->{$c})->toArray());
        //         }
        //     }
        //     // no absent columns exist in appends, we can't return the result with the absent columns
        //     throw $th;
        // }

    }

    /**
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
    public function create($fields, $schema = null)
    {
        $this->traitColumns = $this->setColumns($this->traitColumns, $schema ?? $this->chunkInputs(all: true));

        return DB::transaction(function () use ($fields) {
            LogBatch::startBatch();

            $original_fields = $fields;

            $fields = $this->prepareFieldsBeforeCreate($fields);

            $object = $this->model->create(Arr::except($fields, $this->getReservedFields()));

            $this->beforeSave($object, $original_fields);

            $fields = $this->prepareFieldsBeforeSave($object, $fields);

            $object->save();

            $this->afterSave($object, $fields);

            LogBatch::endBatch();

            $this->dispatchEvent($object, 'create');

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

        if (! $object) {
            return $this->create($fields);
        }

        $this->update($object->id, $fields);
    }

    /**
     * @param mixed $id
     * @param array $fields
     * @return void
     */
    public function update($id, $fields, $schema = null)
    {
        $this->traitColumns = $this->setColumns($this->traitColumns, $schema ?? $this->chunkInputs(all: true));

        DB::transaction(function () use ($id, $fields) {
            LogBatch::startBatch();

            if (classHasTrait($this->model, 'Unusualify\Modularity\Entities\Traits\IsSingular')) {
                $object = $this->model->single();
            } else {
                $object = $this->model->findOrFail($id);
            }

            $this->beforeSave($object, $fields);

            $fields = $this->prepareFieldsBeforeSave($object, $fields);

            $object->fill(Arr::except($fields, $this->getReservedFields()));

            $object->save();

            $this->afterSave($object, $fields);

            LogBatch::endBatch();

            $this->dispatchEvent($object, 'update');
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
    public function duplicate($id, $titleColumnKey, $schema)
    {
        if (($duplicated = $this->model->find($id)) === null) {
            return false;
        }

        $this->traitColumns = $this->setColumns($this->traitColumns, $this->chunkInputs(all: true));

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

            if (! method_exists($object, 'canDeleteSafely') || $object->canDeleteSafely()) {
                $this->dispatchEvent($object, 'delete');

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
                LogBatch::startBatch();

                $this->dispatchEvent($object, 'forceDelete');

                $object->forceDelete();

                $this->afterForceDelete($object);

                LogBatch::endBatch();

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
                LogBatch::startBatch();

                $object->restore();

                $this->afterRestore($object);

                $this->dispatchEvent($object, 'restore');

                LogBatch::endBatch();

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
                if (! in_array($relationshipObject->$attribute, $fields[$formField])) {
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
            // $value
            // '1' or '1,7' or [1,7,9,11]
            $value = $scopes[$scopeField];
            if (is_string($value)) {
                $value = explode(',', $value);
            }

            $query->whereHas($scopeRelation, function ($query) use ($value, $scopeField) {
                $query->whereIn($scopeField, $value);
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
        if (! $modelOrRepository) {
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

        $class = Config::get('twill.namespace') . '\\Repositories\\' . ucfirst($modelOrRepository) . 'Repository';

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
     * @return bool
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
     * @return bool
     */
    public function isTranslatable($column)
    {
        return method_exists($this->model, 'isTranslatable') && $this->model->isTranslatable($column);
    }

    /**
     * @return bool
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
        return array_map(function ($item) {
            return is_array($item)
                ? function ($query) use ($item) {

                    if (is_array($item[0])) {
                        foreach ($item as $key => $args) {
                            $query->{array_shift($args)}(...$args);
                        }
                    } else {
                        $query->{array_shift($item)}(...$item);
                    }
                    $query->without('pivot');
                }
            : $item;
        }, $with);
    }

    public function definedRelations($relations = null): array
    {
        if (method_exists($this->model, 'definedRelations')) {
            return $this->model->definedRelations($relations);
        }

        $relationNamespace = "Illuminate\Database\Eloquent\Relations";

        $relationClassesPattern = '|' . preg_quote($relationNamespace, '|') . '|';

        if ($relations) {
            if (is_array($relations)) {
                $relationNamespaces = implode('|', Arr::map($relations, function ($relationName) use ($relationNamespace) {
                    return $relationNamespace . '\\' . $relationName;
                }));
                $relationClassesPattern = '|' . preg_quote($relationNamespaces, '|') . '|';

            } elseif (is_string($relations)) {
                $relationClassesPattern = '|' . preg_quote($relationNamespace . '\\' . $relations, '|') . '|';
            }
        }

        $reflector = new \ReflectionClass($this->getModel());

        return collect($reflector->getMethods(\ReflectionMethod::IS_PUBLIC))->reduce(function ($carry, $method) use ($relationClassesPattern) {

            if ($method->getNumberOfParameters() < 1) {
                if ($method->hasReturnType()) {
                    if (preg_match($relationClassesPattern, ($returnType = $method->getReturnType()))) {
                        $carry[] = $method->name;
                    }
                } else {

                }
            }

            return $carry;
        }, []);

        return collect($reflector->getMethods(\ReflectionMethod::IS_PUBLIC))
            ->filter(fn (\ReflectionMethod $method) => $method->hasReturnType() && preg_match("{$relationClassesPattern}", $method->getReturnType()))
            ->pluck('name')
            ->all();
    }

    private function getForeignKeyBelongsToMany($related)
    {
        if (method_exists($related, 'getRelatedPivotKeyName')) {
            $foreignKey = $related->getRelatedPivotKeyName();
        }

        return $foreignKey;
    }

    private function getForeignKeyBelongsTo($related)
    {
        $foreignKey = $related->getForeignKeyName();

        return $foreignKey;
    }

    private function getForeignKeyHasManyThrough($related)
    {
        $foreignKey = $related->getSecondLocalKeyName();

        return $foreignKey;
    }

    public function getCountFor($method)
    {
        // dd($method);
        $methodName = 'scope' . ucfirst($method[0]);

        return $this->model->$methodName();
    }

    /**
     * @param string $class name resolution
     * @return bool
     */
    public function hasModelTrait($trait)
    {
        $hasTrait = classHasTrait($this->getModel(), $trait);

        return $hasTrait;
    }

    public function getByColumnValues($column, array $values, $with = [], $scopes = [], $orders = [], $isFormatted = false, $schema = null)
    {
        $query = $this->model->whereIn($column, $values);

        $query = $query->with($this->formatWiths($query, $with));

        $query = $this->filter($query, $scopes);

        $query = $this->order($query, $orders);

        if ($isFormatted) {
            return $query->get()->map(function ($item) {
                // dd($item);
                return array_merge(
                    $this->getShowFields($item, $this->chunkInputs($this->inputs())),
                    $item->attributesToArray(),
                    // $item->toArray(),
                    // $this->getFormFields($item, $this->chunkInputs($this->inputs())),
                    // $columnsData
                );
            });
        } else {

            return $query->get();
        }
    }

    public function getByIds(array $ids, $with = [], $scopes = [], $orders = [], $isFormatted = false, $schema = null)
    {
        $query = $this->model->whereIn('id', $ids);

        $query = $query->with($this->formatWiths($query, $with));

        $query = $this->filter($query, $scopes);

        $query = $this->order($query, $orders);

        if ($isFormatted) {
            return $query->get()->map(function ($item) {
                return array_merge(
                    $this->getShowFields($item, $this->chunkInputs($this->inputs())),
                    $item->attributesToArray(),
                );
            });
        } else {

            return $query->get();
        }
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
}
