<?php

namespace Unusualify\Modularity\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Priceable\Models\Currency;

trait ManageForm {

    protected $formSchema;

    protected function __beforeConstructManageForm($app, $request) {
        $this->formSchema = $this->createFormSchema($this->getConfigFieldsByRoute('inputs'));
    }

    protected function addWithsManageForm() : array
    {
               // $this->indexWith += collect($schema)->filter(function($item){
        return collect(array2Object($this->formSchema))->filter(function($input){
            // return $this->hasWithModel($item['type']);
            return in_array($input->type, [
                'treeview',
                'custom-input-treeview',
                // 'checklist',
                // 'custom-input-checklist',
                'select',
                'combobox',
                'autocomplete',
                'custom-input-repeater'
            ]) && !(isset($input->ext) && $input->ext == 'morphTo');
        })->mapWithKeys(function($input){

            if($input->type == 'custom-input-repeater'){
                if(isset($input->ext) && $input->ext == 'relationship'){
                    return [$input->name];

                    // try {
                    //     $relationships =  method_exists($this->repository->getModel(), 'getDefinedRelations')
                    //         ? $this->repository->getDefinedRelations()
                    //         : $this->repository->modelRelations();


                    //     return in_array($relationshipName, $relationships)
                    //         ? [$relationshipName]
                    //         : [];
                    // } catch (\Throwable $th) {
                    //     dd(
                    //         $th,
                    //         $this->repository,
                    //         $relationshipName
                    //     );
                    // }

                }else{
                    return [];
                }
            }else{
                $relationship = $this->getCamelNameFromForeignKey($input->name) ?: $input->name;
            }

            if(in_array($input->type, ['select', 'combobox', 'autocomplete']) && !isset($input->repository)){
                return [];
            }


            return [
                $relationship => [
                    // ['select', $item['itemValue'], $item['itemTitle']],
                    ['addSelect', $input->itemValue ?? 'id'],
                    ['addSelect', $input->itemTitle ?? 'name']
                ]
            ];
        })->toArray();
    }

    protected function createFormSchema($inputs)
    {
        return Collection::make( $inputs )->mapWithKeys(function($input, $key) use($inputs){
            return $this->getSchemaInput($input, $inputs);
        })->toArray();
    }

