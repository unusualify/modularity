<?php

namespace Unusualify\Modularous\Repositories;

use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;
use Spatie\Activitylog\Facades\LogBatch;
use Unusualify\Modularous\Contracts\Cache\CacheableInterface;
use Unusualify\Modularous\Contracts\Cache\UserAwareCacheInterface;
use Unusualify\Modularous\Contracts\ModuleableInterface;
use Unusualify\Modularous\Models\Model;
use Unusualify\Modularous\Repositories\Contracts\Repository as RepositoryContract;
use Unusualify\Modularous\Repositories\Traits\Concerns\InteractsWithAttachmentPayloads;
use Unusualify\Modularous\Traits\ManageNames;

abstract class Repository implements CacheableInterface, ModuleableInterface, RepositoryContract, UserAwareCacheInterface
{
    use InteractsWithAttachmentPayloads,
        ManageNames,
        Logic\InspectTraits,
        Logic\RelationshipHelpers,
        Logic\MethodTransformers,
        Logic\QueryBuilder,
        Logic\CountBuilders,
        Logic\Dates,
        Logic\Relationships,
        Logic\DispatchEvents,
        Logic\Schema,
        Logic\CollationSelector,
        Logic\CacheableTrait,
        Logic\TouchableEloquentModel,
        Logic\ResourceCacheActionsTrait;

