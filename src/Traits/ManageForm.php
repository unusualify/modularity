<?php

namespace Unusualify\Modularity\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Arr;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Priceable\Models\Currency;

trait ManageForm {

    protected $formSchema;

    protected function __beforeConstructManageForm($app, $request) {
        $this->formSchema = $this->createFormSchema($this->getConfigFieldsByRoute('inputs'));
    }

    protected function createFormSchema($inputs)
    {
        return Collection::make( $inputs )->mapWithKeys(function($input, $key){
            return $this->getSchemaInput($input);
        })->toArray();
    }

    protected function getSchemaInput($input)
    {
        // $default_input = collect(Config::get(unusualBaseKey() . '.default_input'))->mapWithKeys(function($v, $k){return is_numeric($k) ? [$v => true] : [$k => $v];});
        // $default_input = $this->configureInput(array2Object(Config::get(unusualBaseKey() . '.default_input')));
        $default_input = (array) Config::get(unusualBaseKey() . '.default_input');

        [$hydrated, $arrayable] = $this->hydrateInput(object2Array($input));

        if($arrayable){
            return $hydrated;
        }
        return isset($hydrated['name'])
            // ? [ $input->name => $default_input->union( $this->configureInput($input) ) ]
            // ? [ $input['name'] => array_merge_recursive_preserve( $default_input, $this->configureInput($input) ) ]
            ? [ $hydrated['name'] => $this->configureInput( array_merge_recursive_preserve( $default_input, $hydrated )) ]
            : [];
    }

    /**
     * @param Array|stdClass $input
     * @return Collection
     */
    protected function configureInput($input)
    {
        return collect($input)
            ->mapWithKeys(function($v, $k){
                if($k == 'label' && ___("form-labels.{$v}") !== "form-labels.{$v}")
                    $v = ___("form-labels.{$v}");
                // if($k == 'label')
                //     $v = ___("form-labels.{$v}");

                return is_numeric($k) ? [$v => true] : [$k => $v];
            })
            ->toArray();
    }

    /**
     * @param Array|stdClass $input
     * @return Collection
     */
    protected function hydrateInput($input)
    {
        $data = null;
        $arrayable = false;
        switch ($input['type']) {
            case 'custom-input-treeview':
            case 'treeview':
                $relation_class = null;

                // dd(
                //     Modularity::find($this->moduleName),
                //     // $this->config->parent_route,
                //     // FacadesModule::find('Base')
                // );
                if(isset($input['repository'])){
                    $relation_class = App::make($input['repository']);
                }else if(isset($input['model'])){
                    $relation_class = App::make($input['model']);
                }else if(isset($input['route'])){
                    $finder = new Finder();
                    $module = Modularity::find($this->moduleName);

                    if( $module->isEnabledRoute($input['route']) ){
                        foreach ($this->config->routes as $r) {
                            if($r->route_name == $input['route']){
                                $table = Str::plural($input['route']);
                                $relation_class = $finder->getModel($table);
                            }
                        }
                    }
                }

                $data = [];

                $_input = (array) $input;
                $data[$input->name] =   Arr::except($_input, ['route','model']) + [
                    'items' => [
                        [
                            'id' => -1,
                            'name' => 'Role Group',
                            'children' => $relation_class->all(['id', 'name'])->toArray()
                        ]
                    ]
                ];

            break;
            case 'checklist':
                // dd($input);
                $relation_class = null;

                $input['itemValue'] = $input['itemValue'] ?? 'id';
                $input['itemTitle'] = $input['itemTitle'] ?? 'name';
                $input['type'] = 'custom-input-checklist';
                $input['default'] = [];
                $items = [];
                if(isset($input['repository'])){
                    $relation_class = App::make($input['repository']);
                    $items = $relation_class->list($input['itemTitle'])->toArray();
                }else if(isset($input['model'])){
                    $relation_class = App::make($input['model']);
                    $items = $relation_class->all([$input['itemValue'], $input['itemTitle']])->toArray();
                }else if(isset($input['route'])){
                    $finder = new Finder();
                    $module = Modularity::find($this->moduleName);

                    if( $module->isEnabledRoute($input['route']) ){
                        foreach ($this->config->routes as $r) {
                            if($r->route_name == $input['route']){
                                $table = Str::plural($input['route']);
                                $relation_class = $finder->getRepository($table);
                                $items = $relation_class->list($input['itemTitle'])->toArray();
                                break;
                            }
                        }
                    }
                }

                $data =  Arr::except($input, ['route','model', 'repository']) + [
                    'items' => $items
                ];

            break;
            case 'select':
            case 'combobox':
                // dd($input);
                $relation_class= null;
                $input['itemValue'] = $input['itemValue'] ?? 'id';
                $input['itemTitle'] = $input['itemTitle'] ?? 'name';
                $input['default'] ??= [];
                // $input[] = 'multiple';
                if(isset($input['items'])) break;

                $items = [];
                $with = [];

                if(isset($input['cascades'])){
                    // [
                    //     'packageRegions:id,package_continent_id,name',
                    //     'packageRegions.packageCountries:id,package_region_id,name'
                    // ]
                    $with = $input['cascades'];
                }

                if(isset($input['repository'])){
                    $relation_class = App::make($input['repository']);
                    $items = $relation_class->list($input['itemTitle'], $with)->toArray();

                    if(isset($input['cascades'])){
                        $patterns = [];
                        foreach ($input['cascades'] as $key => $cascade) {
                            $explodes = explode('.', explode(':', $cascade)[0]);
                            $patterns[] = "/{$this->getSnakeCase(
                                $explodes[count($explodes)-1]
                            )}/";
                        }
                        $flat = Arr::dot($items);
                        $newArray = [];
                        foreach ($flat as $key => $value) {
                            $newKey = preg_replace($patterns, 'items', $key);
                            Arr::set($newArray, $newKey, $value);
                        }

                        $items = $newArray;
                    }

                }else if(isset($input['model'])){
                    $relation_class = App::make($input['model']);
                    $items = $relation_class->all([$input['itemValue'], $input['itemTitle']])->toArray();

                }else if(isset($input['route'])){
                    $finder = new Finder();
                    $module = Modularity::find($this->moduleName);

                    if( $module->isEnabledRoute($input['route']) ){
                        foreach ($this->config->routes as $r) {
                            if($r->route_name == $input['route']){
                                $table = Str::plural($input['route']);
                                $relation_class = $finder->getRepository($table);
                                $items = $relation_class->list($input['itemTitle'])->toArray();
                                break;
                            }
                        }
                    }
                }

