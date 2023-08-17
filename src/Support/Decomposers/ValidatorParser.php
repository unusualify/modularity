<?php

namespace OoBook\CRM\Base\Support\Decomposers;

use OoBook\CRM\Base\Traits\ManageNames;
use Illuminate\Contracts\Support\Arrayable;

class ValidatorParser implements Arrayable
{
    use ManageNames;

    protected $methods = [
        'belongsTo',
    ];

    protected $arguments = [
        'belongsTo'         => ['table', 'foreign_key', 'owner_key'],
    ];


    /**
     * The rule.
     *
     * @var string
     */
    protected $rules;

    /**
     * Create new instance.
     *
     * @param string|null $schema
     */
    public function __construct($rules = [])
    {
        $this->rules = $rules;
    }

    /**
     * Convert string relation to array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->parse($this->rules);
    }

    /**
     * parse
     *
     * @param  mixed $rules
     * @return void
     */
    public function parse($rules)
    {

        $parsed = [];

        foreach ($this->getFields() as $field) {

            $arr = explode('=', $field);

            $parsed[$arr[0]] = $arr[1] ?? '';
        }

        return $parsed;

    }

    /**
     * getFields
     *
     * @return array
     */
    public function getFields()
    {
        if (is_null($this->rules)) {
            return [];
        }

        return explode('&', str_replace(' ', '', $this->rules));
    }

    /**
     * toReplacement
     *
     * @return string
     */
    public function toReplacement()
    {
        return str_replace("\n", "\n\t\t", arrayExport($this->toArray(),true));
    }


    /**
     * Render the migration to formatted script.
     *
     * @return string
     */
    public function render()
    {

    }



}
