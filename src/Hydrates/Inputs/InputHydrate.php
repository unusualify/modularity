<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Unusualify\Modularity\Module;
use Unusualify\Modularity\Traits\ManageNames;

abstract class InputHydrate
{
    use ManageNames;

    /**
     * İnput Schema array
     *
     *  [
     *      'type' => '${input-type}',
     *      'name' => '${input-name}',
     *      ...
     *  ]
     *
     * @var array
     */
    public $input = [];

    /**
     * İnput Schema array
     *
     *
     * @var Unusualify\Modularity\Module
     */
    public $module;

    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [];


    /**
     * Create a new HydrateInput instance.
     */
    public function __construct(array $input, Module $module = null)
    {
        $this->input = $input;

        $this->module = $module;
    }

    /**
     * Set default values if not exists
     */
    public function setDefaults(): void
    {
        foreach ($this->requirements as $attribute => $defaultValue) {
            $this->input[$attribute] ??= $defaultValue;
        }
    }

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    abstract public function hydrate();

    /**
     * return hydrated input
     *
     * @return array
     */
    public function render() : array
    {
        $this->setDefaults();

        $this->input = $this->hydrate();

        $this->input = $this->hydrateRecords();

        $this->input = $this->hydrateRules();

        $this->input = Arr::except($this->input, ['route','model', 'repository', 'cascades', 'connector']);

        return $this->input;
    }

    /**
     *
     *  Set records wrt repository
     *
     * @return array
     */
    protected function hydrateRecords()
    {
        $input = $this->input;

        if(isset($input['repository'])){
            $args = explode(':', $input['repository']);

            $className = array_shift($args);
            $methodName = array_shift($args) ?? 'list';

            if(!@class_exists($className)){
                return $input;
            }

            $repository = App::make($className);

            $params = Collection::make($args)->mapWithKeys(function($arg){
                [$name, $value] = explode('=', $arg);

                // return [$name => [$value]];
                return [$name => explode(',', $value)];
            })->toArray();

            $params = array_merge_recursive($params, ['with' => $this->getWiths()]);
            // dd($params, [$input['itemTitle'] ?? 'name', ...$this->getItemColumns()]);
            $items =  call_user_func_array(array($repository, $methodName), [
                ...($methodName == 'list'? ['column' => [$input['itemTitle'] ?? 'name', ...$this->getItemColumns()]] : []),
                ...$params
            ])->toArray();

            $input['items'] = $items;
            // $input =  Arr::except($input, ['route', 'model', 'repository']) + [
            //     'items' => $items
            // ];
        }

        $this->afterHydrateRecords($input);

        return $input;
    }

    /**
     *
     *  Handle input after records set
     *
     *  @param array &$input
     *
     *  @return void
     */
    public function afterHydrateRecords(&$input)
    {

    }

    /**
     * Get withs to add to model's withs
     *
     * @return array
     */
    protected function getWiths()
    {
        $input = $this->input;

        $withs = [];

        if(isset($input['cascades'])){
            $withs = $input['cascades'];
        }

        $withs = array_merge($withs, $this->withs());

        return $withs;
    }

    /**
     *  Withs defined on the input to add to model's withs
     *
     * @return array
     */
    public function withs() :array
    {
        return [];
    }

    protected function getItemColumns()
    {
        $input = $this->input;

        $columns = [];

        if(isset($input['ext'])){
            $extensionMethods = explode('|',$input['ext']);

            $cols = collect($extensionMethods)->filter(fn($method) => in_array(explode(':', $method)[0], ['lock']))
                ->map(fn($method) => explode(':',$method)[1])
                ->toArray();

            // if(!empty($cols)){
            //     dd(
            //         $cols,
            //         array_merge($cols, $columns)
            //     );
            // }
            $columns = array_merge(collect($extensionMethods)->filter(fn($method) => in_array(explode(':', $method)[0], ['lock']))
                ->map(fn($method) => explode(':',$method)[1])
                ->toArray(), $columns);
            // $items = $relation_class->list([$input['itemTitle'], ...$extensionColumnNames], $with)->toArray();
        }
        $columns = array_merge($columns, $this->itemColumns());

        return $columns;
    }

    public function itemColumns() :array
    {
        return [];
    }

    public function hydrateRules()
    {
        $input = $this->input;

        if(isset($input['rules']) && is_string($input['rules'])){
            if(preg_match('/required/', $input['rules'])){
                if(isset($input['class']))
                    $input['class'] .= " required";
                else
                    $input['class'] = 'required';
            }
        }

        return $input;
    }

    /**
     * Handle magic method __toString.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}