                if(count($items) && isset($items[0][$input['itemValue']]) && $items[0][$input['itemValue']]){
                    array_unshift($items, [
                        $input['itemValue'] => 0,
                        $input['itemTitle'] => 'Please Select'
                    ]);
                }


                $data =  Arr::except($input, ['route','model', 'repository', 'cascades']) + [
                    'items' => $items
                ];

            break;
            case 'switch':
            case 'checkbox':
                $input['color'] ??= 'success';
                $input['trueValue'] ??= 1;
                $input['falseValue'] ??= 0;
                $input['hideDetails'] = true;
                $input['default'] = 0;

                $data = $input;
            break;
            case 'repeater':
            case 'custom-input-repeater':
                $relation_class= null;

                $input['type'] = 'custom-input-repeater';
                if( $input['draggable'] ?? false){
                    $input['orderKey'] ??= 'position';
                }

                $default_repeater_col = [
                    'cols' => 12,
                    'sm' => 12,
                    'md' => 12,
                    'lg' => 12,
                    'xl' => 12
                ];
                $input['col'] = array_merge_recursive_preserve($default_repeater_col, $input['col'] ?? []);

                if(array_key_exists('schema', $input)){
                    $inputStudlyName = '';
                    $inputSnakeName = '';

                    if(isset($input['repository'])){
                        if( preg_match( '/(\w+)Repository/', get_class_short_name($input['repository']), $matches)){
                            $relation_class = App::make($input['repository']);
                            $inputStudlyName = $matches[1];
                            $inputSnakeName = $this->getSnakeCase($inputStudlyName);
                            $inputCamelName = $this->getCamelCase($inputStudlyName);
                        }
                    } else if(isset($input['model'])){
                        if( preg_match( '/(\w+)/', get_class_short_name($input['model']), $matches)){
                            dd($matches);
                            $relation_class = App::make($input['model']);

                            $inputStudlyName = $matches[1];
                            $inputSnakeName = $this->getSnakeCase($inputStudlyName);
                        }
                    }
                    foreach ($input['schema'] as $key => &$_input) {
                        switch ($_input['type']) {
                            case 'select':
                            case 'combobox':
                            case 'autocomplete':
                                if($inputSnakeName){

                                    if(preg_match("/{$inputSnakeName}_id/", $_input['name'])){ // it means foreign_id of pivot table
                                        if(isset($input['repository'])){
                                            $_input['repository'] ??= $input['repository'];
                                        } else if(isset($input['model'])){
                                            $_input['model'] ??= $input['model'];
                                        }
                                    }else {
                                        $_input['items'] ??= [];
                                    }
                                    break;
                                }
                            default:
                                # code...
                                break;
                        }
                    }

                    $input['schema'] = $this->createFormSchema($input['schema']);
                }

