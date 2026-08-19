<?php

namespace Unusualify\Modularous\Http\Controllers\Traits\Form;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Services\Connector;
use Unusualify\Modularous\Support\Finder;
use Unusualify\Modularous\Traits\Allowable;

trait FormSchema
{
    use Allowable;

    /**
     * Relation count to eager load for the form view.
     *
     * @var array
     */
    protected $formWithCount = [];

    /**
     * formSchema
     *
     * @var array
     */
    protected $formSchema;

    /**
     * @var array
     */
    protected $inputTypes = [];

    /**
     * @var array
     */
    protected static $formSchemaCallbacks = [];

    /**
     * @return void
     */
    protected function __beforeConstructFormSchema($app, $request)
    {
        $this->inputTypes = modularousConfig('input_types', []);
    }

    /**
     * Update the form schema
     *
     * @return void
     */
    public static function updateFormSchema(callable $callback)
    {
        static::$formSchemaCallbacks[static::class] = $callback;
    }

    public function setupFormSchema()
    {
        $this->formSchema = $this->getModuleFormSchema();
    }

    protected function getModuleFormSchema()
    {
        $inputs = $this->getConfigFieldsByRoute('inputs');

        $inputs = method_exists($this, 'formSchema')
            ? $this->formSchema($inputs)
            : $inputs ?? [];

        if ($this->module && $this->repository) {
            if (method_exists($this->repository, 'appendFormSchema')) {
                $inputs = array_merge($inputs, $this->repository->appendFormSchema());
            }
            if (method_exists($this->repository, 'prependFormSchema')) {
                $inputs = array_merge($this->repository->prependFormSchema($inputs), $inputs);
            }
        }

        if (isset(static::$formSchemaCallbacks[static::class]) && is_callable(static::$formSchemaCallbacks[static::class])) {
            $inputs = call_user_func(static::$formSchemaCallbacks[static::class], object_to_array($inputs));
        }

        return $this->createFormSchema($inputs);
    }

    /**
     * Create the form schema
     *
     * @param array $inputs
     * @return array
     */
    public function createFormSchema($inputs)
    {
        return Collection::make($inputs)->mapWithKeys(function ($input, $key) use ($inputs) {
            return $this->getSchemaInput($input, $inputs);
        })->toArray();
    }

