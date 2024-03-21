<?php

namespace Nwidart\Modules\Support\Migrations;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SchemaParser implements Arrayable
{
    /**
     * The array of custom attributes.
     *
     * @var array
     */
    protected $customAttributes = [
        'remember_token' => 'rememberToken()',
        'soft_delete' => 'softDeletes()',
    ];

    /**
     * The migration schema.
     *
     * format => $columnName1:$attributeName1,$columnName2:$attribute2
     * name:string,
     * company:belongsTo
     * description:text
     * stream_code:string:nullable
     * url:string:nullable
     * start_date:timestamp
     * start_time:time
     * settings:json
     *
     * @var string
     */
    protected $schema;

    /**
     * The relationship keys.
     *
     * @var array
     */
    protected $relationshipKeys = [
        'belongsTo',
    ];

    /**
     * Create new instance.
     *
     * @param string|null $schema
     */
    public function __construct($schema = null)
    {
        $this->schema = $schema;
    }

    /**
     * Parse a string to array of formatted schema.
     *
     * @param string $schema
     *
     * @return array
     */
    public function parse($schema)
    {
        $this->schema = $schema;

        $parsed = [];

        foreach ($this->getSchemas() as $i => $schemaArray) {
            $column = $this->getColumn($schemaArray);
            $attributes = $this->getAttributes($column, $schemaArray);
            $parsed[$column] = $attributes;
        }

        return $parsed;
    }

    /**
     * Get array of schema.
     *
     * @return array
     */
    public function getSchemas()
    {
        if (is_null($this->schema) || empty($this->schema) ) {
            return [];
        }

        return preg_split('/(?<!\\\),/',str_replace(' ', '', $this->schema) );

        return explode(',', str_replace(' ', '', $this->schema));
    }

    /**
     * Convert string migration to array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->parse($this->schema);
    }

    /**
     * Render the migration to formatted script.
     *
     * @return string
     */
    public function render()
    {
        $results = '';

        foreach ($this->toArray() as $column => $attributes) {
            if(in_array($attributes[0], ['morphTo'])){
                $morphable_id = snakeCase($column) . 'able_id';
                $morphable_type = snakeCase($column) . 'able_type';

                $results .= $this->createField($morphable_type, ['string', 'nullable']);
                $results .= $this->createField($morphable_id, ['unsignedBigInteger', 'nullable']);
            }
            else if(!in_array($attributes[0], ['belongsToMany', 'hasOne']))
                $results .= $this->createField($column, $attributes);
        }

        return $results;
    }

    /**
     * Render up migration fields.
     *
     * @return string
     */
    public function up()
    {
        return $this->render();
    }

    /**
     * Render down migration fields.
     *
     * @return string
     */
    public function down()
    {
        $results = '';

        foreach ($this->toArray() as $column => $attributes) {
            $attributes = [head($attributes)];
            $results .= $this->createField($column, $attributes, 'remove');
        }

        return $results;
    }


    /**
     * Create field.
     *
     * @param string $column
     * @param array  $attributes
     * @param string $type
     *
     * @return string
     */
    public function createField($column, $attributes, $type = 'add')
    {
        $results = "\t\t\t" . '$table';

        foreach ($attributes as $key => $field) {
            $type_method = $attributes[0];
            $studly_type = studlyName($type_method);

            if (method_exists($this, "add{$studly_type}Column")) {
                $results .= $this->{"add{$studly_type}Column"}($key, $column, $field);
            }  else {
                $results .= $this->{"{$type}Column"}($key, $field, $column);
            }
        }

        return $results . ';' . PHP_EOL;
    }

    /**
     * Add belongsTo column.
     *
     * @param int    $key
     * @param string $field
     * @param string $column
     *
     * @return string
     */
    protected function addBelongsToColumn($key, $column, $field)
    {
        if ($key === 0) {
            $relatedColumn = Str::snake(class_basename($column)) . '_id';

            // return "->integer('{$relatedColumn}')->unsigned();" . PHP_EOL . "\t\t\t" . "\$table->foreignId('{$relatedColumn}')";
            return "->foreignId('{$relatedColumn}')->constrained()->onUpdate('cascade')->onDelete('cascade')";
        }

        if ($key === 1) {
            return "->references('{$field}')";
        }

        if ($key === 2) {
            return "->on('{$field}')";
        }

        if (Str::contains($field, '(')) {
            return '->' . $field;
        }

        return '->' . $field . '()';
    }

    /**
     * Format field to script.
     *
     * @param int    $key
     * @param string $field
     * @param string $column
     *
     * @return string
     */
    protected function addColumn($key, $field, $column)
    {
        // dd($key, $field, $column);
        if ($this->hasCustomAttribute($column)) {
            return '->' . $field;
        }

        if (Str::contains($field, '(')) {
            return '->' . preg_replace( '/\\\,/', ',',$field);
        }

        if ($key == 0) {
            return '->' . $field . "('" . $column . "')";
        }


        return '->' . $field . '()';
    }

    /**
     * Format field to script.
     *
     * @param int    $key
     * @param string $field
     * @param string $column
     *
     * @return string
     */
    protected function removeColumn($key, $field, $column)
    {
        if ($this->hasCustomAttribute($column)) {
            return '->' . $field;
        }

        return '->dropColumn(' . "'" . $column . "')";
    }

    /**
     * Get column name from schema.
     *
     * @param string $schema
     *
     * @return string
     */
    public function getColumn($schema)
    {
        return Arr::get(explode(':', $schema), 0);
    }

    /**
     * Get column attributes.
     *
     * @param string $column
     * @param string $schema
     *
     * @return array
     */
    public function getAttributes($column, $schema)
    {
        $fields = str_replace($column . ':', '', $schema);

        return $this->hasCustomAttribute($column) ? $this->getCustomAttribute($column) : array_map(function($field){
            return $field;
        }, explode(':', $fields));
    }

    /**
     * Determine whether the given column is exist in customAttributes array.
     *
     * @param string $column
     *
     * @return bool
     */
    public function hasCustomAttribute($column)
    {
        return array_key_exists($column, $this->customAttributes);
    }

    /**
     * Get custom attributes value.
     *
     * @param string $column
     *
     * @return array
     */
    public function getCustomAttribute($column)
    {
        return (array) $this->customAttributes[$column];
    }
}
