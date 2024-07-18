<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Unusualify\Modularity\Entities\TemporaryAsset;
use Unusualify\Modularity\Entities\Asset;
use Unusualify\Modularity\Services\Filepond\Filepond;

trait AssetTrait{

    public function setColumnsAssetTrait($columns, $inputs)
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


    public function afterSaveAssetTrait($object,$fields)
    {
        $columns = $this->getColumns(__TRAIT__);
        foreach ($columns as $role) {
            $this->saveFilePond($fields[$role], $object, $fields);
        }


    }


    public function getFormFieldsAssetTrait($object, $fields, $schema)
    {

        $columns = $this->getColumns(__TRAIT__);
        foreach ($columns as $role) {
            if(!isset($fields[$role]))
            {

                // $fields[$role] = $object->assets()->get()->map(function($asset){
                //     return  $asset->uuid;
                //     // return $asset->file_name;
                // });



                $fields[$role] = $object->assets()->get()->map(function($asset){
                    return $asset->mediableFormat();
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
