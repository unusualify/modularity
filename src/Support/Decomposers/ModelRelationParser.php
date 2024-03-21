<?php

namespace Unusualify\Modularity\Support\Decomposers;

use Unusualify\Modularity\Traits\ManageNames;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Unusualify\Modularity\Facades\UFinder;
use Unusualify\Modularity\Support\Finder;
use Unusualify\Modularity\Traits\RelationshipMap;

class ModelRelationParser implements Arrayable
{
    use ManageNames, RelationshipMap;

    protected $methods = [
        'belongsTo',
        'hasOne',
        'hasMany',
        'hasOneThrough',
        'hasManyThrough',
        'belongsToMany',
        'morphTo',
        'morphOne',
        'morphToMany',
    ];

    protected $arguments = [
        'belongsTo'         => ['table', 'foreign_key', 'owner_key'],
        'hasOne'            => ['table', 'foreign_key', 'local_key'],
        'hasMany'           => ['table', 'foreign_key', 'local_key'],
        'hasOneThrough'     => ['table', 'table', 'foreign_key', 'foreign_key', 'local_key', 'local_key'],
        'hasManyThrough'    => ['table', 'table', 'foreign_key', 'foreign_key', 'local_key', 'local_key'],
        'belongsToMany'     => ['table', 'table', 'foreign_pivot_key', 'related_pivot_key', 'parent_key', 'related_key', 'relation'],
        'morphTo'           => ['name', 'type', 'id', 'owner_key']
    ];

    protected $_argumentsMap = [
        'hasOne'            => ['related', 'foreignKey', 'localKey'],
        'hasOneThrough'     => ['related', 'through', 'firstKey', 'secondKey', 'localKey', 'secondLocalKey'],
        'morphOne'          => ['related', 'name', 'type', 'id', 'localKey'],
        'belongsTo'         => ['related', 'foreignKey', 'ownerKey', 'relation'],
        'morphTo'           => ['name', 'type', 'id', 'ownerKey'],
        'morphEagerTo'      => ['name', 'type', 'id', 'ownerKey'],
        'morphInstanceTo'   => ['name', 'type', 'id', 'ownerKey'],
        'hasMany'           => ['table', 'foreignKey', 'localKey'],
        'hasManyThrough'    => ['related', 'throught', 'firstKey', 'secondKey', 'localKey', 'secondLocalKey'],
        'morphMany'         => ['related', 'name', 'type', 'id', 'localKey'],
        'belongsToMany'     => ['related', 'table', 'foreignPivotKey', 'relatedPivotKey', 'parentKey', 'relatedKey', 'relation'],
        'morphToMany'       => ['related', 'name', 'table', 'foreignPivotKey', 'relatedPivotKey', 'parentKey', 'relatedKey', 'relation', 'inverse'],
        'morphedByMany'     => ['related', 'name', 'table', 'foreignPivotKey', 'relatedPivotKey', 'parentKey', 'relatedKey', 'relation'],
    ];

    protected $pivotableRelationships = [
        'belongsToMany',
        // 'morphToMany'
    ];

    protected $model;

    /**
     * The model relation.
     *
     * @var string
     *
     * $example
     */
    protected $relationships;

    /**
     * Create new instance.
     *
     * @param string|null $schema
     */
    public function __construct($model, $relationships = null)
    {
        $this->model = $model;

        $this->relationships = $relationships;

        $this->relationshipMap = unusualConfig('laravel-relationship-map', []);
    }

