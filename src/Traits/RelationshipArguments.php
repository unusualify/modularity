<?php
namespace Unusualify\Modularity\Traits;

use Astrotomic\Translatable\Traits\Relationship;
use Illuminate\Support\Arr;
use Unusualify\Modularity\Facades\UFinder;

trait RelationshipArguments {

    public function getRelationshipArgumentRelated($name, $relationshipName, $arguments)
    {
        $value = "";

        switch ($relationshipName) {
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

}
