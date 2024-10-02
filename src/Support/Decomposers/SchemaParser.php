<?php

namespace Unusualify\Modularity\Support\Decomposers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Nwidart\Modules\Support\Migrations\SchemaParser as Parser;
use Unusualify\Modularity\Facades\UFinder;
use Unusualify\Modularity\Support\Finder;
use Unusualify\Modularity\Traits\ManageNames;
use Unusualify\Modularity\Traits\RelationshipMap;

class SchemaParser extends Parser
{
    use ManageNames, RelationshipMap;

    /**
     * Starting header formats to add to headerFormats
     *
     * @var array
     */
    protected $defaultPreHeaders = [];

    /**
     * End header formats to add to headerFormats
     *
     * @var array
     */
    protected $defaultPostHeaders = [];

    /**
     * default header format for ui table header
     *
     * @var array
     */
    protected $defaultHeaderFormat = [];

    /**
     * defaultInputs
     *
     * @var array
     */
    protected $defaultInputs = [];

    protected $baseNamespace;

    protected $traitNamespaces = [
        'soft_delete' => 'Illuminate\Database\Eloquent\SoftDeletes',
        'has_factory' => 'Illuminate\Database\Eloquent\Factories\HasFactory',
        'model_helpers' => 'Unusualify\Modularity\Entities\Traits\ModelHelpers',
    ];

    protected $traits = [
        'soft_delete' => 'SoftDeletes',
        'has_factory' => 'HasFactory',
        'model_helpers' => 'ModelHelpers',
    ];

    protected $repositoryTraits = [];

    protected $repositoryTraitNamespaces = [];

    protected $interfaces = [];

    protected $interfaceNamespaces = [];

    protected $useDefaults = false;

    protected $chainableMethods = [
        'string',
        'integer',
        'boolean',
        // 'json'
    ];

    protected $exceptHeaderMethods = [
        'json',
    ];

    /**
     * Create new instance.
     *
     * @param string|null $schema
     */
    public function __construct($schema = null, $useDefaults = true, $model = null)
    {
        parent::__construct($schema);

        $this->useDefaults = $useDefaults;

        $this->relationshipParametersMap = unusualConfig('laravel-relationship-map', []);
        $this->model = $model;

        if ($this->useDefaults) {
            $this->defaultInputs = unusualConfig('schemas.default_inputs', []);
            $this->defaultPreHeaders = unusualConfig('schemas.default_pre_headers', []);
        }

        $this->defaultPostHeaders = unusualConfig('schemas.default_post_headers', []);

        $this->defaultHeaderFormat = unusualConfig('default_header', []);

        // $this->baseNamespace = Config::get(unusualBaseKey() . '.namespace')."\\".Config::get(unusualBaseKey() . '.name');
        $this->baseNamespace = unusualConfig('namespace');

        $traits = unusualConfig('traits', []);

        foreach ($traits as $key => $object) {
            $modelTrait = $object['model'];
            if (@trait_exists($modelTrait)) {
                $this->traits[$key] = get_class_short_name($modelTrait);
                $this->traitNamespaces[$key] = $modelTrait;
            } else {
                $this->traits[$key] = $modelTrait;
                $this->traitNamespaces[$key] = "{$this->baseNamespace}\\Entities\\Traits\\{$modelTrait}";
            }

            $this->repositoryTraits[$key] = isset($object['repository']) ? $object['repository'] : '';
            $this->repositoryTraitNamespaces[$key] = isset($object['repository']) ? "{$this->baseNamespace}\\Repositories\\Traits\\{$object['repository']}" : '';

            if (array_key_exists('implementations', $object)) {
                $this->interfaces[$key] = Collection::make($object['implementations'])->map(function ($interface) {
                    return (new \ReflectionClass($interface))->getShortName();
                })->toArray();
                $this->interfaceNamespaces[$key] = Collection::make($object['implementations'])->map(function ($interface) {
                    return (new \ReflectionClass($interface))->getName();
                })->toArray();
            } else {
                $this->interfaces[$key] = [];
                $this->interfaceNamespaces[$key] = [];
            }
        }
        $this->relationshipKeys[] = 'belongsToMany';
        $this->relationshipKeys[] = 'hasOne';
        $this->relationshipKeys[] = 'hasMany';
        $this->relationshipKeys[] = 'morphTo';
        $this->relationshipKeys[] = 'morphOne';
        // dd($this->relationshipKeys);
    }

