<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Support\Finder;

trait ManageForm
{
    /**
     * @var array
     */
    protected $defaultFormAttributes = [];

    /**
     * @var array
     */
    protected $formAttributes = [];

    protected $formSchema;

    /**
     * actions/buttons to see in the form
     *
     * @var array
     */
    // protected $formActions = [];

    protected $inputTypes = [];

    // protected $schemaChangers = [];

    protected function __beforeConstructManageForm($app, $request)
    {
        $this->inputTypes = modularityConfig('input_types', []);
        $this->module = Modularity::find($this->moduleName);
        $this->formSchema = $this->createFormSchema($this->getConfigFieldsByRoute('inputs'));
    }

    protected function __afterConstructManageForm($app, $request)
    {
        $this->defaultFormAttributes = (array) Config::get(modularityBaseKey() . '.default_form_attributes');
        $this->formAttributes = array_merge_recursive_preserve($this->getFormAttributes(), $this->formAttributes ?? []);

        $this->setFormActions();
    }

    /**
     * getFormOptions
     */
    public function getFormAttributes(): array
    {
        if ((bool) $this->config) {
            try {
                return Collection::make(
                    array_merge_recursive_preserve($this->defaultFormAttributes, object_to_array($this->getConfigFieldsByRoute('form_options', [])))
                )->toArray();
            } catch (\Throwable $th) {
                return [];
            }
        }

        return [];
    }

    public function setFormActions()
    {
        $this->defaultFormActions = (array) Config::get(modularityBaseKey() . '.default_form_actions', []);

        $formActions = [];

        if ((bool) $this->config) {
            try {
                $formActions = Collection::make(
                    array_merge_recursive_preserve($this->defaultFormActions, object_to_array($this->getConfigFieldsByRoute('form_actions', [])))
                )->toArray();
            } catch (\Throwable $th) {

            }
        }

        $this->formActions = array_merge_recursive_preserve($formActions, $this->formActions ?? []);
    }

    public function getFormActions(): array
    {
        $default_action = (array) Config::get(modularityBaseKey() . '.default_form_action');

        return Collection::make($this->formActions)->reduce(function ($acc, $action, $key) use ($default_action) {

            $allowedRoles = $action['allowedRoles'] ?? null;

            if (is_string($allowedRoles)) {
                $allowedRoles = explode(',', $allowedRoles);
            }

            if ($allowedRoles && $this->user) {
                if (! $this->user->isSuperAdmin() && ! $this->user->hasRole($allowedRoles)) {
                    return $acc;
                }
            }

            if (isset($action['endpoint']) && ($routeName = Route::hasAdmin($action['endpoint']))) {
                $parameters = Route::getRoutes()->getByName($routeName)->parameterNames();
                $action['endpoint'] = route($routeName, array_fill_keys($parameters, ':id'));
                // $action['endpoint'] = route($routeName, ['press_release' => ':id']);
                // dd($parameters, $action);
                // $action['endpoint'] = route($routeName, ['{id}' => '{id}']);
            }

            if (isset($action['schema'])) {
                $action['schema'] = $this->createFormSchema($action['schema']);
                // dd($action['schema']);
            }

            $acc[$key] = array_merge_recursive_preserve($default_action, $action);

            return $acc;
        }, []);
    }

    protected function addWithsManageForm(): array
    {
        // $this->indexWith += collect($schema)->filter(function($item){

        return collect(array_to_object($this->formSchema))->filter(function ($input) {
            // return $this->hasWithModel($item['type']);
            return in_array($input->type, [
                'treeview',
                'input-treeview',
                // 'checklist',
                // 'input-checklist',
                'select',
                'combobox',
                'autocomplete',
                'input-repeater',
            ]) && ! (isset($input->ext) && $input->ext == 'morphTo');
        })->mapWithKeys(function ($input) {

            if ($input->type == 'input-repeater') {
                if (isset($input->ext) && $input->ext == 'relationship') {
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

                } else {
                    return [];
                }
            } else {
                $relationship = $this->getCamelNameFromForeignKey($input->name) ?: $input->name;
            }

            if (in_array($input->type, ['select', 'combobox', 'autocomplete']) && ! isset($input->repository)) {
                return [];
            }

            $relationshipsTypes = [];

            if (method_exists($this->repository->getModel(), 'definedRelationsTypes')) {
                $relationshipsTypes = $this->repository->definedRelationsTypes();
            }

            $relationType = null;

            if (array_key_exists($relationship, $relationshipsTypes)) {
                $relationType = $relationshipsTypes[$relationship];
            }

            if (in_array($relationType, ['MorphToMany', 'BelongsToMany'])) {
                return [
                    $relationship,
                ];
            }

            return [
                $relationship => [
                    // ['select', $item['itemValue'], $item['itemTitle']],
                    ['addSelect', $input->itemValue ?? 'id'],
                    ['addSelect', $input->itemTitle ?? 'name'],
                ],
            ];
        })->toArray();
    }