                $data = $input;
            break;
            case 'morphTo':

                if(isset($input['parents'])){
                    $data = [];
                    $arrayable = true;
                    $length = count($input['parents']);

                    $reversedParents = array_reverse($input['parents']);

                    foreach ($reversedParents as $index => $attachable) {
                        $attachable['ext'] = 'morphTo';

                        if($index == ($length-1)){
                            // 'packageRegions:id,package_continent_id,name',
                            // 'packageRegions.packageCountries:id,package_region_id,name'
                            $attachable['cascades'] = [];
                            $selectables = array_values(array_reverse($data));
                            $relationChain = '';
                            foreach($selectables as $j => $item){
                                $foreignKey = $item['name'];
                                $relationshipName = pluralize($this->getCamelNameFromForeignKey($foreignKey));
                                $relationChain .= !$relationChain ? $relationshipName : ".{$relationshipName}";
                                $ownerKey = $j == 0 ? $attachable['name'] : $selectables[$j-1]['name'];
                                $attachable['cascades'][] = $relationChain . ":{$item['itemValue']},{$ownerKey},{$item['itemTitle']}";
                                // $attachable['cascades'][$relationChain . " as {$relationChain}_items"] = [
                                //     ['select', $item['itemValue'] , $ownerKey, $item['itemTitle']]
                                // ];
                            }
                            $attachable['cascade'] = $reversedParents[$index-1]['name'];

                        }else if($index){
                            $attachable['cascade'] = $reversedParents[$index-1]['name'];
                        }

                        if($index !== ($length-1)){
                            $attachable['items'] = [];
                        }

                        $_input = $this->getSchemaInput($attachable);


                        $data += $_input;
                    }
                    $data = array_reverse($data);
                }
            break;
            case 'price':
                $input['name'] ??= 'prices';
                $input['type'] = 'custom-input-price';
                $input['ext'] = 'number';
                $input['clearable'] = false;

                $input['col'] ??= [
                    'cols' => 6,
                    'sm' => 5,
                    'md' => 4,
                ];

                $input['default'] ??= [
                    [
                        'display_price' => '',
                        'currency_id' => 1
                    ]
                ];
                // $input['types'] = PriceType::all()->toArray();
                // $input['vatRates'] = VatRate::all()->toArray();
                $input['currencies'] = Currency::query()->select(['id', 'symbol as name'])->get()->toArray();

                $input['label'] ??= __('Prices');

                $data = $input;
                // dd($data);
            break;
            case 'file':
                $input['name'] ??= 'files';
                $input['type'] = 'custom-input-file';
                $input['translated'] = true;
                $input['default'] ??= [];

                $input['label'] ??= __('Files');

                $data = $input;
                // dd($data);
            break;
            case 'image':
                // dd($input);
                $input['name'] ??= 'images';
                $input['type'] = 'custom-input-image';
                $input['translated'] = true;
                $input['default'] ??= [];

                $input['label'] ??= __('Images');

                $data = $input;
            break;
            case 'group':
            case 'wrap':
                $default_repeater_col = [
                    'cols' => 12,
                ];
                $input['col'] = array_merge_recursive_preserve($default_repeater_col, $input['col'] ?? []);

                $input['schema'] = $this->createFormSchema($input['schema']);
                $input['name'] = "group-" . uniqid();

                $data = $input;

            break;
            default:

                break;
        }
        if(isset($input['ext'])){
            switch ($input['ext']) {
                case 'permalink':
                    # code...
                    $data = [];
                    $arrayable = true;
                    $data += $this->getSchemaInput([
                        'type' => 'text',
                        'name' => 'slug',
                        'ref' => 'permalink'
                    ]);
                    unset($input['ext']);
                    $data += $this->getSchemaInput(
                        $input + [
                            'event' => 'formatPermalink:slug',
                            // 'v-on:change' => 'formatPermalink',
                            // 'onChange' => 'formatPermalink'
                        ]
                    );
                    // dd($data);
                    break;


                default:
                    // unset($input['ext']);
                    break;
            }
        }
        if(isset($this->repository)){

            if( method_exists($this->repository->getModel(), 'getTranslatedAttributes')
                && in_array($input['name'], $this->repository->getTranslatedAttributes())
            ){
                $input['translated'] ??= true;
                // $input['locale_input'] = $input['type'];
                // $input['type'] = 'custom-input-locale';
                $data = $input;
            }

        }

        return [
            $data ? $data : $input,
            $arrayable
        ];
    }
}
