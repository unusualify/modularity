<?php

namespace OoBook\CRM\Base\Support\Decomposers;

use OoBook\CRM\Base\Traits\ManageNames;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use OoBook\CRM\Base\Support\Finder;

class ModelRelationParser implements Arrayable
{
    use ManageNames;

    protected $methods = [
        'belongsTo',
        'hasOne',
        'hasMany',
        'hasOneThrough',
        'hasManyThrough',
        'belongsToMany'
    ];

    protected $arguments = [
        'belongsTo'         => ['table', 'foreign_key', 'owner_key'],
        'hasOne'            => ['table', 'foreign_key', 'local_key'],
        'hasMany'           => ['table', 'foreign_key', 'local_key'],
        'hasOneThrough'     => ['table', 'table', 'foreign_key', 'foreign_key', 'local_key', 'local_key'],
        'hasManyThrough'    => ['table', 'table', 'foreign_key', 'foreign_key', 'local_key', 'local_key'],
        'belongsToMany'    => ['table', 'table', 'foreign_pivot_key', 'related_pivot_key', 'parent_key', 'related_key', 'relation']
    ];

    protected $model;

    /**
     * The model relation.
     *
     * @var string
     *
     * $example
     */
    protected $relations;

    /**
     * Create new instance.
     *
     * @param string|null $schema
     */
    public function __construct($model, $relations = null)
    {
        $this->model = $model;
        $this->relations = $relations;
    }

    /**
     * Convert string relation to array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->parse($this->relations);
    }

    public function parse($relations)
    {

        $parsed = [];

        foreach ($this->getRelations() as $relation) {
            $schemas = explode(',', $relation);
            foreach ($schemas as $index => $schema) {
                if(!$index){
                    $method = $this->getMethod($schema);
                    $parameters = $this->getParameters($method, $schema);
                    $function = $this->getFunctionName($method, $schema);

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
                            'relationship_method'    => $method,
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

    public function getPivotTableName($relation_table)  {
        return $this->getSnakeCase($this->getPivotModelName($relation_table));
    }

    public function getPivotModelName($relation_table)  {
        return $this->model . $this->getStudlyName($this->getSingular($relation_table));
    }

    public function isCreatablePivotModel() {
        $creatable = false;

        foreach ($this->getRelations() as $relation) {
            $schemas = explode(',', $relation);

            if(count($schemas) > 1){
                $creatable = true;
                break;
            }
        }

        return $creatable;
    }

    public function getPivotModels() {
        $models = [];

        foreach ($this->getRelations() as $relation) {
            $schemas = explode(',', $relation);
            if(count($schemas) > 1){
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

    public function getRelations()
    {
        if (is_null($this->relations)) {
            return [];
        }

        return explode('|', str_replace(' ', '', $this->relations));
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
     * Get method name from relation.
     *
     * @param string $relation
     *
     * @return string
     */
    public function getMethod($relation)
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
    public function getParameters($method, $relation)
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
    public function getFunctionName($method, $relation)
    {
        $params = explode(':', str_replace($method . ':', '', $relation) );

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
    public function generateFunctionName($method, $params)
    {
        $name = '';

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
    public function generateParameter($method, $index, $param)
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

            default:
                return "'{$param}'";

                break;
        }

    }

    public function findModel($table) {
        return  "\\". (new Finder())->getModel($table) . "::class";
    }

    public function generateMethodComment($attr)
    {
        $comment = '';

        $model = $this->getLowerName($this->model);

        switch ($attr['relationship_method']) {
            case 'belongsTo':
                // $comment = "/**\n\t* Get the {$attr['relationship_name']} of the {$model}.\n\t*/";
                $comment = $this->commentStructure(["Get the {$attr['relationship_name']} that owns the {$model}."]);
                break;
            case 'hasOne':
                $comment = $this->commentStructure(["Get the {$attr['relationship_name']} associated with the {$model}."]);

                break;
            case 'hasMany':
                $comment =  $this->commentStructure(["Get the {$attr['relationship_name']} for the {$model}."]);

                break;
            case 'belongsToMany':
                $comment =  $this->commentStructure(["The {$attr['relationship_name']} that belong to the {$model}."]);
                break;

            default:
                $comment = "/**\n\t* Get .\n\t*/";
                break;
        }

        return $comment;
    }

    public function commentStructure($array)
    {
        $message = array_reduce($array, function($carry, $text){
            $carry .= "{$text}\n\t * ";
            return $carry;
        }, '');
        return "\t/**\n\t * {$message}\n\t */";
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
            $args = implode(', ', array_map(function($v){ return "{$v}";}, $attr['parameters']) );

            $comment = $this->generateMethodComment($attr);

            $relation = $this->getStudlyName($attr['relationship_method']);

            $return_type = "\Illuminate\Database\Eloquent\Relations\\$relation";

            $method_chain = "\$this->{$attr['relationship_method']}({$args})";
            if(count($attr['chain_methods'])){
                foreach ($attr['chain_methods'] as $key => $chain) {
                    $chain_args = implode(', ', array_map(function($v){ return "{$v}";}, $chain['parameters']) );
                    $method_chain .= "\n\t\t\t->{$chain['method_name']}({$chain_args})";
                    # code...
                }
            }
            $method_chain .= ";";

            $methods[] = $comment."\n\tpublic function {$attr['relationship_name']}() : {$return_type}\n\t{\n\t\treturn {$method_chain}\n\t}\n";
        }

        return $methods;
    }
}
