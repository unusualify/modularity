<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Unusualify\Modularity\Services\Filepond\Filepond;

trait FilepondsTrait
{

    public function setColumnsFilepondsTrait($columns, $inputs)
    {
        $traitName = get_class_short_name(__TRAIT__);

        $columns[$traitName] = collect($inputs)->reduce(function($acc, $curr){
            if(preg_match('/filepond/', $curr['type'])){
                $acc[] = $curr['name'];
            }
            return $acc;
        }, []);

        return $columns;
    }


    public function afterSaveFilepondsTrait($object,$fields)
    {
        $columns = $this->getColumns(__TRAIT__);

        foreach ($columns as $role) {
            $this->saveFilePond($fields[$role], $object, $fields);
        }
    }


    public function getFormFieldsFilepondsTrait($object, $fields, $schema)
    {

        $columns = $this->getColumns(__TRAIT__);

        foreach ($columns as $role) {
            if(!isset($fields[$role])){
                $fields[$role] = $object->fileponds()->get()->map(function($filepond) use ($object){
                    return $filepond->mediableFormat() + [
                        'id' => $object->id,
                    ];
                });
            }
        }


        return $fields;
    }

    public function saveFilePond($files, $object, $fields)
    {
        FilePond::saveFile($files, $object);
    }


}
