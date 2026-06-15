<?php

namespace Unusualify\Modularous\Repositories\Logic;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Facades\ModularousFinder;
use Unusualify\Modularous\Facades\ModularousLog;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\RevisionsTrait;
use Unusualify\Modularous\Traits\CheckSnapshot;
use Unusualify\Modularous\Traits\ResolveConnector;

trait Relationships
{
    use CheckSnapshot,
        ResolveConnector;

    /**
     * When true, {@see RevisionsTrait::bypassAfterSaves} may set
     * `passAfterSaveRelationships` during pending-only revision saves so {@see afterSaveRelationships} is skipped.
     */
    protected bool $pendingBypassRevisionRelationships = true;

    public $exceptRelations = [];

    /**
     * @param Model|null $object
     * @param array $fields
     * @return void
     */
    public function afterSaveRelationships($object, $fields)
    {
        $mustTouchEloquentModel = false;

        foreach ($this->getMorphToManyRelations() as $relationName) {
            if (isset($fields[$relationName]) && $fields[$relationName] && $relationName != 'tags') {
                $result = $object->{$relationName}()->sync($fields[$relationName]);

                if (! $mustTouchEloquentModel && ($result['updated'] > 0 || $result['attached'] > 0 || $result['detached'] > 0)) {
                    $mustTouchEloquentModel = true;
                }
            }
        }

        foreach ($this->getMorphToRelations() as $relation => $types) {
            foreach ($types as $key => $type) {
                $name = $type['name'];
                $model = $type['model'];

                if (isset($fields[$name]) && $fields[$name]) {
                    $morphOne = $model::find($fields[$name]);
                    $object->{$this->getSnakeCase($relation) . '_id'} = $morphOne->id;
                    $object->{$this->getSnakeCase($relation) . '_type'} = get_class($morphOne);
                    $object->save();

                    break;
                }
            }
        }

        foreach ($this->getBelongsToManyRelations() as $relation) {
            $relatedPivotKey = $object->{$relation}()->getRelatedPivotKeyName();

            if (isset($fields[$relation])) {
                $payload = $fields[$relation];
                try {
                    if (is_a($payload, 'Illuminate\Support\Collection')
                        || is_a($payload, 'Illuminate\Database\Eloquent\Collection')) {
                        $payload = $payload->toArray();
                    }

                    if (is_array($payload)) {
                        $payload = Arr::mapWithKeys($payload, function ($item, $key) use ($relatedPivotKey) {
                            if (isset($item['pivot']) && isset($item['pivot'][$relatedPivotKey])) {
                                return [$key => $item['pivot'][$relatedPivotKey]];
                            }

                            return is_array($item)
                                    ? [$item[$relatedPivotKey] => Arr::except($item, [$this->getForeignKey()])]
                                    : [$key => $item];
                        });
                    }

                    $result = $object->{$relation}()->sync(
                        $payload
                    );

                    if (! $mustTouchEloquentModel && ($result['updated'] > 0 || $result['attached'] > 0 || $result['detached'] > 0)) {
                        $mustTouchEloquentModel = true;
                    }
                } catch (\Throwable $th) {
                    ModularousLog::critical('Error syncing belongsToMany relationship on afterSaveRelationships', [
                        'repository' => get_class($this),
                        'relationName' => $relation,
                        'error' => $th->getMessage(),
                        'data' => $fields[$relation],
                    ]);
                }
            } elseif (array_key_exists($relation, $fields)) {
                $object->{$relation}()->sync([]);
            }
        }

        foreach ($this->getHasManyRelations() as $relationName) {

            if (array_key_exists($relationName, $fields)) {
                $relation = $object->{$relationName}();
                $relatedLocalKey = $relation->getLocalKeyName(); // id
                $foreignKey = $relation->getForeignKeyName(); // parent_name_id
                $repository = ModularousFinder::getRouteRepository(Str::singular($relationName), asClass: true);
                $hasRepository = (bool) $repository && $repository instanceof Repository;

                if (isset($fields[$relationName])) {
                    $idsDeleted = $hasRepository ?
                        $relation->get()->pluck($relatedLocalKey)->toArray()
                        : [];

                    if (is_array($fields[$relationName]) && count($fields[$relationName]) > 0) {
                        foreach ($fields[$relationName] as $key => $data) {

                            if (is_array($data) && Arr::isAssoc($data)) {
                                if ($hasRepository) {
                                    if (isset($data[$relatedLocalKey])) {
                                        array_splice($idsDeleted, array_search($data[$relatedLocalKey], $idsDeleted), 1);
                                        $result = $repository->update($data[$relatedLocalKey], $data + [$foreignKey => $object->id], options: ['preventDependentWarming' => true]);
                                        // TODO: check if this is needed
                                        // if($nestedObject->wasChanged()){
                                        //     $object->addChangedRelationships($relationName, $data);
                                        // }
                                        if (! $mustTouchEloquentModel && $result) {
                                            $mustTouchEloquentModel = true;
                                        }
                                    } else {
                                        $repository->create(array_merge($data, [$foreignKey => $object->id]), options: ['preventDependentWarming' => true]);

                                        if (! $mustTouchEloquentModel) {
                                            $mustTouchEloquentModel = true;
                                        }
                                        $object->addChangedRelationships($relationName, $data);

                                    }
                                } else {
                                    $idsDeleted = [];
                                }

                            } elseif (is_numeric($data)) {
                                ModularousLog::critical('Found numeric data in hasMany relationship on afterSaveRelationships', [
                                    'relationName' => $relationName,
                                    'data' => $data,
                                    'idsDeleted' => $idsDeleted,
                                    'repository' => get_class($this),
                                    'relationRepository' => $repository::class,
                                ]);
                                if (in_array($data, $idsDeleted)) {
                                    array_splice($idsDeleted, array_search($data, $idsDeleted), 1);
                                }
                            }
                        }

                        if (count($idsDeleted) > 0) {
                            $repository->bulkDelete($idsDeleted);
                            if (! $mustTouchEloquentModel) {
                                $mustTouchEloquentModel = true;
                            }
                            $object->addChangedRelationships($relationName, $idsDeleted);
                        }
                    }
                }

            }
        }

        foreach ($this->getMorphManyRelations() as $relationName) {
            // PricesTrait is a special case, we don't want to update prices when morphMany relations are updated
            if (classHasTrait($this, 'Unusualify\Modularous\Repositories\Traits\PricesTrait') && in_array($relationName, $this->getColumns('Unusualify\Modularous\Repositories\Traits\PricesTrait'))) {
                continue;
            }
            if (isset($fields[$relationName]) && $fields[$relationName] && $relationName != 'tags') {
                $relation = $object->{$relationName}();
                $relatedLocalKey = $relation->getLocalKeyName(); // id
                $relatedModel = $relation->getRelated();
                $idsDeleted = $relation->get()->pluck($relatedLocalKey)->toArray();

                foreach ($fields[$relationName] as $key => $morphManyData) {
                    if (isset($morphManyData['id']) && $morphManyData['id']) {
                        $record = $relatedModel->find($morphManyData['id']);

                        array_splice($idsDeleted, array_search($morphManyData[$relatedLocalKey], $idsDeleted), 1);

                        $record->update($morphManyData);

                        if (! $mustTouchEloquentModel && $record->wasChanged()) {
                            $mustTouchEloquentModel = true;
                        }
                    } else {
                        $object->{$relationName}()->create($morphManyData);

                        if (! $mustTouchEloquentModel) {
                            $mustTouchEloquentModel = true;
                        }
                    }
                }

                if (count($idsDeleted)) {
                    $relatedModel->whereIn($relatedLocalKey, $idsDeleted)->delete();
                    if (! $mustTouchEloquentModel) {
                        $mustTouchEloquentModel = true;
                    }
                }
            }
        }

        $this->letEloquentModelBeTouched($mustTouchEloquentModel);

        return $fields;
    }

