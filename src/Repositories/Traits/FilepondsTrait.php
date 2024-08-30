<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Unusualify\Modularity\Services\Filepond\Filepond;

trait FilepondsTrait
{

    public function setColumnsFilepondsTrait($columns, $inputs)
    {
        $traitName = get_class_short_name(__TRAIT__);

        $_columns = collect($inputs)->reduce(function($acc, $curr){
            if(preg_match('/filepond/', $curr['type'])){
                $acc[] = $curr['name'];
            }
            return $acc;
        }, []);
        $columns[$traitName] = array_unique(array_merge ($this->traitColumns[$traitName] ?? [], $_columns));

        return $columns;
    }


    public function afterSaveFilepondsTrait($object,$fields)
    {
        $columns = $this->getColumns(__TRAIT__);

        foreach ($columns as $role) {
            $files = data_get($fields, $role);
            if($files){
                FilePond::saveFile($object, $files, $role);
            }
        }
    }

    public function _beforeSaveFilepondsTrait($object,$fields)
    {
        $columns = $this->getColumns(__TRAIT__);
        dd($columns, $fields);
        foreach ($columns as $role) {
            $files = data_get($fields, $role);
            if($files){
                dd($role, $files, $fields);
                // $this->saveFilePond($value, $object, $fields);
                // $this->saveFilePond($object, $files, $role);
                FilePond::saveFile($object, $files, $role);
            }
        }
        dd($columns);
    }


    public function getFormFieldsFilepondsTrait($object, $fields, $schema)
    {
        $columns = $this->getColumns(__TRAIT__);

        $fileponds = $object->fileponds()->get()->groupBy('role');
        foreach ($columns as $role) {
            if(!isset($fields[$role])){
                $fields[$role] = [];
                if(isset($fileponds[$role])){
                    $fields[$role] = $fileponds[$role]->map(function($filepond) use ($object){
                        return $filepond->mediableFormat() + [
                            'id' => $object->id,
                        ];
                    });
                }
            }
        }

        return $fields;
    }
}