    protected function getSchemaInput($input, $inputs = [])
    {

        // $default_input = collect(Config::get(unusualBaseKey() . '.default_input'))->mapWithKeys(function($v, $k){return is_numeric($k) ? [$v => true] : [$k => $v];});
        // $default_input = $this->configureInput(array2Object(Config::get(unusualBaseKey() . '.default_input')));
        $default_input = (array) Config::get(unusualBaseKey() . '.default_input');
        [$hydrated, $arrayable] = $this->hydrateInput(object2Array($input), $inputs);


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
    protected function hydrateInput($input, $inputs = [])
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
            case 'autocomplete':
                // dd($input);
                $relation_class= null;
                $input['itemValue'] = $input['itemValue'] ?? 'id';
                $input['itemTitle'] = $input['itemTitle'] ?? 'name';
                $input['default'] ??= [];
                $input['cascadeKey'] ??= 'items';
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
                    if(isset($input['ext'])){

                        $extensionMethods = explode('|',$input['ext']);
                        $extensionColumnNames = collect($extensionMethods)->filter(fn($method) => in_array(explode(':', $method)[0], ['lock']))->map(fn($method) => explode(':',$method)[1])->toArray();
                        $items = $relation_class->list([$input['itemTitle'], ...$extensionColumnNames],$with)->toArray();
                    }else{
                        $items = $relation_class->list($input['itemTitle'], $with)->toArray();

                    }
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



                $data = Arr::except($input, ['route','model', 'cascades']) + [
                    'items' => $items
                ];

                // if($input['name'] == 'surveys')
                //     dd($data);
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
            case 'json-repeater':
                $relation_class= null;

                $input['type'] = 'custom-input-repeater';
                if( $input['draggable'] ?? false){
                    $input['orderKey'] ??= 'position';
                }

                $input['autoIdGenetaror'] ??= true;

                $input['singularLabel'] = isset($input['label']) ? Str::singular($input['label']) : null;

                $default_repeater_col = [
                    'cols' => 12,
                    // 'sm' => 12,
                    // 'md' => 12,
                    // 'lg' => 12,
                    // 'xl' => 12
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
                        $_input['translated'] = false;
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

                if(isset($input['schema'])){
                    $data = [];
                    $arrayable = true;
                    $length = count($input['schema']);

                    $reversedParents = array_reverse($input['schema']);

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
                $input['translated'] ??= false;
                $input['default'] ??= [];

                $input['label'] ??= __('Files');

                $data = $input;
                // dd($data);
            break;
            case 'image':
                // dd($input);
                $input['name'] ??= 'images';
                $input['type'] = 'custom-input-image';
                $input['translated'] ??= false;
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

                $schema = $this->createFormSchema($input['schema']);

                if($input['type'] == 'wrap'){
                    $input['name'] = "wrap-" . uniqid();
                }

                if($input['type'] == 'group'){
                    $input['title'] = $input['label'] ?? headline($input['name']);

                    $input['default'] = Arr::map($schema, fn($i) => $i['default'] ?? '');
                }


                $input['schema'] = $input['type'] == 'wrap' ? $schema :Arr::mapWithKeys($schema, fn($i) => ["{$input['name']}.{$i['name']}" => array_merge($i,['name' => "{$input['name']}.{$i['name']}"])]);

                $data = $input;

            break;
            case 'relationship':
                $relationshipName = $input['relationship'] ?? $input['name'] ?? null;

                if(!$relationshipName){
                    $data = [];
                    break;
                }

                $foreignKey = $this->getForeignKeyFromName($this->routeName);
                $relationshipInputs = $this->app['modularity']
                    ->find($this->moduleName)
                    ->getRouteConfig(studlyName($input['name']) . '.inputs');

                $input['type'] = 'custom-input-repeater';
                $input['label'] = pluralize($this->getHeadline($input['name']));
                $input['autoIdGenerator'] = false;


                $singularRelationshipName = $this->getCamelCase($this->getSingular($relationshipName));
                $pluralRelationshipName = pluralize($singularRelationshipName);

                if(($relationType = $this->repository->getRelationType($singularRelationshipName))){
                    $relationshipName = $singularRelationshipName;
                } else if(($relationType = $this->repository->getRelationType($pluralRelationshipName))){
                    $relationshipName = $pluralRelationshipName;
                } else {

                }

                if($relationType){

                    $input['schema'] = $this->createFormSchema(Collection::make($relationshipInputs)->map(function($_input) use($foreignKey, $pluralRelationshipName){

                        if(isset($_input['name']) && $foreignKey == $_input['name']){
                            unset($_input['rules']);
                            $_input['type'] = 'hidden';
                        }
                            return $_input;
                        })->toArray());



                    $input['name'] = $relationshipName;
                    $input['ext'] = 'relationship';
                    $input[] = 'withGutter';

                    $relationshipName = $input['relationship'] ?? $input['name'];

                    // $relationships =  method_exists($this->repository->getModel(), 'getDefinedRelations')
                    //     ? $this->repository->getDefinedRelations()
                    //     : $this->repository->modelRelations();
                    $data = $input;

                }else{
                    $data = [];
                }

                // if(!array_key_exists($relationshipName, $relationships)){
                //     unset($data['name']);
                // }

            break;
            case 'json':
                $default_repeater_col = [
                    'cols' => 12,
                ];
                $input['col'] = array_merge_recursive_preserve($default_repeater_col, $input['col'] ?? []);
                $input['type'] = 'group';
                $input['schema'] = $this->createFormSchema($input['schema']);

            break;
            default:

                break;
        }

        foreach ($inputs as  $_input) {
            if(isset($_input->type) &&  $_input->type === 'relationship' ){
                $additionalExt = [];

                $foreignKeyExt = collect($this->app['modularity']
                ->find($this->moduleName)
                ->getRouteConfig(studlyName($_input->name) . '.inputs'))->filter(fn($_i) =>

                 $this->getCamelCase($_i['name'] ?? '') === $this->getCamelCase($this->routeName) . 'Id'
                )->toArray()[1]['ext'] ?? '';

                foreach (explode('|', $foreignKeyExt) as  $pattern) {
                    [$methodName, $formattedInput, $parentColumnName] = array_pad(explode(':',$pattern), 3, null);

                    switch($methodName){
                        case 'lock':
                            if(isset($input['name']) && $parentColumnName === $input['name']){
                                $additionalExt += [$methodName . ':' . pluralize($this->getCamelCase($_input->name)) . '.' . $formattedInput . ':' . $parentColumnName];
                            }
                            break;
                        case 'permalinkPrefix':
                            if(isset($input['name']) && $input['name'] === 'name'){
                                $additionalExt += [$methodName . ':' . pluralize($this->getCamelCase($_input->name)) . '.' . $formattedInput];
                            }
                        default:

                            break;
                    }
                }

                if(isset($input['ext'])){
                    $additionalExtString = wrapImplode(seperator:'|', array: $additionalExt, prepend: '|', append:'');
                    $input['ext'] .= $additionalExtString;

                }else{
                    $input['ext'] = implode('|', $additionalExt);
                }

            }
        }

        if(isset($input['ext'])){
            $patterns = explode('|', $input['ext']);
            $events = [];
            $extraInputs = [];
            foreach ($patterns as $key => $pattern) {
                [$methodName, $formattedInput, $parentColumnName] = array_pad(explode(':',$pattern), 3, null);
                switch ($methodName) {
                    case 'permalinkPrefix':
                        if(isset($input['repository'])){
                            foreach ($this->getConfigFieldsByRoute('inputs') as $key => $_input) {
                                if(
                                    isset($_input->ext)
                                    && in_array(explode(':', $_input->ext)[0], ['permalink']
                                )
                                ){
                                    $events[] = 'formatPermalinkPrefix:'.$formattedInput.':' . $this->getSnakeNameFromForeignKey($input['name']);
                                }
                            }
                        }else{
                            $events[] = 'formatPermalinkPrefix:'.$formattedInput.':' . $this->getSnakeCase($this->routeName());
                        }
                        break;
                    case 'lock':
                        $events[] = 'formatLock:'. $formattedInput .':' . $parentColumnName;
                        break;
                    case 'permalink':
                        $permalinkPrefix = getHost() . '/';
                        $permalinkPrefixFormat = getHost() . '/';
                        foreach ($inputs as $key => $_input) {
                            if(is_array($_input)){
                                if(
                                    isset($_input['type'])
                                && in_array($_input['type'], ['select', 'combobox', 'hidden'])
                                && isset($_input['repository'])
                                && isset($_input['ext'])
                                ){
                                    $permalinkPrefixFormat .= ":{$this->getSnakeNameFromForeignKey($_input['name'])}" . '/';
                                }
                            }

                            if( isset($_input->type)
                                && in_array($_input->type, ['select', 'combobox', 'hidden'])
                                && isset($_input->repository)
                                && isset($_input->ext)
                            ){
                                $permalinkPrefixFormat .= ":{$this->getSnakeNameFromForeignKey($_input->name)}" . '/';
                            }
                        }


                        $extraInputs += $this->getSchemaInput([
                            'type' => 'text',
                            'name' => 'slug',
                            'ref' => 'permalink',
                            'label' => 'Permalink',
                            'prefix' => $permalinkPrefix,
                            'prefixFormat' => $permalinkPrefixFormat,
                            'readonly' => true
                        ]);
                        unset($input['ext']);
                        $events[] = 'formatPermalink:'.$formattedInput;
                        break;

                    default:
                        # code...
                        break;
                }
            }
            if(!empty($events)){
                $data = (array)($data ?? $input);
                $data['event'] = implode('|',$events);
            }
            if(!empty($extraInputs)){
                $arrayable = true;
                $_input = (array)($data ?? $input);
                $data = [];
                $data = $this->getSchemaInput($_input)+$extraInputs;
            }
        }

        if(isset($input['rules']) && is_string($input['rules']) && !$arrayable){
            if(preg_match('/required/', $input['rules'])){
                $data = $data ?? $input;
                if(isset($data['class']))
                    $data['class'] .= " required";
                else
                    $data['class'] = 'required';
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