    // /**
    //  * Get array of schema.
    //  *
    //  * @return array
    //  */
    // public function getSchemas()
    // {
    //     if (is_null($this->schema)) {
    //         return [];
    //     }

    //     return preg_split('/(?<!\\\),/',str_replace(' ', '', $this->schema) );
    // }

    /**
     * getColumns
     *
     * @param mixed $schema
     */
    public function getColumns($schema = null): array
    {
        if ((bool) $schema) {
            $this->schema = $schema;
        }

        $parsed = [];

        foreach ($this->getSchemas() as $schema) {
            $column = $this->getColumn($schema);
            $column_type = $this->getColumnType($schema);

            if (in_array($column_type, $this->relationshipKeys)) {
                if (preg_match('/belongsToMany|hasMany|morphOne/', $column_type)) {
                    continue;
                }

                if (preg_match('/morphTo/', $column_type)) {
                    $morphable_id = makeMorphForeignKey($column);
                    $morphable_type = makeMorphForeignType($column);
                    $parsed[] = $morphable_id;
                    $parsed[] = $morphable_type;

                    continue;
                }

                $column = "{$this->getForeignKeyFromName($column)}";
            }

            $parsed[] = $column;
        }

        return $parsed;
    }

    public function getColumnTypes($schema = null): array
    {
        if ((bool) $schema) {
            $this->schema = $schema;
        }

        $parsed = [];

        foreach ($this->getSchemas() as $schema) {
            $column = $this->getColumn($schema);
            $column_type = $this->getColumnType($schema);

            $parsed[$column] = $column_type;
        }

        return $parsed;
    }

    /**
     * getFillables
     *
     * @param mixed $schema
     */
    public function getFillables($schema = null): array
    {
        return array_filter($this->getColumns(), function ($v) {
            return ! array_key_exists($v, $this->customAttributes);
        });
    }

    public function getColumnType($schema)
    {
        return Arr::get(explode(':', $schema), 1);
    }

    /**
     * Get relationships string.
     *
     * method:table_names:foreign_key:owner_key
     *
     * @return array
     */
    public function getRelationships($schema = null)
    {
        $relationships = [];

        $parsed = $this->parse($this->schema);
        $methodChaining = false;

        foreach ($parsed as $col_name => $methods) {
            $methodName = $this->getCamelCase($methods[0]); // belongsTo, belongsToMany,....
            if ($methodChaining && in_array($methods[0], $this->chainableMethods)) {
                $relationships[count($relationships) - 1] .= ",{$col_name}:{$methods[0]}";
            } elseif (array_key_exists($methodName, $this->relationshipParametersMap)) {
                $methodChaining = false;
                $relatedName = $this->getStudlyName($col_name);
                $arguments = $methods;
                array_shift($arguments);

                // if($methodName == 'morphTo'){
                //     dd(
                //         $relatedName,
                //         $arguments
                //     );
                // }
                $relationships[] = $this->createRelationshipSchema($relatedName, $methodName, $arguments);

                if ($methodName == 'belongsToMany') {
                    $methodChaining = true;
                }

            } else {

                foreach ($methods as $i => $method) {
                    switch ($method) {
                        case 'foreignId':
                        case preg_match('/(?<=foreignId[For])/', $method, $matches) ? true : false:

                            // $table_name = $this->getDBTableName(preg_replace('/_id/', '', $col_name));
                            if (! preg_match('/(\w)+_id$/', $col_name, $matches)) {
                                $col_name = makeForeignKey($col_name);
                            }
                            $relatedName = $this->getStudlyName(preg_replace('/_id/', '', $col_name));
                            // $relationships[] = "belongsTo:{$table_name}:{$col_name}:id";
                            $relationships[] = "belongsTo:{$relatedName}:{$col_name}:id";

                            break;
                        case preg_match('/(?<=foreign\(\').*?(?=\'\))/', $method, $matches) ? true : false:
                            $foreign_key = $matches[0];
                            $owner_key = '';
                            $table_name = '';
                            dd($foreign_key);
                            foreach ($methods as $i => $_method) {
                                if (preg_match('/(?<=on\(\').*?(?=\'\))/', $_method, $matches)) {
                                    $table_name = $matches[0];

                                    break;
                                }
                            }

                            if ($table_name === '') {
                                break;
                            }

                            foreach ($methods as $i => $_method) {
                                if (preg_match('/(?<=references\(\').*?(?=\'\))/', $_method, $matches)) {
                                    $owner_key = $matches[0];

                                    break;
                                }
                            }

                            if ($owner_key === '') {
                                break;
                            }

                            $relationships[] = "belongsTo:{$table_name}:{$foreign_key}:{$owner_key}";

                            break;

                        default:
                            // code...
                            break;
                    }
                }

            }
        }

        return $relationships;
    }

