<?php

namespace Unusual\CRM\Base\Support\Decomposers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Nwidart\Modules\Support\Migrations\SchemaParser as Parser;
use Illuminate\Support\Str;
use Unusual\CRM\Base\Traits\Namable;
use phpDocumentor\Reflection\Types\Boolean;
use PhpParser\Node\Expr\FuncCall;

class SchemaParser extends Parser
{
    use Namable;

    /**
     * defaultHeaders
     *
     * @var array
     */
    protected $defaultHeaders = [
        [
            'text' => 'Created Time',
            'value' => 'created_at',
            'formatter' => 'formatDate',
            'searchable' => true
        ],
        [
            'text' => 'Update Time',
            'value' => 'updated_at',
            'formatter' => 'formatDate',
            'searchable' => true
        ],
        [
            'text' => 'Actions',
            'value' => 'actions',
            'sortable' => false
        ]
    ];

    /**
     * defaultInputs
     *
     * @var array
     */
    protected $defaultInputs = [
        [
            'title' => 'Name',
            'name' => 'name',
            'type' => 'text',
            'placeholder' => '',
            'cols' => 12,
            'sm' => 12,
            'md' => 8
        ]
    ];

    protected $baseModelNamespace;

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

    /**
     * Create new instance.
     *
     * @param string|null $schema
     */
    public function __construct($schema = null)
    {
        parent::__construct($schema);

        $this->baseNamespace = Config::get('base.namespace')."\\".Config::get('base.name');

        $this->traits += Collection::make(Config::get('base.traits',[]))->mapWithKeys(function($object, $key){
            return [ $key => $object['model']];
        })->toArray();

        $this->traitNamespaces += Collection::make(Config::get('base.traits',[]))->mapWithKeys(function($object, $key){
            return [ $key => "{$this->baseNamespace}\\Entities\\Traits\\{$object['model']}"];
        })->toArray();

        $this->repositoryTraits += Collection::make(Config::get('base.traits',[]))->mapWithKeys(function($object, $key){
            return [ $key => isset($object['repository']) ? $object['repository'] : ''];
        })->toArray();

        $this->repositoryTraitNamespaces += Collection::make(Config::get('base.traits',[]))->mapWithKeys(function($object, $key){
            return [ $key => isset($object['repository']) ?  "{$this->baseNamespace}\\Repositories\\Traits\\{$object['repository']}" : ''];
        })->toArray();

        $this->interfaces += Collection::make(Config::get('base.traits',[]))->mapWithKeys(function($object, $key){

            return array_key_exists('implementations', $object) ? [
                $key =>Collection::make($object['implementations'])->map(function($interface){
                    return (new \ReflectionClass($interface))->getShortName();
                })
            ] : [ $key => [] ];
        })->toArray();

        $this->interfaceNamespaces += Collection::make(Config::get('base.traits',[]))->mapWithKeys(function($object, $key){
            return array_key_exists('implementations', $object) ? [
                $key => Collection::make($object['implementations'])->map(function($interface){
                    return (new \ReflectionClass($interface))->getName();
                })
            ] : [ $key => [] ];
        })->toArray();

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

        foreach ($this->getSchemas() as $schemaArray) {

            $schema = explode(':', $schemaArray);

            $column = !in_array($this->getColumn($schemaArray), $this->relationshipKeys) ? $this->getColumn($schemaArray) : $schema[1].'_'.$schema[2];

            $parsed[] = $column;
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

        foreach ($this->parse($this->schema) as $col_name => $methods) {

            if (in_array($col_name, $this->relationshipKeys)) {
                $foreign_key = $methods[0].'_id';
                $owner_key = $methods[1];
                $table_name = $methods[2];
                $relationships[] = "belongsTo:{$table_name}:{$foreign_key}:{$owner_key}";

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
    public function headerFormat(string $column) : array
    {
        return [
            'text' => $this->getHeadline($column),
            'value' => $column,
            'align' => 'start',
            'sortable' => false,
            'searchable' => true,
        ];
    }

    /**
     * getHeaderFormats
     *
     * @return array
     */
    public function getHeaderFormats() : array
    {
        $filter = array_filter($this->parse($this->schema), function($v,$k){
            return !array_key_exists($k, $this->customAttributes);
        }, ARRAY_FILTER_USE_BOTH );

        return array_map(function($v, $k){
            return $this->headerFormat($k);
        }, $filter, array_keys($filter) ) + $this->defaultHeaders;
    }

    /**
     * inputFormat
     *
     * @param  mixed $column
     * @return array
     */
    public function inputFormat(string $column) : array
    {
        return [
            'title' => $this->getHeadline($column),
            'name' => $column,
            'type' => 'text',
            'placeholder' => '',
            'cols' => 12,
            'sm' => 12,
            'md' => 8
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

        return array_map(function($v, $k){
            return $this->inputFormat($k);
        }, $filter, array_keys($filter) ) ?: $this->defaultInputs;
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