    protected function createFormSchema($inputs)
    {
        return Collection::make($inputs)->mapWithKeys(function ($input, $key) use ($inputs) {
            return $this->getSchemaInput($input, $inputs);
        })->toArray();
    }

    protected function getSchemaInput($input, $inputs = [])
    {
        // $default_input = collect(Config::get(modularityBaseKey() . '.default_input'))->mapWithKeys(function($v, $k){return is_numeric($k) ? [$v => true] : [$k => $v];});
        // $default_input = $this->configureInput(array_to_object(Config::get(modularityBaseKey() . '.default_input')));
        $default_input = (array) Config::get(modularityBaseKey() . '.default_input');
        // dd($default_input);
        [$hydrated, $spreaded] = $this->hydrateInput(object_to_array($input), $inputs);

        if ($spreaded) {
            return $hydrated;
        }

        $type = getValueOrNull($hydrated, 'type');
        $name = getValueOrNull($hydrated, 'name');
        $_input = null;

        if ($type == 'divider' || (bool) $name) {
            if ($default_input['color'] && in_array($hydrated['type'], ['morphTo', 'relationship', 'wrap', 'group'])) {
                unset($default_input['color']);
            }
            $_input = $this->configureInput(array_merge_recursive_preserve($default_input, $hydrated));
            if ($type == 'divider') {
                $name = $type . '_' . uniqid();
                $_input['name'] ??= $name;
            }
        }

        return (bool) $name ? [$name => $_input] : [];

        return isset($name)
            // ? [ $input->name => $default_input->union( $this->configureInput($input) ) ]
            // ? [ $input['name'] => array_merge_recursive_preserve( $default_input, $this->configureInput($input) ) ]
            ? [$hydrated['name'] => $this->configureInput(array_merge_recursive_preserve($default_input, $hydrated))]
            : ($type == 'divider' ? [$type . '_' . uniqid() => $hydrated] : []);
    }

    /**
     * @param array|stdClass $input
     * @return Collection
     */
    protected function configureInput($input)
    {
        return configure_input($input);
    }