    /**
     * Get the schema input
     *
     * @param array $input
     * @param array $inputs
     * @return array
     */
    protected function getSchemaInput($input, $inputs = [])
    {
        $default_input = (array) Config::get(modularousBaseKey() . '.default_input');
        $input = transform_closure_values($input, forceArray: true);

        [$hydrated, $spreaded] = $this->hydrateInput($input, $inputs);

        if ($spreaded) {
            return $hydrated;
        }

        $type = getValueOrNull($hydrated, 'type');
        $name = getValueOrNull($hydrated, 'name');
        $_input = null;

        if (in_array($type, ['divider', 'title']) || (bool) $name) {
            if ($default_input['color'] && in_array($hydrated['type'], ['morphTo', 'relationship', 'wrap', 'group'])) {
                unset($default_input['color']);
            }
            $_input = $this->configureInput(array_merge_recursive_preserve($default_input, $hydrated));
            if (in_array($type, ['divider', 'title'])) {
                $name = $type . '_' . uniqid();
                $_input['name'] ??= $name;
            }
        }

        return (bool) $name ? [$name => $_input] : [];
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
                //     Modularous::find($this->moduleName),
                //     // $this->config->parent_route,
                //     // FacadesModule::find('Base')
                // );
                if (isset($input['repository'])) {
                    $relation_class = App::make($input['repository']);
                } elseif (isset($input['model'])) {

                    $relation_class = App::make($input['model']);
                } elseif (isset($input['route'])) {
                    $finder = new Finder;
                    $module = Modularous::find($this->moduleName);

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

                $input['class'] ??= 'bg-transparent';

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

                $input['type'] = $input['component'] ?? 'input-repeater';
                // $input['label'] ??= pluralize($this->getHeadline($input['name']));
                $input['autoIdGenerator'] ??= false;

                $singularRelationshipName = $this->getCamelCase($this->getSingular($relationshipName));
                $pluralRelationshipName = pluralize($singularRelationshipName);

                if (($relationType = $this->repository->getModel()->getRelationType($singularRelationshipName))) { // if singular relationship name is a relation
                    $relationshipName = $singularRelationshipName;
                } elseif (($relationType = $this->repository->getModel()->getRelationType($pluralRelationshipName))) { // if plural relationship name is a relation
                    $relationshipName = $pluralRelationshipName;
                } else {
                    throw new \Exception("Relationship {$singularRelationshipName} or {$pluralRelationshipName} not found");
                }

                $relationshipRouteName = Str::camel($singularRelationshipName);

                $relationshipInputs = $this->module
                    ->getRawRouteConfig($relationshipRouteName . '.inputs');

                if ($relationType) {
                    $input['schema'] = $this->createFormSchema(Collection::make($input['schema'] ?? $relationshipInputs)->map(function ($_input) use ($foreignKey) {

                        if (isset($_input['name']) && $foreignKey == $_input['name']) {
                            unset($_input['rules']);
                            $_input['type'] = 'hidden';
                        }

                        return $_input;
                    })->toArray());

                    // dd($input['schema']);

                    $input['name'] = $relationshipName;
                    $input['ext'] = 'relationship';
                    // $input[] = 'withGutter';
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

                    $foreignKeys = array_map(fn ($i) => $i['name'], $reversedParents);

                    $cascades = collect($reversedParents)->mapWithKeys(fn ($i) => [
                        $i['name'] => [],
                    ])->toArray();

                    foreach ($reversedParents as $index => $_input) {
                        $isCascadeable = false;
                        $name = $_input['name'];
                        $connector = $_input['connector'] ?? null;
                        $attachable = $this->getSchemaInput($_input + ['noRecords' => true])[$name];

                        if (isset($_input['model'])) {
                            $attachable['model'] = $_input['model'];
                        }
                        if (isset($_input['repository'])) {
                            $attachable['repository'] = $_input['repository'];
                        }
                        if (isset($_input['connector'])) {
                            $attachable['connector'] = $_input['connector'];
                        }
                        if (isset($_input['newConnector'])) {
                            $attachable['newConnector'] = $_input['newConnector'];
                        }

                        if ((bool) $connector) {
                            $attachable['connector'] = $connector;
                        }

                        $modelClass = null;
                        if (isset($attachable['model'])) {
                            if (! class_exists($attachable['model'])) {
                                throw new \Exception('Model ' . $attachable['model'] . ' does not exist on morphTo input: ' . $name);
                            }
                            $modelClass = App::make($attachable['model']);
                        } elseif (isset($attachable['repository'])) {
                            $modelClass = App::make($attachable['repository'])->getModel();
                        } elseif (isset($attachable['_moduleName']) && isset($attachable['_routeName'])) {
                            $modelClass = Modularous::find($attachable['_moduleName'])->getRepository($attachable['_routeName'])->getModel();
                        } else {
                            throw new \Exception('Model or repository or connector not found on morphTo input: ' . $name);
                        }

                        $columns = $modelClass->getTableColumns();
                        $intersects = array_values(array_intersect($foreignKeys, $columns));
                        if (count($intersects) > 0) {
                            $isCascadeable = true;
                            foreach ($intersects as $intersect) {
                                $cascades[$intersect][] = $name;
                            }
                        }

                        unset($attachable['noRecords']);
                        $attachable['ext'] = 'morphTo';

                        if (count($cascades[$name]) > 0) {
                            $attachable['cascades'] = [];
                            foreach ($cascades[$name] as $cascadeableName) {
                                if (isset($data[$cascadeableName])) {
                                    $foreignKey = $data[$cascadeableName]['name'];
                                    $relationshipName = pluralize($this->getCamelNameFromForeignKey($foreignKey));
                                    $attachable['cascades'][] = $relationshipName;
                                    $attachable['cascade'] = $foreignKey;
                                }
                            }
                        } else {
                            // $attachable['cascade'] = $reversedParents[$index - 1]['name'];
                        }

                        // if ($index !== ($length - 1)) {
                        //     $attachable['items'] = [];
                        // }

                        if ($isCascadeable) {
                            $attachable['items'] = [];
                        }

                        try {
                            $_input = $this->getSchemaInput($attachable);

                            $data += $_input;
                        } catch (\Throwable $th) {
                            dd($attachable, $th);
                        }
                    }
                    $data = array_reverse($data);
                }

                if (empty($data)) {
                    $input = $data;
                }

                break;
            case 'polymorphic':
                $arrayable = true;

                $connector = $input['newConnector'] ?? null;
                $model = null;
                $modelInstance = null;

                if (isset($connector)) {
                    $connector = new Connector($connector);
                    $module = $connector->getModule();
                    $modelInstance = $connector->getModel(false);
                }

                if (! $modelInstance && ! isset($input['model'])) {
                    throw new \Exception('Model is required for polymorphic input');
                }

                $model = $input['model'] ?? $modelInstance;

                if (! class_exists($model)) {
                    throw new \Exception('Model ' . $model . ' does not exist on polymorphic input');
                }

                $modelInstance = App::make($model);

                $morphId = $input['morphId'] ?? makeMorphForeignKey(get_class_short_name($model));
                $morphType = $input['morphType'] ?? makeMorphForeignType(get_class_short_name($model));

                $columns = $modelInstance->getTableColumns();

                if (! (in_array($morphId, $columns) && in_array($morphType, $columns))) {
                    throw new \Exception("{$morphType} and {$morphId} columns are not present in the model " . $model);
                }

                // Ensure we have morphs array
                if (! isset($input['morphs']) || ! is_array($input['morphs'])) {
                    throw new \Exception('Morphs array is required for polymorphic input');
                }

                // Transform models into options for the type combobox
                $polymorphics = collect($input['morphs'])->map(function ($p) {
                    $polymorphic = $p;
                    $repository = $p;
                    $hasConnector = false;
                    $items = [];

                    if (isset($polymorphic['newConnector'])) {
                        $connector = new Connector($polymorphic['newConnector']);
                        $items = ! $this->request->ajax() ? $connector->run($polymorphic, 'items') : [];
                        $hasConnector = true;
                    }

                    $repository = $polymorphic['repository'] ?? '';

                    if (is_string($repository) && ! class_exists($repository) && ! $hasConnector) {
                        throw new \Exception('Repository ' . $repository . ' does not exist on polymorphic input');
                    }

                    $repositoryInstance = null;

                    try {
                        if (class_exists($repository)) {
                            $repositoryInstance = App::make($repository);
                        }
                    } catch (\Throwable $th) {
                        dd($th);
                    }

                    if (! $hasConnector) {
                        $items = ! $this->request->ajax() ? $repositoryInstance->list() : [];
                    }

                    if (is_string($polymorphic)) {
                        $polymorphic = [
                            'repository' => $repository,
                        ];
                    }

                    if (isset($polymorphic['name'])) {
                        if (trans()->has($polymorphic['name'])) {
                            $polymorphic['name'] = trans_choice($polymorphic['name'], 1);
                        } else {
                            $polymorphic['name'] = $polymorphic['name'];
                        }
                    } else {
                        $polymorphic['name'] = get_class_short_name($repositoryInstance->getModel()::class);
                    }

                    return [
                        'name' => $polymorphic['name'],
                        'type' => $repositoryInstance ? $repositoryInstance->getModel()::class : null,
                        'items' => $items,
                        ...(isset($polymorphic['rules']) ? ['rules' => $polymorphic['rules']] : []),
                    ];
                })->toArray();

                $spreadedProps = Arr::only($input, ['createable', 'editable']);
                $typeInput = [
                    ...$spreadedProps,
                    'type' => 'select',
                    'name' => $morphType,
                    'label' => 'Type',
                    'itemValue' => 'id',
                    'itemTitle' => 'name',
                    'cascade' => $morphId,
                    'items' => collect($polymorphics)->map(function ($polymorphic) {
                        return [
                            'id' => $polymorphic['type'],
                            'name' => $polymorphic['name'],
                            'items' => $polymorphic['items'],
                        ];
                    })->toArray(),
                    ...(isset($input['rules']) ? ['rules' => $input['rules']] : []),
                ];

                $idInput = [
                    ...$spreadedProps,
                    'type' => 'combobox',
                    'name' => $morphId,
                    'label' => 'Element',
                    'itemValue' => 'id',
                    'itemTitle' => 'name',
                    'items' => [],
                    ...(isset($input['rules']) ? ['rules' => $input['rules']] : []),
                ];

                $input = [
                    $typeInput,
                    $idInput,
                ];

                // $input = [];
                break;
            case 'title':
                $input['padding'] ??= 'a-0';
                $input['margin'] ??= 'b-0';
                $input['transform'] ??= 'none';
                $input['weight'] ??= 'bold';
                $input['class'] ??= 'text-body-large';
                $input['color'] ??= null;

                // $input = [];
                break;
            default:

                break;
        }

        if (isset($input['type'])) {
            $input = hydrate_input(
                input: $input,
                module: $this->module ?? null,
                routeName: $this->routeName ?? null,
                skipQueries: (Request::ajax() && ! Request::inertia()) || App::runningInConsole() || false,
            );

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
        $input = hydrate_input_type($input);
    }

    /**
     * Hydrate the input connector
     *
     * @param array $input
     * @return void
     */
    public function hydrateInputConnector(&$input)
    {
        if (isset($input['newConnector'])) {
            $connector = new Connector($input['newConnector']);
            $input['_moduleName'] = $connector->getModuleName();
            $input['_routeName'] = $connector->getRouteName();

            if ($connector->isLinkTarget()) {
                $connector->run($input, 'endpoint');
            } else {
                $connector->run($input, 'items');
            }
        } elseif (isset($input['connector'])) {
            // 'moduleName:routeName|uri:edit'
            $targetType = 'uri';

            $parts = explode('|', $input['connector']);

            $names = explode(':', array_shift($parts)); // moduleName:routeName
            $routeName = $this->getStudlyName(array_pop($names));
            $targetModuleName = $this->getStudlyName(! empty($names) ? array_pop($names) : $this->moduleName);
            $targetModule = Modularous::find($targetModuleName);

            $types = ! empty($parts) ? explode(':', array_shift($parts)) : ['uri', 'index']; // uri:edit
            // controller,repository,uri
            $targetType = array_shift($types) ?? $targetType; //

            $input['_moduleName'] = $targetModuleName;
            $input['_routeName'] = $routeName;

            // `spread` inputs only need module/route metadata — see
            // SpreadHydrate which reads `_moduleName` / `_routeName` and
            // ignores `endpoint`. The legacy `Module:Route` (no
            // `|type:action` suffix) form would otherwise default to
            // `uri:index` below, calling `getRouteActionUrl(..., 'index')`
            // and throwing for entities that have no registered index
            // route (panel/singleton entities — e.g. SystemSetting:General).
            if (($input['type'] ?? null) === 'spread') {
                return;
            }

            switch ($targetType) {
                case 'uri':
                    $input['endpoint'] = $targetModule->getRouteActionUrl($routeName, empty($types) ? 'index' : array_shift($types));

                    break;
                default:
                    $input[kebabCase($targetType)] = implode(':', [$targetModule->getRouteClass($routeName, $targetType), ...$types]);
                    unset($input['connector']);

                    break;
            }
        }
    }

    /**
     * Hydrate the input extension
     *
     * @param array $input
     * @param array $data
     * @param array $arrayable
     * @param array $inputs
     * @return void
     */
    public function hydrateInputExtension(&$input, &$data, &$arrayable, $inputs)
    {
        foreach ($inputs as $_input) {
            if (isset($_input->type) && $_input->type === 'relationship') {
                $additionalExt = [];

                $foreignKeyExt = collect(Modularous::find($this->moduleName)->getRawRouteConfig(studlyName($_input->name) . '.inputs'))
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
                $targetInputName = array_shift($args);
                $changers = [];

                switch ($methodName) {
                    case 'permalinkPrefix': // 'permalinkPrefix:slug',
                        if (isset($input['repository'])) {
                            foreach ($this->getConfigFieldsByRoute('inputs') as $key => $_input) {
                                if (isset($_input->ext) && in_array(explode(':', $_input->ext)[0], ['permalink'])) {
                                    $events[] = 'formatPermalinkPrefix:' . $inputToFormat . ':' . $this->getSnakeNameFromForeignKey($input['name']);
                                }
                            }
                        } else {
                            $events[] = 'formatPermalinkPrefix:' . $targetInputName . ':' . $this->getSnakeCase($this->getRouteName());
                        }

                        break;
                    case 'lock': // 'lock:url:url'
                        $parentColumnName = array_shift($args);
                        $events[] = "formatLock:{$targetInputName}:{$parentColumnName}";

                        break;
                    case 'permalink': // 'permalink:slug',
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
                        $events[] = 'formatPermalink:' . $targetInputName;

                        break;
                    case 'filter': // 'filter:{target_input_name}:{target_prop_name}:{followed_key_name}'
                        $targetPropName = array_shift($args) ?? 'inputs';

                        $filterEndpoint = $input['filterEndpoint'] ?? null;

                        if (! $filterEndpoint && isset($input['schema'])) {
                            $filterEndpoint = Collection::make($input['schema'])->mapWithKeys(function ($r) {

                                $routeName = $this->getStudlyName($r['_routeName'] ?? $r['name']);
                                $targetModuleName = $this->getStudlyName($r['_moduleName'] ?? $this->moduleName);
                                $targetModule = Modularous::find($targetModuleName);

                                $routeName = $this->getStudlyName($r['name']);

                                return [$r['name'] => $targetModule->getRouteActionUrl($routeName, 'show')];
                            });
                        }

                        if (! $filterEndpoint && isset($input['_routeName'])) {
                            $routeName = $this->getStudlyName($input['_routeName']);
                            $targetModuleName = $this->getStudlyName($input['_moduleName'] ?? $this->moduleName);
                            $targetModule = Modularous::find($targetModuleName);

                            if ($targetModule) {
                                $filterEndpoint = $targetModule->getRouteActionUrl($routeName, 'show');
                            }
                        }
                        if ($filterEndpoint) {
                            if ($data) {
                                $data['filterEndpoint'] = $filterEndpoint;
                            } else {
                                $input['filterEndpoint'] = $filterEndpoint;
                            }

                            $events[] = 'formatFilter:' . implode(':', [$targetInputName, $targetPropName, ...$args]);
                        }

                        break;
                    case 'preview': //
                        $previewFieldPatterns = array_shift($args) ?? null;

                        if ($previewFieldPatterns) {
                            $previewFieldPatterns = ':' . $previewFieldPatterns;
                        }

                        if ($targetInputName) {
                            $events[] = "formatPreview:{$targetInputName}{$previewFieldPatterns}";
                        }

                        break;
                    case 'set': //
                    case 'update': // same args as set; frontend runs formatUpdate only when valueChanged (not on initial model hydrate)
                        $targetPropName = array_shift($args) ?? null;
                        $setProp = array_shift($args) ?? "items.*.{$targetPropName}";
                        $modelNotation = array_shift($args) ?? null;
                        $changers = [
                            'wrap_location' => [
                                'default',
                                $targetPropName, // schema
                            ],
                        ];

                        $eventName = 'format' . studlyName($methodName);
                        if ($targetInputName && $targetPropName) {

                            $events[] = implode(':', [$eventName, $targetInputName, $targetPropName, $setProp, ...($modelNotation ? [modelNotation] : [])]);
                        }

                        break;
                    case 'clearModel': //

                        if ($targetInputName) {
                            $events[] = "formatClearModel:{$targetInputName}";
                        }

                        break;
                    case 'resetItems': //

                        if ($targetInputName) {
                            $events[] = "formatResetItems:{$targetInputName}";
                        }

                        break;
                    case 'prependSchema': //
                        $prependKey = array_shift($args) ?? null;
                        $setterSchemaKey = array_shift($args) ?? null;
                        $orderKey = array_shift($args) ?? 'false';

                        if ($targetInputName && $prependKey && $setterSchemaKey) {
                            $events[] = "formatPrependSchema:{$targetInputName}:{$prependKey}:{$setterSchemaKey}:{$orderKey}";
                        }

                        break;
                    case 'removeValue': //

                        if ($targetInputName) {
                            $events[] = "formatRemoveValue:{$targetInputName}";
                        }

                        break;
                    case 'toggleInput': // to toggle d-none class and rawRules
                        $toggleValue = array_shift($args) ?? 'toggleValue';
                        $toggleLevel = array_shift($args) ?? -1;

                        if ($targetInputName) {
                            $events[] = "formatToggleInput:{$targetInputName}:{$toggleValue}:{$toggleLevel}";
                        }

                        break;
                    default:
                        // code...
                        break;
                }
            }

            if (! empty($events)) {
                try {
                    $input['event'] = implode('|', array_unique(array_merge($events, isset($data['event']) ? explode('|', $data['event']) : [])));
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

    /**
     * Filters the provided schema based on the roles of the authenticated user.
     *
     * This method iterates through the schema fields and checks if the user has the
     * necessary roles to access each field. If a field has an 'allowedRoles' attribute
     * and the user does not possess the required role, that field will be excluded
     * from the resulting schema. Additionally, if a field is of type 'group' or 'wrap',
     * the method will recursively filter its schema as well.
     *
     * @param array $schema The schema to be filtered.
     * @return array The filtered schema, containing only fields the user is allowed to access.
     */
    public function filterSchemaByRoles($schema)
    {
        return Collection::make($schema)->reduce(function ($carry, $field, $name) {
            $isAllowed = $this->isAllowedItem($field, searchKey: 'allowedRoles');

            if (
                $isAllowed
                || isset($field['viewOnlyComponent'])
            ) {

                if (! $isAllowed && isset($field['viewOnlyComponent'])) {
                    $carry[$name] = $field;
                } elseif (in_array($field['type'], ['group', 'wrap'])) {
                    if (isset($field['schema'])) {
                        $field['schema'] = $this->filterSchemaByRoles($field['schema']);

                        if (! empty($field['schema'])) {
                            $carry[$name] = Arr::except($field, ['viewOnlyComponent']);
                        }
                    }
                } else {
                    $carry[$name] = Arr::except($field, ['viewOnlyComponent']);
                }

            }

            return $carry;
        }, []);
    }

    public function addFormWithsFormSchema(): array
    {
        return collect(array_to_object($this->formSchema))->reduce(function ($carry, $input) {
            if (isset($input->with) && ! empty($input->with)) {
                $with = is_string($input->with) ? explode(',', $input->with) : $input->with;
                $carry = array_merge($carry, $with);
            }

            return $carry;
        }, []);
    }

    public function addFormAppendsFormSchema(): array
    {
        return collect(array_to_object($this->formSchema))->reduce(function ($carry, $input) {
            if (isset($input->appends) && ! empty($input->appends)) {
                $append = is_string($input->appends) ? explode(',', $input->appends) : $input->appends;
                $carry = array_unique(array_merge($carry, $append));
            }

            if (isset($input->name) && ! empty($input->name)) {
                $carry = array_unique(array_merge($carry, [$input->name]));
            }

            return $carry;
        }, []);
    }
}
