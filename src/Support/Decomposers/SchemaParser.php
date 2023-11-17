<?php

namespace Unusualify\Modularity\Support\Decomposers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Nwidart\Modules\Support\Migrations\SchemaParser as Parser;
use Illuminate\Support\Str;
use Unusualify\Modularity\Support\Finder;
use Unusualify\Modularity\Traits\ManageNames;
use phpDocumentor\Reflection\Types\Boolean;
use PhpParser\Node\Expr\FuncCall;

class SchemaParser extends Parser
{
    use ManageNames;

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
        'has_factory'    => 'Illuminate\Database\Eloquent\Factories\HasFactory',
    ];

    protected $traits = [
        'soft_delete' => 'SoftDeletes',
        'has_factory'    => 'HasFactory',
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

    /**
     * Create new instance.
     *
     * @param string|null $schema
     */
    public function __construct($schema = null, $useDefaults = true)
    {
        parent::__construct($schema);

        $this->useDefaults = $useDefaults;

        if($this->useDefaults){
            $this->defaultInputs = Config::get(unusualBaseKey() . '.schemas.default_inputs',[]);
            $this->defaultPreHeaders = Config::get(unusualBaseKey() . '.schemas.default_pre_headers',[]);
        }

        $this->defaultPostHeaders = Config::get(unusualBaseKey() . '.schemas.default_post_headers',[]);

        $this->defaultHeaderFormat = Config::get(unusualBaseKey() . '.default_header',[]);

        // $this->baseNamespace = Config::get(unusualBaseKey() . '.namespace')."\\".Config::get(unusualBaseKey() . '.name');
        $this->baseNamespace = Config::get(unusualBaseKey() . '.namespace');

        $traits = Config::get(unusualBaseKey() . '.traits',[]);

        foreach ($traits as $key => $object) {
            $this->traits[$key] = $object['model'];
            $this->traitNamespaces[$key] = "{$this->baseNamespace}\\Entities\\Traits\\{$object['model']}";

            $this->repositoryTraits[$key] = isset($object['repository']) ? $object['repository'] : '';
            $this->repositoryTraitNamespaces[$key] = isset($object['repository']) ?  "{$this->baseNamespace}\\Repositories\\Traits\\{$object['repository']}" : '';

            if(array_key_exists('implementations', $object)){
                $this->interfaces[$key] = Collection::make($object['implementations'])->map(function($interface){
                    return (new \ReflectionClass($interface))->getShortName();
                })->toArray();
                $this->interfaceNamespaces[$key] = Collection::make($object['implementations'])->map(function($interface){
                    return (new \ReflectionClass($interface))->getName();
                })->toArray();
            }else{
                $this->interfaces[$key] = [];
                $this->interfaceNamespaces[$key] = [];
            }
        }

        $this->relationshipKeys[] = 'belongsToMany';
        $this->relationshipKeys[] = 'hasMany';
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
     * @param  mixed $schema
     * @return array
     */
    public function getColumns($schema = null) : array
    {
        if(!!$schema){
            $this->schema = $schema;
        }

        $parsed = [];

        foreach ($this->getSchemas() as $schema) {
            $column =  $this->getColumn($schema);
            $column_type = $this->getColumnType($schema);

            if(in_array($column_type, $this->relationshipKeys)){
                if($column_type == 'belongsToMany' || $column_type == 'hasOne')
                    continue;

                $column =  "{$column}_id";
            }

            $parsed[] = $column;
        }

        return $parsed;
    }

    public function getColumnTypes($schema = null) : array
    {
        if(!!$schema){
            $this->schema = $schema;
        }

        $parsed = [];

        foreach ($this->getSchemas() as $schema) {
            $column =  $this->getColumn($schema);
            $column_type = $this->getColumnType($schema);

            $parsed[$column] = $column_type;
        }

        return $parsed;
    }

    /**
     * getFillables
     *
     * @param  mixed $schema
     * @return array
     */
    public function getFillables($schema = null) : array
    {
        return array_filter($this->getColumns(), function($v){
            return !array_key_exists($v, $this->customAttributes);
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

            if($methodChaining && in_array($methods[0], $this->chainableMethods)){
                $relationships[count($relationships)-1] .= ",{$col_name}:{$methods[0]}";
            } else if (in_array($methods[0], $this->relationshipKeys)) {
                $relationship_name = $methods[0];
                if($relationship_name  == 'belongsTo'){
                    $foreign_key = $col_name.'_id';
                    $owner_key = $methods[1] ?? 'id';
                    $table_name = $methods[2] ?? pluralize($col_name);
                    $relationships[] = "belongsTo:{$table_name}:{$foreign_key}:{$owner_key}";
                } else if($relationship_name == 'belongsToMany'){
                    $methodChaining = true;
                    $table_name = $methods[2] ?? pluralize($col_name);
                    $relationships[] = "belongsToMany:{$table_name}";
                }

            } else{

                foreach ($methods as $i => $method) {

                    switch ($method) {
                        case 'foreignId':
                            $table_name = $this->getDBTableName(preg_replace('/_id/', '', $col_name));

                            $relationships[] = "belongsTo:{$table_name}:{$col_name}:id";

                            break;
                        case (preg_match('/(?<=foreign\(\').*?(?=\'\))/', $method, $matches) ? true :false):
                            $foreign_key = $matches[0];
                            $owner_key = '';
                            $table_name = '';

                            foreach ($methods as $i => $_method) {
                                if(preg_match('/(?<=on\(\').*?(?=\'\))/', $_method, $matches)){
                                    $table_name = $matches[0];
                                    break;
                                }
                            }

                            if($table_name === '') break;

                            foreach ($methods as $i => $_method) {
                                if(preg_match('/(?<=references\(\').*?(?=\'\))/', $_method, $matches)){
                                    $owner_key = $matches[0];
                                    break;
                                }
                            }

                            if($owner_key === '') break;

                            $relationships[] = "belongsTo:{$table_name}:{$foreign_key}:{$owner_key}";

                            break;

                        default:
                            # code...
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
     * @param  mixed $column
     * @return array
     */
    public function headerFormat(string $column_name, $options = []) : array
    {
        // dd(
        //     $column_name,
        //     $options,
        //     $this->relationshipKeys
        // );
        if(in_array($options[0], $this->relationshipKeys)){
            $column_name = $this->getCamelCase($column_name);
        }

        return [
            'title' => $this->getHeadline($column_name),
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
        + ( $options[0] == 'timestamp' ? ['formatter' => ['date', 'long']] : [])
        + ( !in_array($options[0], $this->relationshipKeys) ? ['searchable' => true] : []);
    }

    /**
     *
     *
     * @return array
     */
    public function getHeaderFormats() : array
    {

        $filter = array_filter($this->parse($this->schema), function($v,$k){
            return !array_key_exists($k, $this->customAttributes);
        }, ARRAY_FILTER_USE_BOTH );
        // dd(
        //     array_merge(
        //         array_merge($this->defaultPreHeaders, array_map(function($k, $options){
        //             if(in_array($k, $this->relationshipKeys)){
        //                 // $options[0] => 'relation_name'
        //                 return $this->headerFormat($options[0], [$k]);
        //             }
        //             return $this->headerFormat($k, $options);
        //         }, array_keys($filter), $filter  )),
        //         $this->defaultPostHeaders
        //     )
        // );
        return array_merge(
            array_merge($this->defaultPreHeaders, array_map(function($k, $options){
                if(in_array($k, $this->relationshipKeys)){
                    // $options[0] => 'relation_name'
                    return $this->headerFormat($options[0], [$k]);
                }
                return $this->headerFormat($k, $options);
            }, array_keys($filter), $filter  )),
            $this->defaultPostHeaders
        );
    }

    /**
     * inputFormat
     *
     * @param  mixed $column
     * @return array
     */
    public function inputFormat(string $column, $options = []) : array
    {
        $extra_options = [];

        $type = 'text';
        $name = $column;
        $label = $this->getHeadline($column);

        if($options[0] == 'timestamp'){
            $extra_options['ext'] = 'date';
        } else if(in_array($options[0], ['text', 'mediumtext', 'longtext'])){
            $type = 'textarea';
        }

        if(in_array($options[0], $this->relationshipKeys)){
            if($options[0] == 'belongsTo'){
                $type = 'select';
                $name .= '_id';

                $finder = new Finder();
                $extra_options['repository'] = $finder->getRepository(pluralize($column));
            } else if($options[0] == 'hasMany'){
                $type = 'checklist';
                $name = pluralize($name);
                $label = pluralize($label);

                $finder = new Finder();
                $extra_options['repository'] = $finder->getRepository(pluralize($column));
            }
        }

        return [
            'name' => $name,
            'label' => $label,
            'type' => $type,
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
     *
     * @return array
     */
    public function getInputFormats() : array
    {
        $filter = array_filter($this->parse($this->schema), function($v,$k){
            return !array_key_exists($k, $this->customAttributes);
        }, ARRAY_FILTER_USE_BOTH );

        return array_merge( $this->defaultInputs, array_map(function($k, $options){
            if(in_array($k, $this->relationshipKeys)){
                // $options[0] => 'relation_name'
                return $this->inputFormat($options[0], [$k]);
            }
            return $this->inputFormat($k, $options);
        }, array_keys($filter), $filter ));
    }

    /**
     * getTraitNamespace
     *
     * @param  mixed $string
     * @return string
     */
    public function getTraitNamespace($string) : string
    {
        return $this->traitNamespaces[$string];
    }

    /**
     * getRepositoryTraitNamespace
     *
     * @param  mixed $string
     * @return string
     */
    public function getRepositoryTraitNamespace($string) : string
    {
        return $this->repositoryTraitNamespaces[$string];
    }

    /**
     * getInterfaceNamespace
     *
     * @param  mixed $string
     * @return array
     */
    public function getInterfaceNamespaces($string) : array
    {
        return $this->interfaceNamespaces[$string];
    }

    /**
     * getTrait
     *
     * @param  mixed $string
     * @return string
     */
    public function getTrait($string) : string
    {
        return $this->traits[$string];
    }

    /**
     * getRepositoryTrait
     *
     * @param  mixed $string
     * @return string
     */
    public function getRepositoryTrait($string) : string
    {
        return $this->repositoryTraits[$string];
    }

    public function getInterfaces($string) : array
    {
        return $this->interfaces[$string];

    }

    /**
     * hasSoftDelete
     *
     * @return bool
     */
    public function hasSoftDelete() : bool
    {
        return in_array('soft_delete', $this->getColumns());
    }
}