    /**
     * headerFormat
     *
     * @param mixed $column
     */
    public function headerFormat(string $column_name, $options = []): array
    {
        if (in_array($options[0], ['json'])) {
            return [];
        }

        $title = $this->getHeadline($column_name);

        if (in_array($options[0], ['morphTo'])) {
            $title = $this->getHeadline($column_name) . ' Parent';
            $column_name = $this->getCamelCase($column_name) . 'able';
        } elseif (in_array($options[0], $this->relationshipKeys)) {
            $column_name = $this->getCamelCase($column_name);
        }

        return [
            'title' => $title,
            'key' => $column_name,
            // 'align' => 'start',
            // 'sortable' => false,
            // 'filterable' => false,
            // 'groupable' => false,
            // 'divider' => false,
            // 'class' => '', // || []
            // 'cellClass' => '', // || []
            // 'width' => '', // || int
            // vuetify datatable header fields end

            // custom fields for ue-datatable start
            // 'searchable' => !in_array($options[0], $this->relationshipKeys), //true,
            // 'isRowEditable' => false,
            // 'isColumnEditable' => false,
            // 'formatter' =>  $options[0] == 'timestamp' ? ['date', 'long'] : [],
        ]
        + ($options[0] == 'timestamp' ? ['formatter' => ['date', 'long']] : [])
        + (! in_array($options[0], $this->relationshipKeys) ? ['searchable' => true] : []);
    }

    public function getHeaderFormats(): array
    {

        $filter = array_filter($this->parse($this->schema), function ($v, $k) {
            return ! (array_key_exists($k, $this->customAttributes) || in_array($v[0], $this->exceptHeaderMethods));
        }, ARRAY_FILTER_USE_BOTH);

        return array_merge(
            array_merge($this->defaultPreHeaders, array_map(function ($k, $options) {
                if (in_array($k, $this->relationshipKeys)) {
                    // $options[0] => 'relation_name'
                    return $this->headerFormat($options[0], [$k]);
                }

                return $this->headerFormat($k, $options);
            }, array_keys($filter), $filter)),
            $this->defaultPostHeaders
        );
    }