    /**
     * Convert string relation to array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->parse($this->relationships);
    }

    public function parse_($relations)
    {

        $parsed = [];

        foreach ($this->getRelationships() as $relation) {
            $schemas = explode(',', $relation);
            foreach ($schemas as $index => $schema) {
                if(!$index){
                    $relationship_method = $this->getMethod($schema);
                    $function = $this->getFunctionName($relationship_method, $schema);
                    $parameters = $this->getParameters($relationship_method, $schema);

                    $chainMethods = [];
                    if(count($schemas) > 1){
                        $pivotTable = $this->getPivotTableName($function);
                        $chainMethods[] = [
                            'method_name' => 'using',
                            'parameters' => [
                                $this->findModel($pivotTable)
                            ]
                        ];
                        $chainMethods[] = [
                            'method_name' => 'withPivot',
                            'parameters' => []
                        ];
                    }
                    if($parameters !== false )
                        $parsed[] = [
                            'relationship_name'  => $function,
                            'relationship_method'    => $relationship_method,
                            'return_type' => "\Illuminate\Database\Eloquent\Relations\\{$this->getStudlyName($relationship_method)}",
                            'parameters'=> $parameters,
                            'chain_methods' => $chainMethods
                        ];

                }else{ // for pivot chaining
                    $parsedIndex = count($parsed)-1;
                    $chainIndex = count($parsed[$parsedIndex]['chain_methods'])-1;
                    $parsed[count($parsed)-1]['chain_methods'][$chainIndex]['parameters'][] = "'{$this->getMethod($schema)}'";
                }
            }
        }


        return $parsed;

    }

    public function parse($relations)
    {

        $parsed = [];

        foreach ($this->getRelationships() as $relationship) {
            ($resp = $this->parseRelationshipSchema($relationship)) ? array_push($parsed, $resp) : null;
        }

        return $parsed;

    }

    public function getReverseRelationships()
    {
        $parsed = [];

        foreach ($this->getRelationships() as $relationship) {

            if(($resp = $this->parseReverseRelationshipSchema($relationship))){
                $parsed = $parsed + $resp;
            }
        }

        return $parsed;
    }

    public function writeReverseRelationships()
    {
        foreach ($this->getReverseRelationships() as $modelName => $format) {

            $modelClass = UFinder::getRouteRepository($modelName, asClass: true)->getModel();
            $reflector = new \ReflectionClass($modelClass);
            dd($modelClass, $reflector);
            // search ModuleRoute Relationships trait
            // foreach ($reflector->getTraits() as $traitNamespace => $trait) {
            //     if($trait->getShortName() == $reflector->getShortName() . 'Relationships'){
            //         $reflector = new \ReflectionClass($traitNamespace);
            //         break;
            //     }
            // }

            if(!$reflector->hasMethod($format['relationship_name'])){

                $filePath = $reflector->getFileName();
                $lines = file($filePath);
                $count = 0;
                $content = "";

                foreach($lines as $line) {
                    $count += 1;
                    // $content .= str_pad($count, 2, 0, STR_PAD_LEFT).". ".$line . "\n";
                    $content .= $line;
                }

                $pattern = "/(\})[^\}]*$/";

                $newContent = preg_replace($pattern, $this->renderFormat($format) . "\n}\n", $content);

                app('files')->put($filePath, $newContent);
            }
        }
    }

    public function hasCreatablePivotModel() {
        $creatable = false;

        foreach ($this->getRelationships() as $relationship) {
            // $schemas = explode(',', $relation);
            // if( count($schemas) > 1){
            //     $creatable = true;
            //     break;
            // }
            $schemas = explode(',', $relationship);
            $relationship_name = explode(':', $schemas[0])[0];

            if(in_array($relationship_name, $this->pivotableRelationships) && count($schemas) > 2){
                $creatable = true;
                break;
            }
        }

        return $creatable;
    }

    public function getPivotModels() {
        $models = [];

        foreach ($this->getRelationships() as $relationship) {
            // dd($relation);
            $schemas = explode(',', $relationship);
            $relationship_name = explode(':', $schemas[0])[0];
            if(count($schemas) > 2 && in_array($relationship_name, $this->pivotableRelationships)){
                foreach ($schemas as $index => $schema) {
                    if(!$index){ // relation_type:table_name
                        $method = $this->getMethod($schema);
                        $function = $this->getFunctionName($method, $schema);

                        $models[] = [
                            'class' => $this->getPivotModelName($function),
                            'fillables' => [
                                $this->getSnakeCase($this->model) . '_id',
                                $this->getSnakeCase($this->getSingular($function)) . '_id',
                            ],
                            'casts' => []
                        ];
                    } else { // other fields
                        $explodes = explode(':', $schema);
                        $field = Arr::get($explodes, 0);

                        $models[count($models)-1]['fillables'][] = $field;
                        if( count($explodes) > 1){
                            $type = Arr::get($explodes, 1);
                            $models[count($models)-1]['casts'][$field] = $this->castFieldType($type);
                        }
                    }
                }
            }
        }

        return $models;
    }

    public function getRelationships()
    {
        if (is_null($this->relationships)) {
            return [];
        }

        return explode('|', str_replace(' ', '', $this->relationships));
    }

    public function castFieldType($type) {
        $casted = $type;

        $castings = [
            'boolean' => 'string'
        ];

        if(array_key_exists($type, $castings)){
            $casted = $castings[$type];
        }

        return $casted;
    }

    /**
     * Render the migration to formatted script.
     *
     * @return string
     */
    public function render()
    {
        $methods = [];
        foreach ($this->toArray() as $attr) {

            $methods[] = $this->renderFormat($attr);
            // $args = implode(', ', array_map(function($v){ return "{$v}";}, $attr['parameters']) );

            // $comment = $this->generateMethodComment($attr);

            // $method_chain = "\$this->{$attr['relationship_method']}({$args})";
            // if(count($attr['chain_methods'])){
            //     foreach ($attr['chain_methods'] as $key => $chain) {
            //         $chain_args = implode(', ', array_map(function($v){ return "{$v}";}, $chain['parameters']) );
            //         $method_chain .= "\n\t\t\t->{$chain['method_name']}({$chain_args})";
            //         # code...
            //     }
            // }
            // $method_chain .= ";";

            // $methods[] = $comment."\n\tpublic function {$attr['relationship_name']}() : {$attr['return_type']}\n\t{\n\t\treturn {$method_chain}\n\t}\n";
        }

        return $methods;
    }

