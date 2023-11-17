<?php

namespace Unusualify\Modularity\Repositories\Traits;


trait TreeviewTrait
{
    /**
     * @param array $fields
     * @return array
     */
    public function prepareFieldsBeforeCreateTreeviewTrait($fields)
    {
        return $this->prepareFieldsBeforeSaveTreeviewTrait(null, $fields);
    }

    /**
     * @param \Unusualify\Modularity\Entities\Model|null $object
     * @param array $fields
     * @return array
     */
    public function prepareFieldsBeforeSaveTreeviewTrait($object, $fields)
    {

        foreach ($this->getTreeviewRelations() as $relation) {
            if (isset($fields[$relation])) {
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

    /**
     * @param \Unusualify\Modularity\Entities\Model|null $object
     * @param array $fields
     * @return array
     */
    public function afterSaveTreeviewTrait($object, $fields)
    {
        foreach ($this->getTreeviewRelations() as $relation) {
            if (isset($fields[$relation])) {
                try {

                    if(is_array($fields[$relation])){
                        foreach ($fields[$relation] as $key => $value) {
                            if(is_array($value)){
                                $fields[$relation][$key] = $value['id'];
                            }
                        }
                    }

                    $object->{$relation}()->sync($fields[$relation]);
                } catch (\Throwable $th) {
                    dd($th, $relation,$fields[$relation]);
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

    /**
     * @param array $fields
     * @param string $f
     * @return array
     */
    public function prepareTreeviewField($fields, $f)
    {
        if ($date = Carbon::parse($fields[$f])) {
            $fields[$f] = $date->format("Y-m-d H:i:s");
        } else {
            $fields[$f] = null;
        }

        return $fields;
    }

    public function getFormFieldsTreeviewTrait($object, $fields, $schema = [])
    {

        $treeviewRelations = $this->getTreeviewRelations();

        foreach ($schema as $key => $input) {
            if(in_array($input['name'], $treeviewRelations) ){
                $fields[$input['name']] = $object->{$input['name']}->map(function($item){ return $item->id; });
            }
        }
        // dd($this, $fields, $treeviewRelations, $schema, debug_backtrace());
        return $fields;
    }

    public function getTreeviewRelations()
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
