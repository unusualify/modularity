<?php

namespace OoBook\CRM\Base\Support\Decomposers;

use OoBook\CRM\Base\Traits\Namable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OoBook\CRM\Base\Support\Finder;



class RelationParser implements Arrayable
{
    use Namable;

    protected $methods = [
        'belongsTo',
        'hasOne',
        'hasMany',
        'hasOneThrough',
        'hasManyThrough'
    ];

    protected $arguments = [
        'belongsTo'         => ['table', 'foreign_key', 'owner_key'],
        'hasOne'            => ['table', 'foreign_key', 'local_key'],
        'hasMany'           => ['table', 'foreign_key', 'local_key'],
        'hasOneThrough'     => ['table', 'table', 'foreign_key', 'foreign_key', 'local_key', 'local_key'],
        'hasManyThrough'    => ['table', 'table', 'foreign_key', 'foreign_key', 'local_key', 'local_key']
    ];


    /**
     * The model relation.
     *
     * @var string
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
            $method = $this->getMethod($relation);

            $parameters = $this->getParameters($method, $relation);

            $function = $this->getFunctionName($method, $relation);

            if($parameters !== false )
                $parsed[] = [
                    'function_name'  => $function,
                    'method'    => $method,
                    'parameters'=> $parameters
                ];
        }

        return $parsed;

    }

    public function getRelations()
    {
        if (is_null($this->relations)) {
            return [];
        }

        return explode(',', str_replace(' ', '', $this->relations));
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
                return  (new Finder())->getModel($param);
                break;

            default:
                return $param;

                break;
        }

    }

    public function generateMethodComment($attr)
    {
        $comment = '';

        $model = $this->getLowerName($this->model);

        switch ($attr['method']) {
            case 'belongsTo':
                $comment = "\n\t/**\n\t* Get the {$attr['function_name']} of the {$model}.\n\t*/";

                break;
            case 'hasOne':
                $comment = "\n\t/**\n\t* Get the {$attr['function_name']} associated with the {$model}.\n\t*/";

                break;
            case 'hasMany':
                $comment = "\n\t/**\n\t* Get the {$attr['function_name']} for the {$model}.\n\t*/";

                break;

            default:
                $comment = "\n\t/**\n\t* Get .\n\t*/";
                break;
        }

        return $comment;
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

            $args = implode(',', array_map(function($v){ return "'{$v}'";}, $attr['parameters']) );

            $comment = $this->generateMethodComment($attr);

            $methods[] = $comment."\n\tpublic function {$attr['function_name']}()\n\t{\n\t\treturn \$this->{$attr['method']}({$args});\n\t}";
        }

        return $methods;
    }



}
