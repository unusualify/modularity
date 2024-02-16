<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Unusualify\Modularity\Facades\UFinder;

trait RelationTrait
{
    public $exceptRelations = [];

    /**
     * @param \Unusualify\Modularity\Entities\Model|null $object
     * @param array $fields
     * @return array
     */
    public function afterSaveRelationTrait($object, $fields)
    {
        foreach ($this->getMorphToRelations() as $relation => $types) {
            foreach ($types as $key => $type) {
                $name = $type['name'];
                $repository = $type['repository'];
                if(isset($fields[$name]) && $fields[$name]){
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
                    if(is_a($fields[$relation], 'Illuminate\Support\Collection')) {
                        $fields[$relation] = $fields[$relation]->toArray();
                    }

                    if(is_array($fields[$relation])){
                        $fields[$relation] = Arr::mapWithKeys($fields[$relation], function($item, $key) use($relatedPivotKey){
                            if( isset($item['pivot']) && isset($item['pivot'][$relatedPivotKey])){
                                return [$key => $item['pivot'][$relatedPivotKey]];
                            }
                            return is_array($item)
                                    ? [ $item[$relatedPivotKey] => Arr::except($item, [$this->getForeignKey()])]
                                    : [ $key => $item];
                        });

                        foreach ($fields[$relation] as $key => $value) {
                            if(is_array($value)){
                                // dd(
                                //     $key,
                                //     $value,
                                //     $fields[$relation]
                                // );
                                // $fields[$relation][$key] = $value['id'];
                            }
                        }

                    }

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
                // unset($fields[$relation]);
                // if (!empty($fields[$f])) {
                //     $fields = $this->prepareTreeviewField($fields, $f);
                // } else {
                //     $fields[$f] = null;
                // }
            }
        }
        foreach ($this->getHasManyRelations() as $relationName) {

            if (isset($fields[$relationName])) {
                $relation = $object->{$relationName}();
                $relatedLocalKey = $relation->getLocalKeyName(); //id
                $foreignKey = $relation->getForeignKeyName(); // parent_name_id

                $repository = UFinder::getRouteRepository( Str::singular($relationName), asClass: true);

                $idsDeleted = $relation->get()->pluck($relatedLocalKey)->toArray();

                foreach ($fields[$relationName] as $key => $data) {
                    if(isset($data[$relatedLocalKey])){

                        array_splice($idsDeleted, array_search($data[$relatedLocalKey], $idsDeleted), 1);

                        $repository->update($data[$relatedLocalKey], $data + [$foreignKey => $object->id]);
                    }else{
                        $repository->create(array_merge($data, [$foreignKey => $object->id]));
                    }
                }

                if(count($idsDeleted))
                    $repository->bulkDelete($idsDeleted);
            }
        }

        return $fields;
    }

    /**
     * @param
     * @return void
     */
    public function afterForceDeleteRelationTrait($object)
    {
        foreach ($this->getBelongsToManyRelations() as $relation) {
            // dd('afterForceDelete', $relation);
            $object->{$relation}()->detach();
        }
    }

    public function getFormFieldsRelationTrait($object, $fields, $schema = [])
    {

        $morphToRelations = $this->getMorphToRelations();
        // $hasManyRelations = $this->getHasManyRelations();
        $belongsToManyRelations = $this->getBelongsToManyRelations();

        foreach ($morphToRelations as $relation => $types) {
            $morphTo = null;
            foreach ($types as $index => $type) {

                $column_name = snakeCase($relation);
                if($object->{$column_name . '_type'} == $type['model']){
                    $morphTo = App::make($type['repository'])->getById($object->{$column_name . '_id'});
                    $fields[$type['name']] = $morphTo->id;
                }else if($morphTo){
                    $fields[$type['name']] = $morphTo->{$type['name']};
                    $morphTo = App::make($type['repository'])->getById($morphTo->{$type['name']});
                }else{
                    $fields[$type['name']] = null;
                }
                // dd($object, $fields, $index, $type);
            }
        }

        // dd($fields);
        foreach ($this->inputs() as $key => $input) {

            if(isset($input['name']) && in_array($input['name'], $belongsToManyRelations) ){
                if(preg_match('/repeater/', $input['type'])){
                    $query = $object->{$input['name']}();

                    if($input['orderable'] ?? false){
                        $query->orderBy('position');
                    }

                    $fields[$input['name']] = $query->get()->map(function($item){
                        // dd(
                        //     $item->pivot->active,
                        //     get_class_methods($item->pivot),
                        //     $item->pivot->getCasts(),
                        //     $item->pivot->toArray(),
                        //     // $item->getRawAttributes(),
                        // );
                        return $item->pivot->toArray();
                    });

                }else {
                    try {
                        //code...
                        $fields[$input['name']] = $object->{$input['name']}->map(function($item){ return $item->id; });
                    } catch (\Throwable $th) {
                        dd(
                            $object,
                            $object->permissions,
                            $input['name']
                        );
                    }
                }
            }
        }

        foreach ($schema as $key => $input) {
            if($input['type'] == 'custom-input-repeater' && isset($input['ext']) && $input['ext'] == 'relationship'){
                $repository = UFinder::getRouteRepository(Str::singular($input['name']), asClass:true);
                $relationshipName = $input['relationship'] ?? $input['name'];
                $records = $object->{$relationshipName};
                try {
                    $fields[$relationshipName] = (!!$records && !$records->isEmpty()) ? $object->{$input['name']}->map(function($model) use($input, $repository){
                        return $repository->getFormFields($model, $input['schema']);
                    }) : $repository->getFormFields($repository->newInstance(), $input['schema']);

                } catch (\Throwable $th) {

                    dd(
                        $object,
                        $relationshipName,
                        $object->{$relationshipName},
                    );
                }
            }
        }

        return $fields;
    }

    public function getBelongsToManyRelations()
    {
        return $this->definedRelations('BelongsToMany');

        if(method_exists($this->getModel(), 'getDefinedRelations')){
            return $this->getDefinedRelations('BelongsToMany');
        }

        return [];
    }

    public function getHasManyRelations()
    {
        return $this->definedRelations('HasMany');

        if(method_exists($this->getModel(), 'getDefinedRelations')){
            return $this->getDefinedRelations('HasMany');
        }

        return [];
    }

    public function getMorphToRelations()
    {
        // $reflector = new \ReflectionClass($this->model);

        // $relations = [];

        // foreach ($reflector->getMethods() as $reflectionMethod) {
        //     $returnType = $reflectionMethod->getReturnType();
        //     if ($returnType) {
        //         // if (in_array(class_basename($returnType->getName()), ['HasOne', 'HasMany', 'BelongsTo', 'BelongsToMany', 'MorphToMany', 'MorphTo'])) {
        //         if (in_array(class_basename($returnType->getName()), ['MorphOne'])) {
        //             $relations[] = $reflectionMethod->name;
        //         }
        //     }
        // }

        return collect($this->inputs())->reduce(function($acc, $curr){
            if(preg_match('/morphTo/', $curr['type'])){
                if(isset($curr['schema'])){
                    $routeCamelCase = camelCase($this->routeName());
                    $acc["{$routeCamelCase}able"] = Arr::map(array_reverse($curr['schema']), fn($item) => [
                        'name' => $item['name'],
                        'repository' => $item['repository'],
                        'model' => get_class( App::make($item['repository'])->getModel()),
                    ]);
                }
            }

            return $acc;
        }, []);
    }
}
