<?php
namespace Unusualify\Modularity\Traits;

use Astrotomic\Translatable\Traits\Relationship;
use Illuminate\Support\Arr;
use Unusualify\Modularity\Facades\UFinder;

trait RelationshipMap {

    use ManageNames, RelationshipArguments;

    protected $relationshipMap = [];

    protected $model;

    protected $propsDelimeter = "?";

    protected $propsIndicator = "&";

    protected $reverseMapping = [
        'belongsTo' => 'hasMany',
        'morphTo' => 'morphMany',
        'belongsToMany' => 'belongsToMany',
        'hasManyThrough' => 'hasOneThrough',
        'hasOneThrough' => 'hasManyThrough',
    ];


    /**
     *
     *
     * @return string | $relationshipName[:]args
     */
    public function createRelationshipSchema($name, $relationshipName, $arguments = []) :string
    {
        $name = $this->getStudlyName($name);
        $relationshipName = $this->getCamelCase($relationshipName);
        $parameters = $this->relationshipMap[$relationshipName];

        $parts = [$relationshipName];

        $props = '';

        if($relationshipName === 'morphTo'){
            if(count($arguments) > 0){
                $props = $this->propsDelimeter . implode($this->propsIndicator, $arguments);
            }
        }

        foreach ($parameters as $n => $p) {
            $parameter = (object) $p;
            $methodName = "getRelationshipArgument". $this->getStudlyName($n);

            if(method_exists($this, $methodName)){
                ($v = $this->{$methodName}($name, $relationshipName, $arguments, $this->model)) != false
                    ? array_push($parts, $v)
                    : null;

            }else if($parameter->required){
                dd($n, $parameter, $name, $relationshipName, $parameters);
            }else{
                break;
            }
        }
        return implode(':', $parts) . $props;
    }

    /**
     *
     * {relationshipName}:modelName:[..]params
     * @return array
     */
    public function parseRelationshipSchema($relationship) :array
    {
        $data = [];

        $schemas = explode(',', $relationship);

        foreach ($schemas as $index => $schema) {
            $schematic = explode($this->propsDelimeter, $schema);
            $schema = array_shift($schematic);
            $props = [];
            if(count($schematic) > 0)
                $props = explode($this->propsIndicator, $schematic[0]);

            if(!$index){

                $relationshipName = $this->getRelationshipName($schema);
                $methodName = $this->getRelatedMethodName($relationshipName, $schema);
                $arguments = $this->getRelationshipArguments($relationshipName, $schema);

                $chainMethods = [];

                // if($relationshipName == 'belongsToMany')
                //     dd($schema, $schemas);

                if(count($schemas) > 1 && $relationshipName == 'belongsToMany'){
                    // dd($this->model, $methodName, $relationshipName, $arguments);
                    $pivotTableName = $this->getPivotTableName($this->model, $methodName);

                    $pivotTableClass = UFinder::getModel($pivotTableName);

                    if($pivotTableClass){
                        $chainMethods[] = [
                            'method_name' => 'using',
                            'parameters' => [
                                "\\" . $pivotTableClass . "::class"
                            ]
                        ];
                        $chainMethods[] = [
                            'method_name' => 'withPivot',
                            'parameters' => []
                        ];
                    }
                }

                // if($relationshipName == 'morphTo')
                //     dd($methodName ,$arguments);

                if($arguments !== false )
                    $data = $this->relationshipFormat($this->model, $methodName, $relationshipName, $arguments, $chainMethods);
                    // $data = [
                    //     'relationship_name'  => $methodName,
                    //     'relationship_method'    => $relationshipName,
                    //     'return_type' => "\Illuminate\Database\Eloquent\Relations\\{$this->getStudlyName($relationshipName)}",
                    //     'parameters'=> $arguments,
                    //     'chain_methods' => $chainMethods
                    // ];



            }else{ // for pivot chaining
                if($data['relationship_method'] == 'belongsToMany' && count($data['chain_methods']) > 0){
                    $chainIndex = count($data['chain_methods'])-1;
                    $data['chain_methods'][$chainIndex]['parameters'][] = "'{$this->getMethodName($schema)}'";
                }
            }
        }

        return $data;
    }

