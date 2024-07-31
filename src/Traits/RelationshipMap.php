<?php
namespace Unusualify\Modularity\Traits;

use Astrotomic\Translatable\Traits\Relationship;
use Illuminate\Support\Arr;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Facades\UFinder;

use function Laravel\Prompts\select;

trait RelationshipMap
{

    use ManageNames, RelationshipArguments;

    protected $relationshipParametersMap = [];

    protected $model;

    protected $propsDelimeter = "?";

    protected $propsIndicator = "&";

    protected $reverseMapping = [
        'belongsTo' => 'hasMany',
        'morphTo' => 'morphMany',
        'morphToMany' => 'morphedByMany',
        'hasOneThrough' => 'hasManyThrough',

        'belongsToMany' => 'belongsToMany',
        'hasMany' => 'belongsTo',
        'hasOne' => 'belongsTo',
        'hasManyThrough' => 'hasOneThrough',
    ];


    protected $modelClasses = [];

    /**
     *
     *
     * @return string | $relationshipName[:]args
     */
    public function createRelationshipSchema($name, $relationshipName, $arguments = []) :string
    {
        $name = $this->getStudlyName($name);
        $relationshipName = $this->getCamelCase($relationshipName);
        $parameters = $this->relationshipParametersMap[$relationshipName];

        $parts = [$relationshipName];

        $props = '';

        if($relationshipName === 'morphTo'){
            if(count($arguments) > 0){
                $props = $this->propsDelimeter . implode($this->propsIndicator, $arguments);
            }
        }

        // if($relationshipName === 'belongsToMany'){
        //     if(count($arguments) > 0){
        //         $props = $this->propsDelimeter . implode($this->propsIndicator, $arguments);
        //     }
        // }
        // dd($relationshipName, $name, $arguments);
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

                if($relationshipName == 'morphedByMany')
                    continue;

                $methodName = $this->getRelatedMethodName($relationshipName, $schema);
                $arguments = $this->getRelationshipArguments($relationshipName, $schema);

                $chainMethods = [];

                // if($relationshipName == 'belongsToMany')
                //     dd($schema, $schemas);

                if(count($schemas) > 1 && $relationshipName == 'belongsToMany'){
                    // dd($this->model, $methodName, $relationshipName, $arguments);
                    $pivotTableName = $this->getPivotTableName($this->model, singularize($methodName));

                    $pivotTableClass = UFinder::getModel($pivotTableName);

                    if($pivotTableClass){
                        $chainMethods[] = [
                            'method_name' => 'using',
                            'arguments' => [
                                "\\" . $pivotTableClass . "::class"
                            ]
                        ];
                        $chainMethods[] = [
                            'method_name' => 'withPivot',
                            'arguments' => []
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
                    //     'arguments'=> $arguments,
                    //     'chain_methods' => $chainMethods
                    // ];



            }else{ // for pivot chaining
                if($data['relationship_method'] == 'belongsToMany' && count($data['chain_methods']) > 0){
                    $chainIndex = count($data['chain_methods'])-1;
                    $data['chain_methods'][$chainIndex]['arguments'][] = "'{$this->getMethodName($schema)}'";
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
    public function parseReverseRelationshipSchema($relationship, bool $test = false) :array
    {
        $data = [];

        $schemas = explode(',', $relationship);
        $lastModel = null;
        $lastIndex = null;

        foreach ($schemas as $index => $schema) {
            $schematic = explode($this->propsDelimeter, $schema);
            $schema = array_shift($schematic);
            $props = [];

            if(count($schematic) > 0){
                $props = explode($this->propsIndicator, $schematic[0]);
            }

            if(!$index){
                $lastModel = null;

                $relationshipName = $this->getRelationshipName($schema);

                if(isset($this->reverseMapping[$relationshipName])){

                    $reverseRelationshipName = $this->reverseMapping[$relationshipName];
                    if($relationshipName == 'belongsTo'){
                        $modelName = studlyName($this->getRelatedMethodName($relationshipName, $schema));
                        $reverseRelationshipName = select(
                            label: "Select reverse relationship of belongsTo on '{$modelName}' model?",
                            options: ['hasMany', 'hasOne']
                        );
                    }else if($relationshipName == 'morphTo'){
                        $modelName = studlyName($this->getRelatedMethodName($relationshipName, $schema));
                        $reverseRelationshipName = select(
                            label: "Select reverse relationship of morphTo on '{$modelName}' model?",
                            options: ['morphMany', 'morphOne']
                        );
                    }

                    switch ($reverseRelationshipName) {
                        case 'hasManyThrough':
                            [$modelName, $intermediateName, $localKey, $secondLocalKey, $firstKey, $secondKey] = array_slice(explode(':', $schema),1);
                            $methodName = $this->getPlural($this->getCamelCase($this->model));

                            $targetModelClass = $this->getModelClass($this->model, $relationshipName);
                            $intermediateModelClass = $this->getModelClass($intermediateName, $relationshipName);

                            $data[] = $this->relationshipFormat(
                                modelName: $modelName,
                                methodName: $methodName,
                                relationshipName: $reverseRelationshipName,
                                arguments: [
                                    "\\" . $targetModelClass . "::class",
                                    "\\".$intermediateModelClass ."::class",
                                    "'{$this->getSnakeCase($modelName)}_id'",
                                    "'{$this->getSnakeCase($intermediateName)}_id'",
                                    "'id'",
                                    "'id'",
                                ]
                            ) + [ 'model_class' => $targetModelClass];

                        break;
                        case 'hasOneThrough':
                            $methodName = $this->getSingular($this->getCamelCase($this->model));
                            [$modelName, $intermediateName, $localKey, $secondLocalKey, $firstKey, $secondKey] = array_slice(explode(':', $schema),1);

                            $targetModelClass = $this->getModelClass($this->model, $relationshipName);
                            $intermediateModelClass = $this->getModelClass($intermediateName, $relationshipName);


                            $data[] = $this->relationshipFormat(
                                modelName: $modelName,
                                methodName: $methodName,
                                relationshipName: $reverseRelationshipName,
                                arguments: [
                                    "\\".$targetModelClass."::class",
                                    "\\".$intermediateModelClass."::class",
                                    "'id'",
                                    "'id'",
                                    "'{$this->getSnakeCase($intermediateName)}_id'",
                                    "'{$this->getSnakeCase($this->model)}_id'",
                                ]
                            ) + [ 'model_class' => $targetModelClass];
                        break;
                        case 'morphMany':
                        case 'morphOne':
                            $relatedMethodName = $this->getRelatedMethodName($reverseRelationshipName, $schema);
                            $parentModelClass = $this->getModelClass($this->model);

                            $nameArgument = snakeCase(singularize($relatedMethodName));
                            if($parentModelClass){
                                foreach ($props as $key => $targetName) {
                                    // $targetName = $this->getRelatedMethodName($relationshipName, $schema);
                                    $targetModelName = $this->getStudlyName(singularize($targetName));
                                    $targetModelClass = $this->getModelClass($targetModelName, $relationshipName);

                                    $reverseSchema = "{$reverseRelationshipName}:{$this->getStudlyName($this->model)}:{$nameArgument}";
                                    $arguments = $this->getRelationshipArguments($reverseRelationshipName, $reverseSchema);

                                    $data[] = ['model_class' => $targetModelClass]
                                        + $this->relationshipFormat($targetModelName, $relatedMethodName, $reverseRelationshipName, $arguments);
                                }
                            }

                        break;
                        case 'morphedByMany':
                            $targetName = $this->getRelatedMethodName($relationshipName, $schema);
                            $targetModelName = $this->getStudlyName( singularize($targetName) );

                            $targetModelClass = $this->getModelClass($targetModelName, $relationshipName);

                            $nameArgument = snakeCase($targetName);
                            $reverseSchema = "{$reverseRelationshipName}:{$this->getStudlyName($this->model)}:{$nameArgument}";
                            $relationshipName = $reverseRelationshipName;
                            $relatedMethodName = $this->getRelatedMethodName($relationshipName, $reverseSchema);
                            $arguments = $this->getRelationshipArguments($relationshipName, $reverseSchema);
                            $chainMethods = [];

                            $data[] = ['model_class' => $targetModelClass]
                                + $this->relationshipFormat($targetName, $relatedMethodName, $reverseRelationshipName, $arguments, $chainMethods);

                        break;
                        case 'belongsToMany':
                            // $targetName = $this->getRelatedMethodName($relationshipName, $schema);
                            $targetName = $this->getSingular($this->getRelatedMethodName($relationshipName, $schema));
                            $targetModelName = $this->getStudlyName(singularize($targetName));
                            $targetModelClass = $this->getModelClass($targetModelName, $relationshipName);

                            $chainMethods = [];
                            // dd(
                            //     $schemas,
                            //     $schema,
                            //     $schematic,
                            //     $relationship
                            // );
                            if(count($schemas) > 1 && $relationshipName == 'belongsToMany'){
                                // dd($this->model, $methodName, $relationshipName, $arguments);
                                $pivotTableName = $this->getPivotTableName($this->model, singularize($targetModelName));
                                $pivotTableClass = UFinder::getModel($pivotTableName);
                                // dd($pivotTableClass, $pivotTableName);
                                if($pivotTableClass){
                                    $chainMethods[] = [
                                        'method_name' => 'using',
                                        'arguments' => [
                                            "\\" . $pivotTableClass . "::class"
                                        ]
                                    ];
                                    $chainMethods[] = [
                                        'method_name' => 'withPivot',
                                        'arguments' => []
                                    ];
                                }
                            }

                            $reverseSchema = "{$reverseRelationshipName}:{$this->getStudlyName($this->model)}";
                            $relationshipName = $reverseRelationshipName;
                            $relatedMethodName = $this->getRelatedMethodName($reverseRelationshipName, $reverseSchema);
                            $arguments = $this->getRelationshipArguments($reverseRelationshipName, $reverseSchema);
                            // $methodName = $this->getPlural($this->getCamelCase($this->model));
                            $lastModel = $targetModelName;

                            $data[] = ['model_class' => $targetModelClass]
                                + $this->relationshipFormat($targetModelName, $relatedMethodName, $reverseRelationshipName, $arguments, $chainMethods);

                            $lastIndex = count($data) - 1;

                        break;
                        default:
                            $targetName = $this->getRelatedMethodName($relationshipName, $schema);
                            $targetModelName = $this->getStudlyName(singularize($targetName));

                            $targetModelClass = $this->getModelClass($targetModelName, $relationshipName);

                            // dd($this->modelClasses, $targetName, $targetModelName, $relationshipName, $reverseRelationshipName);
                            $reverseSchema = "{$reverseRelationshipName}:{$this->getStudlyName($this->model)}";
                            $relationshipName = $reverseRelationshipName;
                            $relatedMethodName = $this->getRelatedMethodName($relationshipName, $reverseSchema);
                            $arguments = $this->getRelationshipArguments($relationshipName, $reverseSchema);
                            $chainMethods = [];
                            $data[] = $this->relationshipFormat($targetModelName, $relatedMethodName, $reverseRelationshipName, $arguments, $chainMethods) + ['model_class' => $targetModelClass];

                        break;
                    }
                }

            }else{ // for pivot chaining
                // dd(
                //     $schemas,
                //     $data,
                //     $index,
                // );
                if($data[$lastModel]['relationship_method'] == 'belongsToMany' && count($data[$lastModel]['chain_methods']) > 0){
                    $chainIndex = count($data[$lastModel]['chain_methods'])-1;
                    $data[$lastIndex]['chain_methods'][$chainIndex]['arguments'][] = "'{$this->getMethodName($schema)}'";
                }
            }
        }

        return $data;
    }

    public function relationshipFormat($modelName, $methodName, $relationshipName, $arguments, $chainMethods = []) :mixed
    {
        $relationshipType = $relationshipName;

        if($relationshipType == 'morphedByMany') $relationshipType = 'morphToMany';

        return [
            'model_name' => $this->getStudlyName( singularize($modelName) ),
            'relationship_name'  => $methodName,
            'relationship_method'    => $relationshipName,
            'return_type' => "\Illuminate\Database\Eloquent\Relations\\{$this->getStudlyName($relationshipType)}",
            'arguments'=> $arguments,
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
        $parameters = $this->relationshipParametersMap[$relationshipName];

        switch ($relationshipName) {
            case 'morphTo':
                $relatedMethodName = $this->getMorphToMethodName($this->model);
                break;
            case 'morphOne':
                $relatedMethodName = $this->getSingular($this->getCamelCase($this->model));
                break;
            case 'morphMany':
                // $relatedMethodName = $this->getPlural($arguments[$position]);
                $relatedMethodName = $this->getPlural($this->getCamelCase($this->model));
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
            case 'morphToMany':
                $position = $parameters['related']['position'];

                $relatedMethodName = $this->getPlural($arguments[$position]);

                break;
            case 'morphedByMany':
                $position = $parameters['related']['position'];
                $relatedMethodName = $this->getPlural($arguments[$position]);

                break;
            default:
                # code...
                break;
        }

        return $this->getCamelCase($relatedMethodName);



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
        $parameters = array_filter($this->relationshipParametersMap[$relationshipName], fn($ar) => $ar['position'] == $index);
        $parameterName = array_keys($parameters)[0];
        $parameter = $parameters[$parameterName];

        $formattedArgument =  "'{$argument}'";

        switch ($parameterName) {
            case 'related':
                $related = $this->getModelClass($argument, $relationshipName);
                $formattedArgument = "\\" . $related . "::class";

                break;
            case 'through':
                $through = $this->getModelClass($argument, $relationshipName);
                $formattedArgument = "\\" . $through . "::class";

                break;
            case 'name':
                switch ($relationshipName) {
                    case 'morphTo':
                        $formattedArgument = '__FUNCTION__';
                        break;
                    case 'morphToMany':
                        $formattedArgument = "'{$argument}'";
                        break;
                    case 'morphedByMany':
                    case 'morphMany':
                    case 'morphOne':
                        $argument = makeMorphName($argument);
                        $formattedArgument = "'{$argument}'";
                        break;

                    default:
                        dd(
                            __FUNCTION__,
                            $index,
                            $relationshipName,
                            $argument,
                        );
                        break;
                }

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
            case 'morphMany':
                $comment =  $this->commentStructure(["Get the all of the {$model}'s {$attr['relationship_name']}"]);
                break;
            case 'morphOne':
                $comment =  $this->commentStructure(["Get the {$model}'s {$attr['relationship_name']}"]);
                break;
            case 'morphToMany':
                $comment =  $this->commentStructure(["Get the all of {$attr['relationship_name']} for the {$model}"]);
                break;
            case 'morphedByMany':
                $comment =  $this->commentStructure(["Get the all of {$attr['relationship_name']} that are assigned the {$model}"]);
                break;
            case 'hasManyThrough':
                $comment = $this->commentStructure(["The {$attr['relationship_name']} that belong to the {$model}."]);
                break;
            case 'hasOneThrough':
                $comment = $this->commentStructure(["The {$attr['relationship_name']} that owns the {$model}."]);
                break;
            default:
                dd(
                    __FUNCTION__,
                    $attr['relationship_method']
                );

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

    public function getModelClass(string $str, $relationship = null)
    {
        $possibles = array_unique(array_merge(UFinder::getPossibleModels($str),Modularity::getModels($str)), SORT_REGULAR);

        $class = $this->getStudlyName($str);

        if($relationship && isset($this->modelClasses[$relationship]) && isset($this->modelClasses[$relationship][$str])){
            $class = $this->modelClasses[$relationship][$str];
        }else if(count($possibles) == 1){
            $class = array_shift($possibles);
            $this->modelClasses[$relationship][$str] = $class;
        }else if( count($possibles) > 1 ){
            $class = select(
                label: "We found multiple classes for the {$str} model, please select a model from possible model classes",
                options: $possibles
            );
            $this->modelClasses[$relationship][$str] = $class;
        }

        return $class;
    }

}
