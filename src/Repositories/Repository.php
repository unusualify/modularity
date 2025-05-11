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
use Unusualify\Modularity\Repositories\Contracts\Repository as RepositoryContract;
use Unusualify\Modularity\Traits\ManageNames;

abstract class Repository implements RepositoryContract
{
    use ManageNames,
        Logic\InspectTraits,
        Logic\RelationshipHelpers,
        Logic\MethodTransformers,
        Logic\QueryBuilder,
        Logic\CountBuilders,
        Logic\Dates,
        Logic\Relationships,
        Logic\DispatchEvents;

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
     * @return string
     */
    public function getLikeOperator()
    {
        if (DB::connection()->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            return 'ILIKE';
        }

        return 'LIKE';
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
}