    /**
     *
     * {relationshipName}:modelName:[..]params
     * @return array
     */
    public function parseReverseRelationshipSchema($relationship) :array
    {
        $data = [];

        $schemas = explode(',', $relationship);

        $lastModel = null;
        foreach ($schemas as $index => $schema) {
            $schematic = explode($this->propsDelimeter, $schema);
            $schema = array_shift($schematic);
            $props = [];

            if(count($schematic) > 0){
                $props = explode($this->propsIndicator, $schematic[0]);
            }
            if(!$index){

                /**
                 * Get all of the post's comments.
                 */
                // public function packages(): MorphMany
                // {
                    //     return $this->morphMany(Package::class, 'packageable');
                    // }

                $relationshipName = $this->getRelationshipName($schema);

                if(isset($this->reverseMapping[$relationshipName])){
                    $reverseRelationshipName = $this->reverseMapping[$relationshipName];
                    switch ($reverseRelationshipName) {
                        case 'hasManyThrough':
                            [$modelName, $intermediateName, $localKey, $secondLocalKey, $firstKey, $secondKey] = array_slice(explode(':', $schema),1);
                            $methodName = $this->getPlural($this->getCamelCase($this->model));
                            $targetModel = ($targetModel = UFinder::getRouteModel($this->model))
                            ? $targetModel
                            : get_class(UFinder::getRouteRepository($this->model, asClass: true)->getModel());
                            $intermediateModel = ($intermediateModel = UFinder::getRouteModel($intermediateName))
                            ? $intermediateModel
                            : get_class(UFinder::getRouteRepository($intermediateName, asClass: true)->getModel());

                            $data[$this->getStudlyName($modelName)] = $this->relationshipFormat(
                                modelName:$modelName,
                                methodName: $methodName,
                                relationshipName: $reverseRelationshipName,
                                arguments: [
                                    "\\" . $targetModel . "::class",
                                    "\\".$intermediateModel."::class",
                                    "'{$this->getCamelCase($modelName)}_id'",
                                    "'{$this->getCamelCase($intermediateName)}_id'",
                                    "'id'",
                                    "'id'",
                                ]
                                );
                            break;

                        case 'hasOneThrough':
                            $methodName = $this->getSingular($this->getCamelCase($this->model));
                            [$modelName, $intermediateName, $localKey, $secondLocalKey, $firstKey, $secondKey] = array_slice(explode(':', $schema),1);
                            $targetModel = ($targetModel = UFinder::getRouteModel($this->model))
                            ? $targetModel
                            : get_class(UFinder::getRouteRepository($this->model, asClass: true)->getModel());
                            $intermediateModel = ($intermediateModel = UFinder::getRouteModel($intermediateName))
                            ? $intermediateModel
                            : get_class(UFinder::getRouteRepository($intermediateName, asClass: true)->getModel());

                            $data[$this->getStudlyName($modelName)] = $this->relationshipFormat(
                                modelName: $modelName,
                                methodName: $methodName,
                                relationshipName: $reverseRelationshipName,
                                arguments: [
                                    "\\".$targetModel."::class",
                                    "\\".$intermediateModel."::class",
                                    "'id'",
                                    "'id'",
                                    "'{$this->getCamelCase($intermediateName)}_id'",
                                    "'{$this->getCamelCase($this->model)}_id'",
                                ]
                            );
                            break;

                        case 'morphMany':
                            $methodName = $this->getPlural($this->getCamelCase($this->model));
                            $related = ($related = UFinder::getRouteModel($this->model))
                                ? $related
                                : get_class(UFinder::getRouteRepository($this->model, asClass: true)->getModel());

                            if($related){

                                foreach ($props as $key => $targetName) {
                                    $arguments = [
                                        "\\" . $related . "::class",
                                        $this->getMorphToMethodName($this->model)
                                    ];
                                    $data[$this->getStudlyName($targetName)] = $this->relationshipFormat($targetName, $methodName, $reverseRelationshipName, $arguments);

                                }
                            }

                            break;
                        case 'belongsToMany':
                            $targetName = $this->getSingular($this->getRelatedMethodName($relationshipName, $schema));
                            $chainMethods = [];

                            if(count($schemas) > 1 && $relationshipName == 'belongsToMany'){
                                // dd($this->model, $methodName, $relationshipName, $arguments);
                                $pivotTableName = $this->getPivotTableName($this->model, $methodName);
                                dd(
                                    $pivotTableName
                                );
                                $pivotTableClass = UFinder::getModel($pivotTableName);

                                if($pivotTableClass){
                                    $chainMethods[] = [
                                        'method_name' => 'using',
                                        'parameters' => [
                                            "\\" . $pivotTableClass . "::class"
                                        ]
                                    ];
                                    $chainMethods[] = [
                                        'method_name' => 'withPivot',
                                        'parameters' => []
                                    ];
                                }
                            }

                            $reverseSchema = "{$reverseRelationshipName}:{$this->getStudlyName($this->model)}";
                            $relationshipName = $reverseRelationshipName;
                            $methodName = $this->getRelatedMethodName($reverseRelationshipName, $reverseSchema);
                            $arguments = $this->getRelationshipArguments($reverseRelationshipName, $reverseSchema);
                            // $methodName = $this->getPlural($this->getCamelCase($this->model));
                            $lastModel = $this->getStudlyName($targetName);

                            $data[$this->getStudlyName($targetName)] = $this->relationshipFormat($$targetName, $methodName, $reverseRelationshipName, $arguments, $chainMethods);

                            break;

                        default:
                            $targetName = $this->getRelatedMethodName($relationshipName, $schema);

                            $reverseSchema = "{$reverseRelationshipName}:{$this->getStudlyName($this->model)}";
                            $relationshipName = $reverseRelationshipName;
                            $methodName = $this->getRelatedMethodName($relationshipName, $reverseSchema);
                            $arguments = $this->getRelationshipArguments($relationshipName, $reverseSchema);
                            $chainMethods = [];

                            $data[$this->getStudlyName($targetName)] = $this->relationshipFormat($targetName, $methodName, $reverseRelationshipName, $arguments, $chainMethods);

                            break;
                    }
                }

            }else{ // for pivot chaining
                if($data[$lastModel]['relationship_method'] == 'belongsToMany' && count($data[$lastModel]['chain_methods']) > 0){
                    $chainIndex = count($data[$lastModel]['chain_methods'])-1;
                    $data[$lastModel]['chain_methods'][$chainIndex]['parameters'][] = "'{$this->getMethod($schema)}'";
                }
            }
        }

        return $data;
    }

