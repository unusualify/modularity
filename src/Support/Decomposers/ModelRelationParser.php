<?php

namespace Unusualify\Modularity\Support\Decomposers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Unusualify\Modularity\Facades\UFinder;
use Unusualify\Modularity\Support\Finder;
use Unusualify\Modularity\Traits\ManageNames;
use Unusualify\Modularity\Traits\RelationshipMap;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;

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
        'belongsTo' => ['table', 'foreign_key', 'owner_key'],
        'hasOne' => ['table', 'foreign_key', 'local_key'],
        'hasMany' => ['table', 'foreign_key', 'local_key'],
        'hasOneThrough' => ['table', 'table', 'foreign_key', 'foreign_key', 'local_key', 'local_key'],
        'hasManyThrough' => ['table', 'table', 'foreign_key', 'foreign_key', 'local_key', 'local_key'],
        'belongsToMany' => ['table', 'table', 'foreign_pivot_key', 'related_pivot_key', 'parent_key', 'related_key', 'relation'],
        'morphTo' => ['name', 'type', 'id', 'owner_key'],
    ];

    protected $_argumentsMap = [
        'hasOne' => ['related', 'foreignKey', 'localKey'],
        'hasOneThrough' => ['related', 'through', 'firstKey', 'secondKey', 'localKey', 'secondLocalKey'],
        'morphOne' => ['related', 'name', 'type', 'id', 'localKey'],
        'belongsTo' => ['related', 'foreignKey', 'ownerKey', 'relation'],
        'morphTo' => ['name', 'type', 'id', 'ownerKey'],
        'morphEagerTo' => ['name', 'type', 'id', 'ownerKey'],
        'morphInstanceTo' => ['name', 'type', 'id', 'ownerKey'],
        'hasMany' => ['table', 'foreignKey', 'localKey'],
        'hasManyThrough' => ['related', 'throught', 'firstKey', 'secondKey', 'localKey', 'secondLocalKey'],
        'morphMany' => ['related', 'name', 'type', 'id', 'localKey'],
        'belongsToMany' => ['related', 'table', 'foreignPivotKey', 'relatedPivotKey', 'parentKey', 'relatedKey', 'relation'],
        'morphToMany' => ['related', 'name', 'table', 'foreignPivotKey', 'relatedPivotKey', 'parentKey', 'relatedKey', 'relation', 'inverse'],
        'morphedByMany' => ['related', 'name', 'table', 'foreignPivotKey', 'relatedPivotKey', 'parentKey', 'relatedKey', 'relation'],
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

        $this->relationshipParametersMap = modularityConfig('laravel-relationship-map', []);
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

    public function parse($relations)
    {
        $parsed = [];

        foreach ($this->getRelationships() as $relationship) {
            ($resp = $this->parseRelationshipSchema($relationship)) ? array_push($parsed, $resp) : null;
        }

        return $parsed;

    }

    public function getReverseRelationships(bool $test = false)
    {
        $parsed = [];

        foreach ($this->getRelationships() as $relationship) {
            if (preg_match('/,/', $relationship)) {
                // dump($relationship);
            }
            if (($resp = $this->parseReverseRelationshipSchema($relationship, $test))) {
                $parsed = array_merge($parsed, $resp);
            }
        }

        return $parsed;
    }

    public function writeReverseRelationships(bool $test = false)
    {
        foreach ($this->getReverseRelationships($test) as $format) {
            $modelClass = $format['model_class'];
            // $repository = UFinder::getRouteRepository($modelName, asClass: true);
            // if(!$repository)
            //     continue;

            // $modelClass = $repository->getModel();
            if (! @class_exists($modelClass)) {
                continue;
            }

            $reflector = new \ReflectionClass($modelClass);

            // search ModuleRoute Relationships trait
            // foreach ($reflector->getTraits() as $traitNamespace => $trait) {
            //     if($trait->getShortName() == $reflector->getShortName() . 'Relationships'){
            //         $reflector = new \ReflectionClass($traitNamespace);
            //         break;
            //     }
            // }
            // dd(
            //     $format,
            //     $this->renderFormat($format)
            // );
            if (! $reflector->hasMethod($format['relationship_name'])) {

                $filePath = $reflector->getFileName();

                $content = get_file_string($filePath);

                $pattern = "/(\})[^\}]*$/";

                $newContent = preg_replace($pattern, $this->renderFormat($format) . "\n}\n", $content);
                $runnable = (! $test || confirm(label: "Do you want to see  content of {$format['model_name']} in result of writing reverse relationship in the test mode?", default: false));

                if ($runnable) {
                    if ($test) {
                        info($newContent);
                    } else {
                        app('files')->put($filePath, $newContent);
                    }
                }
            }
        }
    }

    public function hasCreatablePivotModel()
    {
        $creatable = false;

        foreach ($this->getRelationships() as $relationship) {
            // $schemas = explode(',', $relation);
            // if( count($schemas) > 1){
            //     $creatable = true;
            //     break;
            // }
            $schemas = explode(',', $relationship);
            $relationship_name = explode(':', $schemas[0])[0];

            if (in_array($relationship_name, $this->pivotableRelationships) && count($schemas) > 1) {
                $creatable = true;

                break;
            }
        }

        return $creatable;
    }

    public function getPivotModels()
    {
        $models = [];

        foreach ($this->getRelationships() as $relationship) {
            // dd($relation);
            $schemas = explode(',', $relationship);

            $relationship_name = explode(':', $schemas[0])[0];
            if (count($schemas) > 1 && in_array($relationship_name, $this->pivotableRelationships)) {
                foreach ($schemas as $index => $schema) {
                    if (! $index) { // relation_type:table_name
                        $relationshipName = $this->getRelationshipName($schema);
                        $relatedMethodName = $this->getRelatedMethodName($relationshipName, $schema);

                        $models[] = [
                            'class' => $this->getPivotModelName($relatedMethodName),
                            'fillables' => [
                                makeForeignKey($this->model),
                                makeForeignKey($relatedMethodName),
                            ],
                            'casts' => [],
                        ];
                    } else { // other fields
                        $explodes = explode(':', $schema);
                        $field = Arr::get($explodes, 0);

                        $models[count($models) - 1]['fillables'][] = $field;
                        if (count($explodes) > 1) {
                            $type = Arr::get($explodes, 1);
                            $models[count($models) - 1]['casts'][$field] = $this->castFieldType($type);
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

    public function castFieldType($type)
    {
        $casted = $type;

        $castings = [
            'boolean' => 'string',
        ];

        if (array_key_exists($type, $castings)) {
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
        }

        return $methods;
    }

    public function renderFormat($attr): string
    {

        $args = implode(', ', array_map(function ($v) {
            return "{$v}";
        }, $attr['arguments']));

        $comment = $this->generateMethodComment($attr);

        $method_chain = "\$this->{$attr['relationship_method']}({$args})";

        if (count($attr['chain_methods'])) {
            foreach ($attr['chain_methods'] as $key => $chain) {
                $chain_args = implode(', ', array_map(function ($v) {
                    return "{$v}";
                }, $chain['arguments']));
                $method_chain .= "\n\t\t\t->{$chain['method_name']}({$chain_args})";
                // code...
            }
        }
        $method_chain .= ';';

        return $comment . "\n\tpublic function {$attr['relationship_name']}() : {$attr['return_type']}\n\t{\n\t\treturn {$method_chain}\n\t}\n";
    }

    public function getPivotModelName($relatedModel)
    {
        return $this->model . $this->getStudlyName($this->getSingular($relatedModel));
    }

    public function findModel_($table)
    {
        return '\\' . (new Finder)->getModel($table) . '::class';
    }
}
