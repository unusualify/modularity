<?php

namespace Unusualify\Modularity\Repositories\Logic;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Facades\UFinder;

trait Relationships
{
    public $exceptRelations = [];

    /**
     * @param \Unusualify\Modularity\Entities\Model|null $object
     * @param array $fields
     * @return void
     */
    public function afterSaveRelationships($object, $fields)
    {

        foreach ($this->getMorphToManyRelations() as $relationName) {
            if (isset($fields[$relationName]) && $fields[$relationName] && $relationName != 'tags') {
                $object->{$relationName}()->sync($fields[$relationName]);
            }
        }

        foreach ($this->getMorphToRelations() as $relation => $types) {
            foreach ($types as $key => $type) {
                $name = $type['name'];
                $repository = $type['repository'];
                if (isset($fields[$name]) && $fields[$name]) {
                    // dd(
                    //     $object->{$relation}(),
                    //     get_class_methods(
                    //         $object->{$relation}(),
                    //     )
                    // );
                    // $object->files()->sync([]);
                    // $this->getFiles($fields)->each(function ($file) use ($object) {
                    //     $object->files()->attach($file['id'], Arr::except($file, ['id']));
                    // });

                    // $object->files()->sync([]);

                    $morphOne = App::make($repository)->getById($fields[$name]);

                    $object->{$this->getSnakeCase($relation) . '_id'} = $morphOne->id;
                    $object->{$this->getSnakeCase($relation) . '_type'} = get_class($morphOne);

                    $object->save();

                    // $object->{$relation}()->save($attach);
                    // $object->save();

                    // $object->{$relation}()->saveMany([]);
                    // $object->{$relation}()->saveMany($attach);

                    // $object->{$relation}()->updateOrCreate([],[$attach]);
                    break;
                }
            }
        }

        foreach ($this->getBelongsToManyRelations() as $relation) {
            $relatedPivotKey = $object->{$relation}()->getRelatedPivotKeyName();

            if (isset($fields[$relation])) {
                try {
                    if (is_a($fields[$relation], 'Illuminate\Support\Collection')) {
                        $fields[$relation] = $fields[$relation]->toArray();
                    }

                    if (is_array($fields[$relation])) {
                        $fields[$relation] = Arr::mapWithKeys($fields[$relation], function ($item, $key) use ($relatedPivotKey) {
                            if (isset($item['pivot']) && isset($item['pivot'][$relatedPivotKey])) {
                                return [$key => $item['pivot'][$relatedPivotKey]];
                            }

                            return is_array($item)
                                    ? [$item[$relatedPivotKey] => Arr::except($item, [$this->getForeignKey()])]
                                    : [$key => $item];
                        });

                        foreach ($fields[$relation] as $key => $value) {
                            if (is_array($value)) {
                                // dd(
                                //     $key,
                                //     $value,
                                //     $fields[$relation]
                                // );
                                // $fields[$relation][$key] = $value['id'];
                            }
                        }

                    }
                    // dd($relation, $fields[$relation], $object);
                    $object->{$relation}()->sync(
                        $fields[$relation]
                    );
                } catch (\Throwable $th) {
                    dd(
                        $relation,
                        $fields[$relation],
                        // $object->{$relation}(),
                        $th
                    );
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
                $repository = UFinder::getRouteRepository(Str::singular($relationName), asClass: true);

                $idsDeleted = $relation->get()->pluck($relatedLocalKey)->toArray();

                if (isset($fields[$relationName]) && is_array($fields[$relationName]) && count($fields[$relationName]) > 0) {
                    foreach ($fields[$relationName] as $key => $data) {

                        if (isset($data[$relatedLocalKey])) {

                            array_splice($idsDeleted, array_search($data[$relatedLocalKey], $idsDeleted), 1);

                            $repository->update($data[$relatedLocalKey], $data + [$foreignKey => $object->id]);
                        } else {
                            $repository->create(array_merge($data, [$foreignKey => $object->id]));
                        }
                    }
                }

                if (count($idsDeleted)) {
                    $repository->bulkDelete($idsDeleted);
                }
            }
        }

        return $fields;
    }

    /**
     * @return void
     */
    public function afterForceDeleteRelationships($object)
    {
        foreach ($this->getBelongsToManyRelations() as $relation) {
            try {
                $object->{$relation}()->detach();
            } catch (\Throwable $th) {
                // TODO - check if relation is realy exists
                continue;
            }
        }
    }

    public function prepareFieldsBeforeSaveRelationships($object, $fields)
    {
        foreach ($this->getHasManyRelations() as $relation) {
            // dd('afterForceDelete', $relation, );
            if (isset($fields[$relation])) {
                $values = array_values($fields[$relation]);
                $related = $object->{$relation}()->getRelated();
                if (in_array('Oobook\Snapshot\Traits\HasSnapshot', class_uses_recursive($related))) {
                    // The related model has the HasSnapshot trait
                    // You can add any additional logic here if needed
                    $idValues = array_reduce($values, function ($acc, $item) use ($related) {
                        if (! is_array($item)) {
                            $id = $item;
                            $acc[] = [
                                $related->getSnapshotSourceForeignKey() => $id,
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
        $morphToRelations = $this->getMorphToRelations();
        // $hasManyRelations = $this->getHasManyRelations();
        $belongsToManyRelations = $this->getBelongsToManyRelations();

        foreach ($this->getMorphToManyRelations() as $relationName) {
            if (array_key_exists($relationName, $inputs)) {
                $fields[$relationName] = $object->{$relationName}->map(fn ($rel) => $rel->id)->toArray();
            }
        }

        foreach ($morphToRelations as $relation => $types) {
            $morphTo = null;
            foreach ($types as $index => $type) {
                $column_name = snakeCase($relation);
                if ($object->{$column_name . '_type'} == $type['model']) {
                    $morphTo = App::make($type['repository'])->getById($object->{$column_name . '_id'});
                    $fields[$type['name']] = $morphTo->id;
                } elseif ($morphTo && $morphTo->{$type['name']}) {
                    $fields[$type['name']] = $morphTo->{$type['name']};
                    $morphTo = App::make($type['repository'])->getById($morphTo->{$type['name']});
                } elseif ($object->{$type['name']}) {
                    $fields[$type['name']] = $object->{$type['name']};
                } else {
                    $fields[$type['name']] = null;
                }
                // dd($object, $fields, $index, $type);
            }
        }

        foreach ($this->getHasManyRelations() as $relation) {
            if (isset($schema[$relation])) {
                // dd($object, $relation, $object->{$relation});
                $fields[$relation] = $object->{$relation};
            }
        }

        // dd($fields);
        foreach ($inputs as $key => $input) {

            if (isset($input['name']) && in_array($input['name'], $belongsToManyRelations)) {
                if (preg_match('/repeater/', $input['type'])) {
                    $query = $object->{$input['name']}();

                    if ($input['orderable'] ?? false) {
                        $query->orderBy('position');
                    }

                    $fields[$input['name']] = $query->get()->map(function ($item) {
                        // dd(
                        //     $item->pivot->active,
                        //     get_class_methods($item->pivot),
                        //     $item->pivot->getCasts(),
                        //     $item->pivot->toArray(),
                        //     // $item->getRawAttributes(),
                        // );
                        return $item->pivot->toArray();
                    });

                } else {
                    try {
                        // code...
                        $fields[$input['name']] = $object->{$input['name']}->map(function ($item) {
                            return $item->id;
                        });
                    } catch (\Throwable $th) {
                        dd(
                            $object,
                            $object->packageFeatures,
                            $input['name'],
                            $th
                        );
                    }
                }
            }
        }

        foreach ($schema as $key => $input) {
            if (isset($input['ext']) && $input['ext'] == 'relationship') {
                $repository = UFinder::getRouteRepository(Str::singular($input['name']), asClass: true);
                $relationshipName = $input['relationship'] ?? $input['name'];
                $records = $object->{$relationshipName};
                $fields[$relationshipName] = ((bool) $records && ! $records->isEmpty()) ? $object->{$input['name']}->map(function ($model) use ($input, $repository) {
                    return $repository->getFormFields($model, $input['schema']);
                }) : $repository->getFormFields($repository->newInstance(), $input['schema']);
                try {

                } catch (\Throwable $th) {

                    dd(
                        get_class($object),
                        $relationshipName,
                        $object->{$relationshipName},
                        backtrace_formatted(),
                        $th
                    );
                }
            }
        }

        return $fields;
    }

    public function _getShowFieldsRelationships($object, $fields, $schema = [])
    {
        // dd(
        //     $this->definedRelationsTypes()
        // );
        if (method_exists($this->model, 'definedRelationsTypes')) {
            foreach ($this->model->definedRelationsTypes() as $relationship => $relationshipType) {
                // if($relationship == 'prices'){
                //     dd(
                //         'prices',
                //         $relationshipType,
                //         $object->{$relationship}
                //     );
                // }
                switch ($relationshipType) {
                    case 'BelongsTos':
                        $fields["{$relationship}_show"] = $object->{$relationship}->getShowFormat();

                        break;
                    case 'MorphManys':
                        $fields["{$relationship}_show"] = $object->{$relationship}->map(fn ($model) => method_exists($model, 'getShowFormat') ? $model->getShowFormat() : $model->name)->implode(', ');

                        break;
                    case 'MorphToManys':
                        $fields["{$relationship}_show"] = $object->{$relationship}->map(fn ($model) => method_exists($model, 'getShowFormat') ? $model->getShowFormat() : $model->name)->implode(', ');

                        break;
                    case 'BelongsToMany':

                        break;

                    default:
                        try {
                            // code...
                            $record = $object->{$relationship};

                            if ($record instanceof \Illuminate\Database\Eloquent\Collection) {
                                // $record->map(function($model){
                                //     if(get_class_short_name($model) == 'Price'){
                                //         dd($model, modelShowFormat($model));
                                //     }
                                // });
                                // $fields["{$relationship}_show"] = $record->map(fn ($model) => modelShowFormat($model))->implode(', ');

                            } elseif ($record instanceof \Illuminate\Database\Eloquent\Model) {
                                // $fields["{$relationship}_show"] = modelShowFormat($record);

                            } elseif (! is_null($record)) {
                                dd(
                                    // $relationship,
                                    // $object,
                                    // $record,
                                    // $object->{$relationship}(),
                                    // $object->{$relationship}()->get()
                                    $related = $object->{$relationship}()->getRelated()->fill($record),
                                );

                                $fields["{$relationship}_show"] = null;
                            }
                        } catch (\Throwable $th) {
                            dd(
                                $object,
                                $record,
                                $relationship,
                                $this->definedRelationsTypes(),
                                $th
                            );
                            // throw $th;
                        }

                        // if($relationship == 'packages'){
                        //     $record->map(function($model){
                        //         dd(
                        //             $model,
                        //             $model->price(),
                        //             $model->price_formatted,
                        //             $model->price,
                        //         );
                        //     })->implode(', ');

                        // }

                        break;
                }
            }
        }

        return $fields;
    }

    public function getBelongsToManyRelations()
    {
        return $this->definedRelations('BelongsToMany');

        if (method_exists($this->getModel(), 'getDefinedRelations')) {
            return $this->getDefinedRelations('BelongsToMany');
        }

        return [];
    }

    public function getHasManyRelations()
    {
        return $this->definedRelations('HasMany');

        if (method_exists($this->getModel(), 'getDefinedRelations')) {
            return $this->getDefinedRelations('HasMany');
        }

        return [];
    }

    public function getMorphToRelations()
    {
        // dd($this->inputs(), $this->chunkInputs());
        return collect($this->inputs())->reduce(function ($acc, $curr) {
            if (preg_match('/morphTo/', $curr['type'])) {
                if (isset($curr['schema'])) {
                    $routeCamelCase = camelCase($this->routeName());
                    // dd($curr);
                    $acc["{$routeCamelCase}able"] = Arr::map(array_reverse($curr['schema']), function ($item) {
                        $repository = null;
                        if (! isset($item['repository'])) {
                            if (isset($item['connector'])) {
                                $parsedConnector = find_module_and_route($item['connector']);
                                $repository = Modularity::find($parsedConnector['module'])->getRepository($parsedConnector['route']);
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

                        return [
                            'name' => $item['name'],
                            'repository' => $repository::class,
                            'model' => $repository->getModel()::class,
                        ];
                    });
                }
            }

            return $acc;
        }, []);
    }

    public function getMorphManyRelations()
    {
        return $this->definedRelations('MorphMany');
    }

    public function getMorphToManyRelations()
    {
        return $this->definedRelations('MorphToMany');
    }
}