    /**
     * @return void
     */
    public function afterForceDeleteRelationships($object)
    {
        foreach ($this->getBelongsToManyRelations() as $relation) {
            $object->{$relation}()->detach();
        }
    }

    public function prepareFieldsBeforeSaveRelationships($object, $fields)
    {
        foreach ($this->getHasManyRelations() as $relation) {
            // dd('afterForceDelete', $relation, );
            if (isset($fields[$relation])) {
                $values = array_values($fields[$relation]);
                $related = $object->{$relation}()->getRelated();
                if ($this->isSnapshotRelation($related)) {
                    // The related model has the HasSnapshot trait
                    // You can add any additional logic here if needed
                    $idValues = array_reduce($values, function ($acc, $item) use ($related) {
                        if (! is_array($item)) {
                            $id = $item;
                            $acc[] = [
                                $this->getSnapshotSourceForeignKey($related) => $id,
                            ];
                        }

                        return $acc;
                    }, []);

                    if (count($idValues)) {
                        $fields[$relation] = $idValues;
                    }
                }
            }
        }

        return $fields;
    }

    public function getFormFieldsRelationships($object, $fields, $schema = [])
    {
        $inputs = $this->inputs();

        foreach ($this->getMorphToManyRelations() as $relationName) {
            if (array_key_exists($relationName, $inputs)) {
                $fields[$relationName] = $object->{$relationName}->map(fn ($rel) => $rel->id)->toArray();
            }
        }

        foreach ($this->getMorphToRelations() as $relation => $types) {
            $morphTo = null;
            foreach ($types as $index => $type) {
                $column_name = snakeCase($relation);
                if ($object->{$column_name . '_type'} == $type['model']) {
                    $morphTo = App::make($type['model'])->find($object->{$column_name . '_id'});
                    $fields[$type['name']] = $morphTo->id;
                } elseif ($object->{$type['name']}) {
                    $fields[$type['name']] = $object->{$type['name']};
                } else {
                    $fields[$type['name']] = null;
                }
            }
        }

        foreach ($this->getHasManyRelations() as $relation) {
            if (isset($schema[$relation])) {
                $fields[$relation] = $object->{$relation};
            }
        }

        $belongsToManyRelations = $this->getBelongsToManyRelations();
        $morphManyRelations = $this->getMorphManyRelations();

        foreach ($inputs as $input) {
            if (isset($input['name'])) {
                if (in_array($input['name'], $belongsToManyRelations)) {
                    $relationshipName = $input['name'];
                    if (preg_match('/repeater/', $input['type'])) {
                        $query = $object->{$relationshipName}();

                        if ($input['orderable'] ?? false) {
                            $query->orderBy('position');
                        }

                        $fields[$relationshipName] = $query->get()->map(function ($item) {
                            return $item->pivot->toArray();
                        });

                    } else {
                        $fields[$input['name']] = $object->{$input['name']}->map(function ($item) {
                            return $item->id;
                        });
                    }
                } elseif (in_array($input['name'], $morphManyRelations)) {
                    if (preg_match('/repeater/', $input['type'])) {
                        $query = $object->{$input['name']}();

                        $columns = array_reduce($input['schema'] ?? [], function ($acc, $item) {
                            if (isset($item['name'])) {
                                $acc[] = $item['name'];
                            }

                            return $acc;
                        }, []);

                        if ($input['draggable'] ?? false) {
                            $columns[] = 'position';
                            $query->orderBy('position');
                        }

                        $fields[$input['name']] = $query->get()->map(function ($item) use ($columns) {
                            return Arr::only($item->toArray(), $columns);
                        });
                    }
                }
            }

            if (isset($input['connectedRelationship']) && is_string($input['connectedRelationship'])) {
                $fields[$input['connectedRelationship']] = $object->{$input['connectedRelationship']};
            }
        }

        foreach ($schema ?? [] as $input) {
            if (isset($input['ext']) && $input['ext'] == 'relationship') {
                $repository = ModularousFinder::getRouteRepository(Str::studly(Str::singular($input['name'])), asClass: true);
                $relationshipName = $input['relationship'] ?? $input['name'];
                $records = $object->{$relationshipName};
                $appends = $input['relationshipAppends'] ?? [];
                $fields[$relationshipName] = ((bool) $records && ! $records->isEmpty()) ? $object->{$input['name']}->map(function ($model) use ($input, $repository, $appends) {
                    $data = [
                        'id' => $model->getKey(),
                    ];
                    foreach ($appends as $append) {
                        $data[$append] = $model->{$append};
                    }

                    return [
                        ...$data,
                        ...$repository->getFormFields($model, $input['schema'], noSerialization: ! ($input['isSerialized'] ?? false)),
                    ];
                }) : $repository->getFormFields($repository->newInstance(), $input['schema'], noSerialization: ! ($input['isSerialized'] ?? false));

            }
        }

        return $fields;
    }

