<?php
namespace Unusualify\Modularity\Traits;

use Astrotomic\Translatable\Traits\Relationship;
use Illuminate\Support\Arr;
use Unusualify\Modularity\Facades\UFinder;

trait RelationshipArguments {
    use ManageNames;

    public function getRelationshipArgumentRelated($name, $relationshipName, $arguments)
    {
        $value = "";

        switch ($relationshipName) {
            case 'hasOneThrough':
            case 'hasManyThrough':
            case 'belongsToMany':
            case 'belongsTo':
                $value = $this->getStudlyName($name);
                break;
            case 'morphMany':
                $value = $this->getStudlyName($name);
                break;

            default:
                # code...
                break;
        }

        return $value;

    }

    public function getRelationshipArgumentForeignKey($name, $relationshipName, $arguments)
    {
        $value = "";

        switch ($relationshipName) {
            case 'hasManyThrough':
            case 'belongsTo':
                $value = $arguments[0] ?? $this->getForeignKeyFromName($name);
                break;

            default:
                # code...
                break;
        }

        return $value;

    }

    public function getRelationshipArgumentOwnerKey($name, $relationshipName, $arguments)
    {
        $value = "";

        switch ($relationshipName) {
            case 'belongsTo':
                $value = $arguments[1] ?? 'id';
                break;

            default:
                # code...
                break;
        }

        return $value;

    }

    public function getRelationshipArgumentName($name, $relationshipName, $arguments)
    {
        $value = "";

        switch ($relationshipName) {
            case 'belongsTo':
                $value = $arguments[1] ?? 'id';
                break;
            case 'morphMany':
                dd($name, $relationshipName, $arguments);
                $value = '';
                break;

            default:
                # code...
                break;
        }

        return $value;

    }


    public function getRelationshipArgumentThrough($name, $relationshipName, $arguments)
    {
        $value = "";

        switch ($relationshipName) {
            case 'hasManyThrough':
            case 'hasOneThrough':
                $value = $this->getStudlyName($arguments[0]);
                break;
            default:
                # code...
                break;
        }

        return $value;
    }

    public function getRelationshipArgumentFirstKey($name, $relationshipName, $arguments, $modelName){
        switch ($relationshipName) {
            case 'hasOneThrough':
                $value = $arguments[2] ?? 'id';
                break;
            case 'hasManyThrough':
                $value =  $this->getForeignKeyFromName($modelName);
                break;
            default:
                # code...
                break;
        }

        return $value ?? '';
    }

    public function getRelationshipArgumentSecondKey($name, $relationshipName, $arguments){
        switch ($relationshipName) {
            case 'hasOneThrough':
                $value = $arguments[3] ?? 'id';
                break;
            case 'hasManyThrough':
                $value =  $this->getForeignKeyFromName($arguments[0]);
                break;
            default:
                # code...
                break;
        }
        return $value ?? '';
    }

    public function getRelationshipArgumentLocalKey($name, $relationshipName, $arguments){
        switch ($relationshipName) {
            case 'hasOneThrough':
                $value = $arguments[4] ?? $this->getForeignKeyFromName($arguments[0]);
                break;
            case 'hasManyThrough':
                $value = $arguments[1] ?? 'id';
                break;

            default:
                # code...
                break;
        }
        return $value ?? '';
    }

    public function getRelationshipArgumentSecondLocalKey($name, $relationshipName, $arguments, $modelName){
        switch ($relationshipName) {
            case 'hasOneThrough':
                $value = $arguments[5] ?? $this->getForeignKeyFromName($name);
                break;
            case 'hasManyThrough':
                $value = $arguments[1] ?? 'id';
                break;

            default:
                # code...
                break;
        }
        return $value ?? '';
    }



}