    /**
     * @param array $input
     * @return array
     */
    protected function hydrateInput($input, $inputs = [])
    {
        $data = null;
        $arrayable = false;

        $this->hydrateInputType($input);

        $this->hydrateInputConnector($input);

        if (! in_array($input['type'], ['morphTo', 'relationship', 'repeater']) && isset($input['schema'])) {
            $input['schema'] = $this->createFormSchema($input['schema']);
        }

        switch ($input['type']) {
            case 'input-treeview':
            case 'treeview':
                $relation_class = null;

                // dd(
                //     Modularity::find($this->moduleName),
                //     // $this->config->parent_route,
                //     // FacadesModule::find('Base')
                // );
                if (isset($input['repository'])) {
                    $relation_class = App::make($input['repository']);
                } elseif (isset($input['model'])) {
                    $relation_class = App::make($input['model']);
                } elseif (isset($input['route'])) {
                    $finder = new Finder;
                    $module = Modularity::find($this->moduleName);

                    if ($module->isEnabledRoute($input['route'])) {
                        foreach ($this->config->routes as $r) {
                            if ($r->route_name == $input['route']) {
                                $table = Str::plural($input['route']);
                                $relation_class = $finder->getModel($table);
                            }
                        }
                    }
                }

                $data = [];

                $_input = (array) $input;
                $data[$input->name] = Arr::except($_input, ['route', 'model']) + [
                    'items' => [
                        [
                            'id' => -1,
                            'name' => 'Role Group',
                            'children' => $relation_class->all(['id', 'name'])->toArray(),
                        ],
                    ],
                ];

                break;
            case 'group':
            case 'wrap':
                $input['typeInt'] ??= 'sheet';
                if (isset($input['noLabel']) && $input['noLabel']) {
                    unset($input['label'], $input['title']);

                } else {
                    $input['title'] ??= $input['label'] ?? (isset($input['name']) ? headline($input['name']) : '');
                }
                $default_repeater_col = [
                    'cols' => 12,
                ];
                $input['col'] = array_merge_recursive_preserve($default_repeater_col, $input['col'] ?? []);

                $schema = $this->createFormSchema($input['schema']);

                if ($input['type'] == 'wrap') {
                    $input['name'] ??= 'wrap-' . uniqid();
                }

                $input['schema'] = $input['type'] == 'wrap'
                    ? $schema
                    // : Arr::mapWithKeys($schema, fn($i) => ["{$input['name']}.{$i['name']}" => array_merge($i,['name' => "{$input['name']}.{$i['name']}"])]);
                    : Arr::mapWithKeys($schema, function ($_input) use ($input) {
                        if ($input['type'] == 'group') {
                            $_input['parentName'] = $input['name'];
                        }
                        $name = $_input['type'] == 'wrap' ? "{$input['name']}.{$_input['name']}" : $_input['name'];

                        return [$name => array_merge($_input, ['name' => $name])];
                    });

                if ($input['type'] == 'group') {
                    $input['default'] = Collection::make($schema)->reduce(function ($acc, $item, $key) {
                        if ($item['type'] == 'wrap') {
                            $acc += Arr::map($item['schema'], fn ($i) => $i['default'] ?? '');
                        } else {
                            $acc[$key] = $item['default'] ?? '';
                        }

                        return $acc;
                    }, []);

                    // dd($input);
                }

                if ($input['type'] == 'wrap') {
                    $input['default'] = Arr::map($schema, fn ($i) => $i['default'] ?? '');
                }

                if (isset($input['rules']) && is_string($input['rules'])) {
                    if (preg_match('/required/', $input['rules'])) {
                        if (isset($input['class'])) {
                            $input['class'] .= ' required';
                        } else {
                            $input['class'] = 'required';
                        }
                    }
                }

                // dump($input);
                // if($input['name'] == 'wrap-content')
                $data = $input;

                break;
            case 'relationship':
                $relationshipName = $input['relationship'] ?? $input['name'] ?? null;

                if (! $relationshipName) {
                    $data = [];

                    break;
                }

                $foreignKey = $this->getForeignKeyFromName($this->routeName);
                $relationshipInputs = $this->app['modularity']
                    ->find($this->moduleName)
                    ->getRouteConfig(studlyName($input['name']) . '.inputs');

                $input['type'] = 'input-repeater';
                $input['label'] = pluralize($this->getHeadline($input['name']));
                $input['autoIdGenerator'] = false;

                $singularRelationshipName = $this->getCamelCase($this->getSingular($relationshipName));
                $pluralRelationshipName = pluralize($singularRelationshipName);

                if (($relationType = $this->repository->getRelationType($singularRelationshipName))) {
                    $relationshipName = $singularRelationshipName;
                } elseif (($relationType = $this->repository->getRelationType($pluralRelationshipName))) {
                    $relationshipName = $pluralRelationshipName;
                } else {

                }

                if ($relationType) {

                    $input['schema'] = $this->createFormSchema(Collection::make($relationshipInputs)->map(function ($_input) use ($foreignKey) {

                        if (isset($_input['name']) && $foreignKey == $_input['name']) {
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

                } else {
                    $data = [];
                }

                // if(!array_key_exists($relationshipName, $relationships)){
                //     unset($data['name']);
                // }

                break;
            case 'morphTo':

                $data = [];

                if (isset($input['schema'])) {
                    $data = [];
                    $arrayable = true;
                    $length = count($input['schema']);

                    $reversedParents = array_reverse($input['schema']);

                    foreach ($reversedParents as $index => $attachable) {
                        $name = $attachable['name'];
                        $connector = $attachable['connector'] ?? null;
                        $attachable = $this->getSchemaInput($attachable + ['noRecords' => true])[$name];

                        if ((bool) $connector) {
                            $attachable['connector'] = $connector;
                        }
                        unset($attachable['noRecords']);
                        $attachable['ext'] = 'morphTo';

                        if ($index == ($length - 1)) {
                            // 'packageRegions:id,package_continent_id,name',
                            // 'packageRegions.packageCountries:id,package_region_id,name'
                            $attachable['cascades'] = [];
                            $selectables = array_values(array_reverse($data));
                            $relationChain = '';
                            foreach ($selectables as $j => $item) {
                                $foreignKey = $item['name'];
                                $relationshipName = pluralize($this->getCamelNameFromForeignKey($foreignKey));
                                $relationChain .= ! $relationChain ? $relationshipName : ".{$relationshipName}";
                                $ownerKey = $j == 0 ? $attachable['name'] : $selectables[$j - 1]['name'];

                                $attachable['cascades'][] = $relationChain;
                                // $attachable['cascades'][] = $relationChain . ":{$item['itemValue']},{$ownerKey},{$item['itemTitle']}";

                                // $attachable['cascades'][$relationChain . " as {$relationChain}_items"] = [
                                //     ['select', $item['itemValue'] , $ownerKey, $item['itemTitle']]
                                // ];
                            }
                            $attachable['cascade'] = $reversedParents[$index - 1]['name'];

                        } elseif ($index) {
                            $attachable['cascade'] = $reversedParents[$index - 1]['name'];
                        }

                        if ($index !== ($length - 1)) {
                            $attachable['items'] = [];
                        }

                        $_input = $this->getSchemaInput($attachable);

                        $data += $_input;
                    }
                    $data = array_reverse($data);
                }

                if (empty($data)) {
                    $input = $data;
                }

                break;
            default:

                break;
        }

        if (isset($input['type'])) {
            $input = hydrate_input($input, $this->module ?? null);

            if (in_array($input['type'], ['input-repeater']) && isset($input['schema'])) {
                $input['schema'] = $this->createFormSchema($input['schema']);
            }
        }

        $this->hydrateInputExtension($input, $data, $arrayable, $inputs);

        if (isset($this->repository) && isset($input['name'])) {
            try {
                if (method_exists($this->repository->getModel(), 'isTranslationAttribute')
                    && $this->repository->isTranslationAttribute($input['name'])
                ) {
                    $input['translated'] ??= true;
                    // $input['locale_input'] = $input['type'];
                    // $input['type'] = 'input-locale';
                    $data = $input;
                }
            } catch (\Throwable $th) {
                dd($input, $th);
            }

        }

        return [
            $data ? $data : $input,
            $arrayable,
        ];
    }

    /**
     * @param array|stdClass $input
     * @return void
     */
    protected function hydrateInputType(&$input)
    {
        if (array_key_exists($input['type'], $this->inputTypes)) {
            $input = array_merge($this->inputTypes[$input['type']], Arr::except($input, ['type']));
        }
    }

    public function hydrateInputConnector(&$input)
    {
        if (isset($input['connector'])) {
            // 'moduleName:routeName|uri:edit'
            $targetType = 'uri';

            $parts = explode('|', $input['connector']);

            $names = explode(':', array_shift($parts)); // moduleName:routeName
            $routeName = $this->getStudlyName(array_pop($names));
            $targetModuleName = $this->getStudlyName(! empty($names) ? array_pop($names) : $this->moduleName);
            $targetModule = Modularity::find($targetModuleName);

            $types = ! empty($parts) ? explode(':', array_shift($parts)) : ['uri', 'index']; // uri:edit
            // controller,repository,uri
            $targetType = array_shift($types) ?? $targetType; //

            $input['_moduleName'] = $targetModuleName;
            $input['_routeName'] = $routeName;

            switch ($targetType) {
                case 'uri':
                    $input['endpoint'] = $targetModule->getRouteActionUri($routeName, empty($types) ? 'index' : array_shift($types));

                    break;
                default:
                    $input[kebabCase($targetType)] = implode(':', [$targetModule->getRouteClass($routeName, $targetType), ...$types]);
                    unset($input['connector']);

                    break;
            }
        }
    }

    public function hydrateInputExtension(&$input, &$data, &$arrayable, $inputs)
    {
        foreach ($inputs as $_input) {
            if (isset($_input->type) && $_input->type === 'relationship') {
                $additionalExt = [];

                $foreignKeyExt = collect(Modularity::find($this->moduleName)->getRouteConfig(studlyName($_input->name) . '.inputs'))
                    ->filter(fn ($_i) => $this->getCamelCase($_i['name'] ?? '') === $this->getCamelCase($this->routeName) . 'Id')
                    ->toArray()[1]['ext'] ?? '';

                foreach (explode('|', $foreignKeyExt) as $pattern) {
                    [$methodName, $formattedInput, $parentColumnName] = array_pad(explode(':', $pattern), 3, null);

                    switch ($methodName) {
                        case 'lock':
                            if (isset($input['name']) && $parentColumnName === $input['name']) {
                                $additionalExt += [$methodName . ':' . pluralize($this->getCamelCase($_input->name)) . '.' . $formattedInput . ':' . $parentColumnName];
                            }

                            break;
                        case 'permalinkPrefix':
                            if (isset($input['name']) && $input['name'] === 'name') {
                                $additionalExt += [$methodName . ':' . pluralize($this->getCamelCase($_input->name)) . '.' . $formattedInput];
                            }
                        default:

                            break;
                    }
                }

                if (isset($input['ext'])) {
                    $additionalExtString = wrapImplode(seperator: '|', array: $additionalExt, prepend: '|', append: '');
                    $input['ext'] .= $additionalExtString;

                } else {
                    $input['ext'] = implode('|', $additionalExt);
                }

            }
        }

        if (isset($input['ext'])) {

            $continue = false;
            if (is_string($input['ext'])) {
                switch ($input['ext']) {
                    case 'date':
                        // code...
                        $input['default'] ??= date('Y-m-d');
                        $continue = true;

                        break;
                    case 'time':
                        $input['default'] ??= date('H:i');
                        $continue = true;

                        break;

                    default:
                        // code...
                        break;

                }
            }

            if ($continue) {
                return;
            }

            //  pattern examples
            // 'permalink:slug'
            // 'permalinkPrefix:slug',
            // 'permalinkPrefix:slug|lock:url:url',
            $patterns = $input['ext'];
            if (is_string($input['ext'])) {
                $patterns = explode('|', $input['ext']);
            }

            $events = [];
            $extraInputs = [];

            foreach ($patterns as $pattern) {
                $args = $pattern;
                if (is_string($pattern)) {
                    $pattern = trim($pattern);
                    $args = explode(':', $pattern);
                }

                $methodName = array_shift($args);
                // [$methodName, $formattedInput, $parentColumnName] = array_pad(explode(':',$pattern), 3, null);
                $changers = [];
                switch ($methodName) {
                    case 'permalinkPrefix': // 'permalinkPrefix:slug',
                        $inputToFormat = array_shift($args);
                        if (isset($input['repository'])) {
                            foreach ($this->getConfigFieldsByRoute('inputs') as $key => $_input) {
                                if (isset($_input->ext) && in_array(explode(':', $_input->ext)[0], ['permalink'])) {
                                    $events[] = 'formatPermalinkPrefix:' . $inputToFormat . ':' . $this->getSnakeNameFromForeignKey($input['name']);
                                }
                            }
                        } else {
                            $events[] = 'formatPermalinkPrefix:' . $inputToFormat . ':' . $this->getSnakeCase($this->routeName());
                        }

                        break;
                    case 'lock': // 'lock:url:url'
                        $inputToFormat = array_shift($args);
                        $parentColumnName = array_shift($args);
                        $events[] = "formatLock:{$inputToFormat}:{$parentColumnName}";

                        break;
                    case 'permalink': // 'permalink:slug',
                        $inputToFormat = array_shift($args);
                        $permalinkPrefix = getHost() . '/';
                        $permalinkPrefixFormat = getHost() . '/';
                        foreach ($inputs as $_input) {
                            if (is_array($_input)) {
                                if (isset($_input['type'])
                                    && in_array($_input['type'], ['select', 'combobox', 'hidden'])
                                    && isset($_input['repository'])
                                    && isset($_input['ext'])
                                ) {
                                    $permalinkPrefixFormat .= ":{$this->getSnakeNameFromForeignKey($_input['name'])}" . '/';
                                }
                            }

                            if (isset($_input->type)
                                && in_array($_input->type, ['select', 'combobox', 'hidden'])
                                && isset($_input->repository)
                                && isset($_input->ext)
                            ) {
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
                            'readonly' => true,
                        ]);
                        unset($input['ext']);
                        $events[] = 'formatPermalink:' . $inputToFormat;

                        break;
                    case 'filter': // 'filter:{target_input_name}:{target_prop_name}:{followed_key_name}'
                        $inputToFormat = array_shift($args);
                        $targetPropName = array_shift($args) ?? 'inputs';

                        $filterEndpoint ??= null;

                        if (! $filterEndpoint && isset($input['schema'])) {
                            $filterEndpoint = Collection::make($input['schema'])->mapWithKeys(function ($r) {

                                $routeName = $this->getStudlyName($r['_routeName'] ?? $r['name']);
                                $targetModuleName = $this->getStudlyName($r['_moduleName'] ?? $this->moduleName);
                                $targetModule = Modularity::find($targetModuleName);

                                $routeName = $this->getStudlyName($r['name']);

                                return [$r['name'] => $targetModule->getRouteActionUri($routeName, 'show')];
                            });
                        }

                        if (! $filterEndpoint && isset($input['_routeName'])) {
                            $routeName = $this->getStudlyName($input['_routeName']);
                            $targetModuleName = $this->getStudlyName($input['_moduleName'] ?? $this->moduleName);
                            $targetModule = Modularity::find($targetModuleName);

                            if ($targetModule) {
                                $filterEndpoint = $targetModule->getRouteActionUri($routeName, 'show');
                            }
                        }
                        if ($filterEndpoint) {
                            // schemaChangers set
                            // if(isset($input['toChange'])){
                            //     $changerName = $inputToFormat;
                            //     $inputToFormatParts = explode('.', $inputToFormat);
                            //     if(count($inputToFormatParts) > 1 && ctype_digit($inputToFormatParts[0])){
                            //         $changerName = implode('.', array_slice($inputToFormatParts, 1));
                            //     }
                            //     $this->schemaChangers[] = [
                            //         'changer' => $changerName,
                            //         'toChange' => $input['toChange'],
                            //     ];
                            // }
                            // dd(
                            //     get_defined_vars(),
                            //     $this->schemaChangers
                            // );
                            if ($data) {
                                $data['filterEndpoint'] = $filterEndpoint;
                            } else {
                                $input['filterEndpoint'] = $filterEndpoint;
                            }

                            $events[] = 'formatFilter:' . implode(':', [$inputToFormat, $targetPropName, ...$args]);
                        }

                        break;
                    case 'preview': //
                        $inputToFormat = array_shift($args) ?? '';
                        $previewFieldPatterns = array_shift($args) ?? null;

                        if ($previewFieldPatterns) {
                            $previewFieldPatterns = ':' . $previewFieldPatterns;
                        }

                        if ($inputToFormat) {
                            $events[] = "formatPreview:{$inputToFormat}{$previewFieldPatterns}";
                        }

                        break;
                    case 'set': //
                        $inputToFormat = array_shift($args) ?? '';
                        $inputPropToFormat = array_shift($args) ?? null;
                        $setProp = array_shift($args) ?? "items.*.{$inputPropToFormat}";
                        // dd(
                        //     $inputToFormat,
                        //     $inputPropToFormat,
                        //     $setProp,
                        //     $inputs
                        // );
                        $changers = [
                            'wrap_location' => [
                                'default',
                                $inputPropToFormat, // schema
                            ],
                        ];
                        if ($inputToFormat && $inputPropToFormat) {
                            $events[] = "formatSet:{$inputToFormat}:{$inputPropToFormat}:{$setProp}";
                        }

                        break;
                    case 'clearModel': //
                        $inputToFormat = array_shift($args) ?? '';

                        if ($inputToFormat) {
                            $events[] = "formatClearModel:{$inputToFormat}";
                        }

                        break;
                    case 'resetItems': //
                        $inputToFormat = array_shift($args) ?? '';

                        if ($inputToFormat) {
                            $events[] = "formatResetItems:{$inputToFormat}";
                        }

                        break;
                    case 'prependSchema': //
                        $inputToFormat = array_shift($args) ?? '';
                        $prependKey = array_shift($args) ?? null;
                        $setterSchemaKey = array_shift($args) ?? null;

                        if ($inputToFormat && $prependKey && $setterSchemaKey) {
                            $events[] = "formatPrependSchema:{$inputToFormat}:{$prependKey}:{$setterSchemaKey}";
                        }

                        // dd($input, $patterns, $events);
                        break;
                    default:
                        // code...
                        break;
                }
            }

            if (! empty($events)) {
                $data = (array) ($data ?? $input);
                try {
                    // code...
                    // if($input['name'] == 'packageCountry'){
                    //     dd($data, $events, explode('|', $data['event'] ?? ''));
                    // }
                    $data['event'] = implode('|', array_merge($events, isset($data['event']) ? explode('|', $data['event']) : []));
                } catch (\Throwable $th) {
                    dd($events, $data, $th, $this->config);
                }
            }

            if (! empty($extraInputs)) {
                $arrayable = true;
                $_input = (array) ($data ?? $input);
                $data = [];
                $data = $this->getSchemaInput($_input) + $extraInputs;
            }
        }
    }
}