    /**
     * inputFormat
     *
     * @param mixed $column
     */
    public function inputFormat(string $column, $options = []): array
    {
        $extra_options = [];
        $type = 'text';
        $name = $column;
        $label = $this->getHeadline($column);
        $rules = [];

        switch ($options[0]) {
            case 'timestamp':
                $extra_options['ext'] = 'date';

                break;
            case 'time':
                $extra_options['ext'] = 'time';

                break;
            case 'json':
                $type = 'group';
                $extra_options['schema'] = [];

                break;
            case in_array($options[0], ['text', 'mediumtext', 'longtext']) ? true : false:
                $type = 'textarea';

                break;

            default:
                // code...
                break;
        }

        if ($options[0] == 'timestamp') {
            $extra_options['ext'] = 'date';
        } elseif ($options[0] == 'time') {
            $extra_options['ext'] = 'time';
        } elseif (in_array($options[0], ['text', 'mediumtext', 'longtext'])) {
            $type = 'textarea';
        } elseif ($options[0] == 'json') {
            $type = 'group';
            $extra_options['schema'] = [];
        }

        if (count($options) > 1) {

            foreach ($options as $option) {
                // default value perception from options
                if (preg_match('/default\(\'?([A-Za-z\d]+)\'?\)/', $option, $matches)) {
                    $extra_options['default'] = $matches[1];
                }
            }
        }

        if (! in_array('nullable', $options) && ! in_array($type, ['json'])) {
            $rules[] = 'required';
        }

        if (in_array($options[0], $this->relationshipKeys)) {
            if ($options[0] == 'belongsTo') {
                $type = 'select';
                // $name .= '_id';
                $name = $this->getForeignKeyFromName($name);

                $extra_options['repository'] = UFinder::getRouteRepository($column);
            } elseif ($options[0] == 'hasMany') {
                $type = 'checklist';
                $name = pluralize($name);
                $label = pluralize($label);

                $extra_options['repository'] = UFinder::getRouteRepository($column);
            } elseif ($options[0] == 'morphTo') {
                $type = 'morphTo';
                $finder = new Finder;
                $parents = [];

                foreach (array_slice($options, 1) as $key => $routeName) {
                    $routeName = $this->getStudlyName($routeName);
                    $foreign_id = $this->getForeignKeyFromName($routeName); //$this->getSnakeCase($routeName) . '_id';
                    if (($repository = $finder->getRouteRepository($routeName))) {
                        array_push($parents, [
                            'name' => $foreign_id,
                            'label' => $this->getHeadline($routeName),
                            'type' => 'select',
                            'repository' => $repository,
                        ]);
                    }
                }
                $extra_options['schema'] = $parents;
            }
        }

        if (count($rules) > 0) {
            array_unshift($rules, 'sometimes');
            $extra_options['rules'] = implode('|', $rules);
        }

        return [
            'type' => $type,
            'name' => $name,
            'label' => $label,
            ...$extra_options,
            // 'placeholder' => "{$this->getHeadline($column)} Value",

            // 'hint' => '',
            // 'default' => '',
            // 'col' => [
            //     'cols' => 12,
            //     'sm' => 12,
            //     'md' => 12,
            //     'lg' => 6,
            //     'xl' => 4
            // ]
        ];
    }

    /**
     * getInputFormats
     */
    public function getInputFormats(): array
    {
        $filter = array_filter($this->parse($this->schema), function ($v, $k) {
            return ! array_key_exists($k, $this->customAttributes);
        }, ARRAY_FILTER_USE_BOTH);

        return array_merge($this->defaultInputs, array_map(function ($k, $options) {
            if (in_array($k, $this->relationshipKeys)) {
                // $options[0] => 'relation_name'
                return $this->inputFormat($options[0], [$k]);
            }

            return $this->inputFormat($k, $options);
        }, array_keys($filter), $filter));
    }

    /**
     * getTraitNamespace
     *
     * @param mixed $string
     */
    public function getTraitNamespace($string): string
    {
        return $this->traitNamespaces[$string];
    }

    /**
     * getRepositoryTraitNamespace
     *
     * @param mixed $string
     */
    public function getRepositoryTraitNamespace($string): string
    {
        return $this->repositoryTraitNamespaces[$string];
    }

    /**
     * getInterfaceNamespace
     *
     * @param mixed $string
     */
    public function getInterfaceNamespaces($string): array
    {
        return $this->interfaceNamespaces[$string];
    }

    /**
     * getTrait
     *
     * @param mixed $string
     */
    public function getTrait($string): string
    {
        return $this->traits[$string];
    }

    /**
     * getRepositoryTrait
     *
     * @param mixed $string
     */
    public function getRepositoryTrait($string): string
    {
        return $this->repositoryTraits[$string];
    }

    public function getInterfaces($string): array
    {
        return $this->interfaces[$string];

    }

    /**
     * hasSoftDelete
     */
    public function hasSoftDelete(): bool
    {
        return in_array('soft_delete', $this->getColumns());
    }
}
