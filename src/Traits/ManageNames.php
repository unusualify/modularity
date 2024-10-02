<?php

namespace Unusualify\Modularity\Traits;

use Illuminate\Support\Str;

trait ManageNames
{
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

    public function getDBTableName($string)
    {

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

    protected function getStudlyNameFromForeignKey($foreign_key)
    {
        if (preg_match('/(.*)(_id)/', $foreign_key, $matches)) {
            return $this->getStudlyName($matches[1]);
        }

        return null;
    }

    protected function getForeignKeyFromName($name)
    {
        return $this->getSnakeCase($name) . '_id';
    }

    protected function getTableNameFromName($name)
    {
        return $this->getPlural($this->getSnakeCase($name));
    }

    protected function getMorphToMethodName($name)
    {
        return makeMorphToMethodName($name);
    }

    protected function getMorphPivotTableName($name)
    {
        return makeMorphPivotTableName($name);
    }

    protected function getPivotTableName($modelName1, $modelName2)
    {
        return $this->getSnakeCase($this->getStudlyName($modelName1) . $this->getStudlyName($modelName2));
    }

    protected function getCamelNameFromForeignKey($foreign_key)
    {
        if (preg_match('/(.*)(_id)/', $foreign_key, $matches)) {
            return $this->getCamelCase($matches[1]);
        }

        return null;
    }

    protected function getSnakeNameFromForeignKey($foreign_key)
    {
        if (preg_match('/(.*)(_id)/', $foreign_key, $matches)) {
            return $this->getSnakeCase($matches[1]);
        }

        return null;
    }
}