    /**
     * @var Model
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
     * Create a new model record.
     *
     * Lifecycle order:
     * 1. prepareFieldsBeforeCreate($fields)
     * 2. model->create($fields) — creates DB record
     * 3. beforeSave($object, $original_fields)
     * 4. prepareFieldsBeforeSave($object, $fields)
     * 5. $object->save()
     * 6. afterSave($object, $fields)
     * 7. dispatchEvent($object, 'create')
     *
     * @param string[] $fields
     * @return Model
     */
    public function create($fields, $schema = null, $options = [])
    {
        $this->setSchema($schema);

        $this->setColumns($schema ?? $this->chunkInputs(all: true));

        return DB::transaction(function () use ($fields, $options) {
            LogBatch::startBatch();

            $original_fields = $fields;

            $fields = $this->prepareFieldsBeforeCreate($fields);

            $model = $this->model;

            if (method_exists($model, 'preventDependentWarming')) {
                $model = $model->preventDependentWarming(isset($options['preventDependentWarming']) && $options['preventDependentWarming']);
            }

            $object = $model->create(Arr::except($fields, $this->getReservedFields()));

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
     * @return Model
     */
    public function firstOrCreate($attributes, $fields = [], $schema = null)
    {
        return $this->model->where($attributes)->first() ?? $this->create($attributes + $fields, $schema);
    }

    /**
     * @param array $fields
     * @return Model
     */
    public function createForPreview($fields)
    {
        $fields = $this->prepareFieldsBeforeCreate($fields);

        $model = $this->getModel();

        $object = $model->newInstance(Arr::except($fields, $this->getReservedFields()));

        return $this->hydrate($object, $fields);
    }

    /**
     * @param array $attributes
     * @param array $fields
     * @return Model|void
     */
    public function updateOrCreate($attributes, $fields, $schema = null)
    {
        $object = $this->model->where($attributes)->first();

        if (! $object) {
            return $this->create($fields, $schema);
        }

        $this->update($object->id, $fields, $schema);
    }

    /**
     * Update an existing model record.
     *
     * Lifecycle order:
     * 1. beforeSave($object, $fields)
     * 2. prepareFieldsBeforeSave($object, $fields)
     * 3. $object->fill($fields)
     * 4. $object->save()
     * 5. afterSave($object, $fields)
     * 6. dispatchEvent($object, 'update')
     *
     * @param mixed $id
     * @param array $fields
     * @return bool
     */
    public function update($id, $fields, $schema = null, $options = [])
    {
        $this->setSchema($schema);

        $this->setColumns($schema ?? $this->chunkInputs(all: true));

        return DB::transaction(function () use ($id, $fields, $options) {
            LogBatch::startBatch();

            if (classHasTrait($this->model, 'Unusualify\Modularous\Entities\Traits\IsSingular')) {
                $object = $this->model->single();
            } else {
                $object = $this->model->findOrFail($id);
            }

            $this->beforeSave($object, $fields);

            $fields = $this->prepareFieldsBeforeSave($object, $fields);

            $object->fill(Arr::except($fields, $this->getReservedFields()));

            if (method_exists($object, 'preventDependentWarming')) {
                $object = $object->preventDependentWarming(isset($options['preventDependentWarming']) && $options['preventDependentWarming']);
            }

            $object->save();

            $this->afterSave($object, $fields);

            LogBatch::endBatch();

            $object = $this->touchEloquentModel($object);

            $this->dispatchEvent($object, 'update');

            return $object->wasChanged();
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
        return DB::transaction(function () use ($ids) {
            return $this->model->setNewOrder($ids);
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

        $this->setSchema($schema);

        $this->setColumns($this->chunkInputs(all: true));

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
     * @param Model $object
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
     * @param Model $object
     * @param array $fields
     * @param string $relationship
     * @return void
     */
    public function updateMultiSelect($object, $fields, $relationship)
    {
        $object->$relationship()->sync($fields[$relationship] ?? []);
    }

    /**
     * @param Builder $query
     * @param array $scopes
     * @param string $scopeField
     * @param string $scopeRelation
     * @return void
     */
    public function addRelationFilterScope(&$query, &$scopes, $scopeField, $scopeRelation)
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

    public function addRelationFilterScopeByRelationName($query, &$scopes, $scopeField, $scopeRelation)
    {
        if (isset($scopes[$scopeField])) {
            // $value
            // '1' or '1,7' or [1,7,9,11]
            $value = $scopes[$scopeField];
            if (is_string($value)) {
                $value = explode(',', $value);
            }

            $relationNotation = "$scopeField";
            try {
                // code...
                $table = $query->getModel()->$scopeRelation()->getTable();
                $relationNotation = "$table.$scopeField";
            } catch (\Throwable $th) {
                try {
                    $table = $query->getModel()->$scopeRelation()->getRelated()->getTable();
                    $relationNotation = "$table.$scopeField";
                } catch (\Throwable $th) {
                    dd(
                        $th->getMessage(),
                        $query->getModel()->$scopeRelation()
                    );
                }
            }

            $query->whereHas($scopeRelation, function ($query) use ($value, $relationNotation) {
                $query->whereIn($relationNotation, $value);
            });
            unset($scopes[$scopeField]);
        }
    }

    /**
     * @param Builder $query
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
     * @param Builder $query
     * @param array $scopes
     * @param string $scopeField
     * @param string[] $orFields
     */
    public function searchIn($query, &$scopes, $scopeField, $orFields = [])
    {
        if (isset($scopes[$scopeField]) && is_string($scopes[$scopeField])) {
            $query->orWhere(function ($query) use (&$scopes, $scopeField, $orFields) {
                $shouldUseSearchCollation = $this->shouldUseSearchCollation($query);
                foreach ($orFields as $field) {
                    if ($shouldUseSearchCollation) {
                        $query = $this->addSearchCollationToQuery($query, $field, $scopes[$scopeField]);
                    } else {
                        $query->orWhere($field, $this->getLikeOperator(), '%' . $scopes[$scopeField] . '%');
                    }
                    unset($scopes[$field]);
                }
            });
        }
    }

    /**
     * Search in relationship fields
     *
     * @param Builder $query
     * @param array $scopes
     * @param string $scopeField
     * @param string[] $relationshipFields
     */
    public function searchInRelationships($query, &$scopes, $scopeField, $relationshipFields = [])
    {
        $shouldUseSearchCollation = $this->shouldUseSearchCollation($query);

        if (isset($scopes[$scopeField]) && is_string($scopes[$scopeField])) {
            $searchValue = $scopes[$scopeField];

            // Group relationship fields by relationship name
            $relationshipGroups = [];
            foreach ($relationshipFields as $field) {
                $parts = explode('.', $field);
                if (count($parts) > 1) {
                    $relationshipColumn = array_pop($parts);
                    $relationshipName = implode('.', $parts);

                    if (! isset($relationshipGroups[$relationshipName])) {
                        $relationshipGroups[$relationshipName] = [];
                    }
                    $relationshipGroups[$relationshipName][] = $relationshipColumn;

                    // Remove the relationship field value from scopes
                    unset($scopes[$field]);
                }
            }

            // Add whereHas for each relationship group
            foreach ($relationshipGroups as $relationshipName => $columns) {
                $query->orWhereHas($relationshipName, function ($q) use ($columns, $searchValue, $shouldUseSearchCollation) {
                    $relatedModel = $q->getModel();

                    // Check if the related model is translatable
                    $isTranslatable = method_exists($relatedModel, 'isTranslatable') && $relatedModel->isTranslatable();
                    $translatedAttributes = $isTranslatable ? ($relatedModel->translatedAttributes ?? []) : [];

                    // Separate translated and non-translated columns
                    $regularColumns = [];
                    $translatedColumns = [];

                    foreach ($columns as $column) {
                        if ($isTranslatable && in_array($column, $translatedAttributes)) {
                            $translatedColumns[] = $column;
                        } else {
                            $regularColumns[] = $column;
                        }
                    }

                    $q->where(function ($q) use ($regularColumns, $translatedColumns, $searchValue, $relatedModel, $shouldUseSearchCollation) {
                        // Search in regular columns
                        if (! empty($regularColumns)) {
                            $tableName = $relatedModel->getTable();
                            foreach ($regularColumns as $column) {
                                if ($shouldUseSearchCollation) {
                                    $q = $this->addSearchCollationToQuery($q, $tableName . '.' . $column, $searchValue, $relatedModel);
                                } else {
                                    $q->orWhere($tableName . '.' . $column, $this->getLikeOperator(), '%' . $searchValue . '%');
                                }
                            }
                        }

                        // Search in translated columns
                        if (! empty($translatedColumns)) {
                            $q->orWhereHas('translations', function ($translationQuery) use ($translatedColumns, $searchValue, $shouldUseSearchCollation) {
                                $translationQuery->where(function ($tq) use ($translatedColumns, $searchValue, $shouldUseSearchCollation) {
                                    $translationModel = $tq->getModel();
                                    foreach ($translatedColumns as $column) {
                                        if ($shouldUseSearchCollation) {
                                            $tq = $this->addSearchCollationToQuery($tq, $column, $searchValue, $translationModel);
                                        } else {
                                            $tq->orWhere($column, $this->getLikeOperator(), '%' . $searchValue . '%');
                                        }
                                    }
                                });
                            });
                        }
                    });
                });
            }
        }
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

    public function getIgnoreFieldsBeforeSave()
    {
        return $this->ignoreFieldsBeforeSave;
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
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->model->$method(...$parameters);
    }
}
