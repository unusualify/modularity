<?php

namespace OoBook\CRM\Base\Repositories\Traits;


trait RelationTrait
{
    /**
     * @param \OoBook\CRM\Base\Entities\Model|null $object
     * @param array $fields
     * @return array
     */
    public function afterSaveRelationTrait($object, $fields)
    {
        foreach ($this->getBelongsToManyRelations() as $relation) {
            if (isset($fields[$relation])) {
                try {
                    if(is_array($fields[$relation])){

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
                    // dd(
                    //     $relation,
                    //     $fields[$relation]
                    // );
                    $object->{$relation}()->sync($fields[$relation]);
                } catch (\Throwable $th) {
                    dd($relation, $fields[$relation], $th);
                }
                // unset($fields[$relation]);
                // if (!empty($fields[$f])) {
                //     $fields = $this->prepareTreeviewField($fields, $f);
                // } else {
                //     $fields[$f] = null;
                // }
            }
        }

        return $fields;
    }

    public function getFormFieldsRelationTrait($object, $fields, $schema = [])
    {
        $relations = $this->getBelongsToManyRelations();

        foreach ($schema as $key => $input) {
            if(in_array($input['name'], $relations) ){

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
                    $fields[$input['name']] = $object->{$input['name']}->map(function($item){ return $item->id; });
                }
            }
        }

        return $fields;
    }

    public function getBelongsToManyRelations()
    {

        $reflector = new \ReflectionClass($this->model);

        $relations = [];
        foreach ($reflector->getMethods() as $reflectionMethod) {
            $returnType = $reflectionMethod->getReturnType();
            if ($returnType) {
                // if (in_array(class_basename($returnType->getName()), ['HasOne', 'HasMany', 'BelongsTo', 'BelongsToMany', 'MorphToMany', 'MorphTo'])) {
                if (in_array(class_basename($returnType->getName()), ['BelongsToMany'])) {
                    $relations[] = $reflectionMethod->name;
                }
            }
        }

        return $relations;

        dd($relations);
    }
}
