<?php
namespace OoBook\CRM\Base\Traits;

use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Str;

trait ManagesNames {

    public function getStudlyName($string)
    {
        return Str::studly($string);
    }

    public function getLowerName($string)
    {
        return Str::lower($string);
    }

    public function getPlural($string)
    {
        return Str::plural($string);
    }

    public function getHeadline($string)
    {
        return Str::headline($string);
    }

    public function getDBTableName($string){

        return Str::plural(Str::lower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string)));
    }

    public function getSingular($string)
    {
        return Str::singular($string);
    }

    public function getCamelCase($string)
    {
        return Str::camel($string);
    }

    public function getKebabCase($string)
    {
        return Str::kebab($string);
    }

    public function getSnakeCase($string)
    {
        return Str::snake($string);
    }

    public function getPascalCase($string)
    {
        return Str::studly($string);
    }

}