    public function relationshipFormat($modelName, $methodName, $relationshipName, $arguments, $chainMethods = []) :mixed
    {
        return [
            'model_name' => $this->getStudlyName($modelName),
            'relationship_name'  => $methodName,
            'relationship_method'    => $relationshipName,
            'return_type' => "\Illuminate\Database\Eloquent\Relations\\{$this->getStudlyName($relationshipName)}",
            'parameters'=> $arguments,
            'chain_methods' => $chainMethods
        ];
    }

    /**
     * Get relationship method.
     *
     * @param string $relation
     *
     * @return string
     */
    public function getRelationshipName($schema)
    {
        return $this->getMethodName($schema);
    }

    /**
     * Get method name from schema.
     *
     * @param string $relation
     *
     * @return string
     */
    public function getMethodName($schema)
    {
        return Arr::get(explode(':', $schema), 0);
    }

    /**
     * Get .
     *
     * @param string $method
     * @param string $relation
     *
     * @return string
     */
    public function getRelatedMethodName($relationshipName, $schema)
    {
        $pattern = "/^({$relationshipName}:?)(.*)$/";

        $replace = '${2}';

        $arguments = explode(':',  preg_replace($pattern, $replace, $schema) );

        return $this->generateRelatedMethodName($relationshipName, $arguments);
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
    public function generateRelatedMethodName($relationshipName, $arguments)
    {
        $relatedMethodName = '';
        $parameters = $this->relationshipMap[$relationshipName];

        switch ($relationshipName) {
            case 'morphTo':
                $relatedMethodName = $this->getMorphToMethodName($this->model);
                break;
            case 'morphMany':
                dd(
                    $relationshipName,
                    $arguments
                );
                $relatedMethodName = $this->getMorphToMethodName($this->model);
                break;
            case 'belongsTo':
            case 'hasOne':
            case 'hasOneThrough':
                // dd($parameters, $relationshipName, $arguments);
                $position = $parameters['related']['position'];
                $relatedMethodName = $this->getSingular($arguments[$position]);
                // dd('belongsTo & hasOne', $parameters['related'], $arguments, $relatedMethodName);
                break;
            case 'hasManyThrough':
            case 'hasMany':
                $position = $parameters['related']['position'];
                $relatedMethodName = $this->getPlural($arguments[$position]);
                break;
            case 'belongsToMany':
                // dd('belongsToMany', $parameters, $arguments, $this->model);

                $position = $parameters['related']['position'];
                // $tablePosition = $parameters['table']['position'];

                $relatedMethodName = $this->getPlural($arguments[$position]);
                // if($relatedMethodName !== '')
                //     $relatedMethodName .= $this->getSingular($param);
                // else
                //     $relatedMethodName .= '_'.$this->getPlural($param);

                break;
            default:
                # code...
                break;
        }

        return $this->getCamelCase($relatedMethodName);

        foreach($params as $i => $param){

            $format = $this->arguments[$relationshipName][$i];
            switch ($format) {
                case 'table':
                    switch ($relationshipName) {
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
     * Get method details.
     *
     * @param string $method
     * @param string $relation
     *
     * @return array
     */
    public function getRelationshipArguments($relationshipName, $relationship)
    {
        $keys = explode(':', str_replace($relationshipName . ':', '', $relationship) );

        $arguments = [];

        foreach($keys as $i => $key){

            if( ($res = $this->generateRelationshipArgument($relationshipName, $i, $key)) === false)
                return false;

            $arguments[] = $res;
        }

        return $arguments;

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
    public function generateRelationshipArgument($relationshipName, $index, $argument)
    {
        $parameters = array_filter($this->relationshipMap[$relationshipName], fn($ar) => $ar['position'] == $index);
        $parameterName = array_keys($parameters)[0];
        $parameter = $parameters[$parameterName];

        $formattedArgument =  "'{$argument}'";

        switch ($parameterName) {
            case 'related':
                if($relationshipName == 'morphMany'){
                    dd(

                        $argument
                    );
                }
                $related = ($related = UFinder::getRouteModel($argument))
                    ? $related
                    : get_class(UFinder::getRouteRepository($argument, asClass: true)->getModel());

                $formattedArgument = "\\" . $related . "::class";

                break;
            case 'through':
                $through = ($through = UFinder::getRouteModel($argument))
                    ? $through
                    : get_class(UFinder::getRouteRepository($argument, asClass: true)->getModel());
                $formattedArgument = "\\" . $through . "::class";
                break;
            case 'name':
                return '__FUNCTION__';
            default:

                break;
        }
        return $formattedArgument;

    }

    public function generateMethodComment($attr)
    {
        $comment = '';

        $model = $this->getStudlyName($attr['model_name']);

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
            case 'morphTo':
                $comment =  $this->commentStructure(["Get the model that the {$model} belongs to"]);
                break;
            case 'hasManyThrough':
                $comment = $this->commentStructure(["The {$attr['relationship_name']} that belong to the {$model}."]);
                break;
            case 'hasOneThrough':
                $comment = $this->commentStructure(["The {$attr['relationship_name']} that owns the {$model}."]);
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

}