    public function getBelongsToManyRelations()
    {
        return $this->definedRelations('BelongsToMany');
    }

    public function getHasManyRelations()
    {
        return $this->definedRelations('HasMany');
    }

    public function getMorphManyRelations()
    {
        return $this->definedRelations('MorphMany');
    }

    public function getMorphToManyRelations()
    {
        return $this->definedRelations('MorphToMany');
    }

    public function getMorphToRelations()
    {
        return collect($this->getRawChunkedInputs(all: false, noGroupChunk: false))->reduce(function ($acc, $curr) {
            if (preg_match('/morphTo/', $curr['type'])) {
                if (isset($curr['schema'])) {
                    $modelName = get_class_short_name($this->getModel());
                    $morphToName = $curr['relationshipName'] ?? makeMorphToName($modelName, suffix: 'able');
                    $acc[$morphToName] = Arr::map(array_reverse($curr['schema']), function ($item) {
                        $repository = null;
                        $model = null;

                        if (isset($item['model'])) {
                            $model = $item['model'];
                            if (! class_exists($model)) {
                                throw new \Exception('Model not found on morphTo input: ' . $item['model'] . ' on ' . $item['name']);
                            }
                        } else {
                            if (! isset($item['repository'])) {
                                if (isset($item['connector'])) {
                                    $repository = $this->findConnectorRepository($item['connector']);
                                } elseif (isset($item['newConnector'])) {
                                    $repository = $this->findNewConnectorRepository($item['newConnector']);
                                } else {
                                    throw new \Exception('Repository or connector not found on morphTo input: ' . $item['name']);
                                }
                            } else {
                                if (class_exists($item['repository'])) {
                                    $repository = App::make($item['repository']);
                                } else {
                                    throw new \Exception('Repository not found on morphTo input: ' . $item['name']);
                                }
                            }

                            $model = $repository->getModel()::class;
                        }

                        return [
                            'name' => $item['name'],
                            'model' => $model,
                        ];
                    });
                }
            }

            return $acc;
        }, []);
    }
}