    public function renderFormat($attr) : string {

        $args = implode(', ', array_map(function($v){ return "{$v}";}, $attr['parameters']) );

        $comment = $this->generateMethodComment($attr);

        $method_chain = "\$this->{$attr['relationship_method']}({$args})";

        if(count($attr['chain_methods'])){
            foreach ($attr['chain_methods'] as $key => $chain) {
                $chain_args = implode(', ', array_map(function($v){ return "{$v}";}, $chain['parameters']) );
                $method_chain .= "\n\t\t\t->{$chain['method_name']}({$chain_args})";
                # code...
            }
        }
        $method_chain .= ";";

        return $comment."\n\tpublic function {$attr['relationship_name']}() : {$attr['return_type']}\n\t{\n\t\treturn {$method_chain}\n\t}\n";
    }





    // public function getPivotTableName($relation_table)  {
    //     return $this->getSnakeCase($this->getPivotModelName($relation_table));
    // }

    public function getPivotModelName_($relation_table)  {
        return $this->model . $this->getStudlyName($this->getSingular($relation_table));
    }
    /**
     * Get method name from relation.
     *
     * @param string $relation
     *
     * @return string
     */
    public function getMethod_($relation)
    {
        return Arr::get(explode(':', $relation), 0);
    }

    /**
     * Get method details.
     *
     * @param string $method
     * @param string $relation
     *
     * @return array
     */
    public function getParameters_($method, $relation)
    {
        $params = explode(':', str_replace($method . ':', '', $relation) );

        $parameters = [];

        foreach($params as $i => $param){

            if( ($res = $this->generateParameter($method, $i, $param)) === false)
                return false;

            $parameters[] = $res;
        }

        return $parameters;

    }

    /**
     * Get relationship function name.
     *
     * @param string $method
     * @param string $relation
     *
     * @return string
     */
    public function getFunctionName_($method, $relation)
    {
        $pattern = "/^({$method}:?)(.*)$/";
        $replace = '${2}';

        $params = explode(':',  preg_replace($pattern, $replace, $relation) );

        return $this->generateFunctionName($method, $params);
    }

    /**
     * Get parameters.
     *
     * @param string $method
     * @param integer $index
     * @param string $param
     *
     * @return array
     */
    public function generateFunctionName_($method, $params)
    {
        $name = '';

        switch ($method) {
            case 'morphTo':
                $name = $this->getCamelCase($this->model) . 'able';
            default:
                # code...
                break;
        }

        foreach($params as $i => $param){

            $format = $this->arguments[$method][$i];
            switch ($format) {
                case 'table':
                    switch ($method) {
                        case 'belongsTo':
                        case 'hasOne':
                            $name .= $this->getSingular($param);

                            break;
                        case 'hasOneThrough':
                            if($name !== '')
                                $name .= $this->getSingular($param);
                            else
                                $name .= '_'.$this->getSingular($param);

                            break;
                        case 'hasMany':
                            $name .= $this->getPlural($param);

                            break;
                        case 'hasManyThrough':
                            if($name !== '')
                                $name .= $this->getSingular($param);
                            else
                                $name .= '_'.$this->getPlural($param);

                            break;
                        case 'belongsToMany':
                            if($name !== '')
                                $name .= $this->getSingular($param);
                            else
                                $name .= '_'.$this->getPlural($param);

                            break;
                        case 'morphTo':
                            dd($method, $param);
                            if($name !== '')
                                $name .= $this->getSingular($param);
                            else
                                $name .= '_'.$this->getPlural($param);

                            break;
                        default:
                            # code...
                            break;
                    }


                default:
                    # code...
                    break;
            }
        }

        return $this->getCamelCase($name);

    }

    /**
     * Get parameters.
     *
     * @param string $method
     * @param integer $index
     * @param string $param
     *
     * @return array
     */
    public function generateParameter_($method, $index, $param)
    {
        $format = $this->arguments[$method][$index];

        switch ($format) {
            case 'table':
                // dd(
                //     $param,
                //     (new Finder())->getModel($param),
                //     "\\". (new Finder())->getModel($param) . "::class",
                //     get_class_methods( App::make((new Finder())->getModel($param)) )
                // );
                return  $this->findModel($param);
                return  (new Finder())->getModel($param);
                break;
            case 'name':
                return '__FUNCTION__';
            default:

                return "'{$param}'";

                break;
        }

    }

    public function findModel_($table) {
        return  "\\". (new Finder())->getModel($table) . "::class";
    }

}
