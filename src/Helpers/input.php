<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Hydrates\InputHydrator;

if (! function_exists('configure_input')) {
    function configure_input(array $input)
    {
        return collect($input)
            ->mapWithKeys(function ($v, $k) {
                if ($k == 'label'
                    && ($translation = ___("form-labels.{$v}")) !== "form-labels.{$v}"
                ) {
                    $v = $translation;
                }

                return is_numeric($k) ? [$v => true] : [$k => $v];
            })
            ->toArray();
    }
}

if (! function_exists('modularity_default_input')) {
    function modularity_default_input()
    {
        return (array) Config::get(modularityBaseKey() . '.default_input');
    }
}

if (! function_exists('hydrate_input_type')) {
    function hydrate_input_type(array $input)
    {
        $inputTypes = modularityConfig('input_types', []);

        try {
            if (array_key_exists($input['type'], $inputTypes)) {
                return array_merge($inputTypes[$input['type']], Arr::except($input, ['type']));
            }

            return $input;
        } catch (\Throwable $th) {
            dd($input, $th);
        }
    }
}

if (! function_exists('hydrate_input_connector')) {
    function hydrate_input_connector(array &$input, $moduleName = null, $routeName = null)
    {
        if (isset($input['connector'])) {
            // 'moduleName:routeName|uri:edit'
            $targetType = 'uri';

            $parts = explode('|', $input['connector']);

            $names = explode(':', array_shift($parts)); // moduleName:routeName
            $routeName = studlyName(array_pop($names));
            $targetModuleName = studlyName(! empty($names) ? array_pop($names) : $moduleName);
            $targetModule = Modularity::find($targetModuleName);

            $types = ! empty($parts) ? explode(':', array_shift($parts)) : ['uri', 'index']; // uri:edit
            // controller,repository,uri
            $targetType = array_shift($types) ?? $targetType; //

            $input['_moduleName'] = $targetModuleName;
            $input['_routeName'] = $routeName;

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
}

if (! function_exists('hydrate_input_extension')) {
    function hydrate_input_extension(array &$input, &$data, &$arrayable, $inputs)
    {
        foreach ($inputs as $_input) {
            if (is_array($_input)) {
                $_input = (object) $_input;
            }

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
                                $additionalExt += [$methodName . ':' . pluralize(camelCase($_input->name)) . '.' . $formattedInput . ':' . $parentColumnName];
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

                                return [$r['name'] => $targetModule->getRouteActionUrl($routeName, 'show')];
                            });
                        }

                        if (! $filterEndpoint && isset($input['_routeName'])) {
                            $routeName = $this->getStudlyName($input['_routeName']);
                            $targetModuleName = $this->getStudlyName($input['_moduleName'] ?? $this->moduleName);
                            $targetModule = Modularity::find($targetModuleName);

                            if ($targetModule) {
                                $filterEndpoint = $targetModule->getRouteActionUrl($routeName, 'show');
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
                            if ($inputToFormat == '3.content.schema.wrap-content.schema.1_content') {
                                // dump($inputToFormat, $inputPropToFormat, $setProp);
                            }
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
                        $orderKey = array_shift($args) ?? 'false';

                        if ($inputToFormat && $prependKey && $setterSchemaKey) {
                            $events[] = "formatPrependSchema:{$inputToFormat}:{$prependKey}:{$setterSchemaKey}:{$orderKey}";
                        }

                    break;
                    case 'removeValue': //
                        $inputToFormat = array_shift($args) ?? '';

                        if ($inputToFormat) {
                            $events[] = "formatRemoveValue:{$inputToFormat}";
                        }

                    break;
                    case 'toggleInput': // to toggle d-none class and rawRules
                        $inputToFormat = array_shift($args) ?? '';
                        $toggleValue = array_shift($args) ?? 'toggleValue';
                        $toggleLevel = array_shift($args) ?? -1;

                        if ($inputToFormat) {
                            $events[] = "formatToggleInput:{$inputToFormat}:{$toggleValue}:{$toggleLevel}";
                        }

                    break;
                    default:
                        // code...
                    break;
                }
            }

            if (! empty($events)) {
                $data = (array) ($data ?? $input);
                try {
                    $data['event'] = implode('|', array_unique(array_merge($events, isset($data['event']) ? explode('|', $data['event']) : [])));
                } catch (\Throwable $th) {
                    dd($events, $data, $th);
                }
            }

            if (! empty($extraInputs)) {
                $arrayable = true;
                $_input = (array) ($data ?? $input);
                $data = [];
                $data = modularity_format_input($_input) + $extraInputs;
            }
        }
    }
}

if (! function_exists('hydrate_input')) {
    function hydrate_input(array $input, $module = null, $routeName = null, $skipQueries = null)
    {
        $hydrator = new InputHydrator($input, $module, $routeName, $skipQueries);

        return $hydrator->hydrate();
    }
}

if (! function_exists('format_input')) {
    function format_input(array $input, $module = null, $routeName = null, $skipQueries = null, $inputs = [])
    {
        $data = null;
        $arrayable = false;

        $input = hydrate_input_type($input);

        hydrate_input_connector($input);

        if (! in_array($input['type'], ['morphTo', 'relationship', 'repeater']) && isset($input['schema'])) {
            $input['schema'] = modularity_format_inputs($input['schema'], $module, $routeName, $skipQueries);
        }

        switch ($input['type']) {
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
                    // dd($reversedParents, $foreignKeys);
                    foreach ($reversedParents as $index => $attachable) {
                        $isCascadeable = false;
                        $name = $attachable['name'];
                        $connector = $attachable['connector'] ?? null;
                        // dd($foreignKeys, $name, $attachable, $connector);
                        $attachable = $this->getSchemaInput($attachable + ['noRecords' => true])[$name];

                        if ((bool) $connector) {
                            $attachable['connector'] = $connector;
                        }

                        $modelClass = null;

                        if (isset($attachable['repository'])) {
                            $modelClass = App::make($attachable['repository'])->getModel();
                        } elseif (isset($attachable['_moduleName']) && isset($attachable['_routeName'])) {
                            $modelClass = Modularity::find($attachable['_moduleName'])->getRepository($attachable['_routeName'])->getModel();
                        } else {
                            throw new \Exception('Repository or connector not found on morphTo input: ' . $name);
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

                if (! isset($input['model'])) {
                    throw new \Exception('Model is required for polymorphic input');
                }

                $model = $input['model'];

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
                    if (is_array($polymorphic)) {
                        $repository = $polymorphic['repository'];
                    } else {
                        throw new \Exception('Invalid polymorphic input');
                    }

                    if (! class_exists($repository)) {
                        throw new \Exception('Repository ' . $repository . ' does not exist on polymorphic input');
                    }

                    $repositoryInstance = App::make($repository);

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
                        'type' => $repositoryInstance->getModel()::class,
                        'items' => ! $this->request->ajax() ? $repositoryInstance->list() : [],
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
                ];

                $idInput = [
                    ...$spreadedProps,
                    'type' => 'combobox',
                    'name' => $morphId,
                    'label' => 'Element',
                    'itemValue' => 'id',
                    'itemTitle' => 'name',
                    'items' => [],
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
                $input['class'] ??= 'text-body-1';
                $input['color'] ??= null;

                // $input = [];
            break;
            default:
            break;
        }

        if (isset($input['type'])) {
            $input = hydrate_input(
                input: $input,
                module: $module ?? null,
                routeName: $routeName ?? null,
                skipQueries: $skipQueries ?? false,
            );
        }

        hydrate_input_extension($input, $data, $arrayable, $inputs);

        // if (isset($this->repository) && isset($input['name'])) {
        //     try {
        //         if (method_exists($this->repository->getModel(), 'isTranslationAttribute')
        //             && $this->repository->isTranslationAttribute($input['name'])
        //         ) {
        //             $input['translated'] ??= true;

        //             $data = $input;
        //         }
        //     } catch (\Throwable $th) {
        //         dd($input, $th);
        //     }

        // }

        return [
            $data ? $data : $input,
            $arrayable,
        ];
    }
}

if (! function_exists('modularity_format_input')) {
    function modularity_format_input(array $input, $module = null, $routeName = null, $skipQueries = null, $inputs = [])
    {
        $defaultInput = modularity_default_input();
        $input = transform_closure_values($input, forceArray: true);

        [$formatted, $spreaded] = format_input($input, $module, $routeName, $skipQueries, $inputs);

        if ($spreaded)
            return $formatted;

        $type = getValueOrNull($formatted, 'type');
        $name = getValueOrNull($formatted, 'name');
        $_input = null;


        if (in_array($type, ['divider', 'title']) || (bool) $name) {
            if ($defaultInput['color'] && in_array($type, ['morphTo', 'relationship', 'wrap', 'group'])) {
                unset($defaultInput['color']);
            }
            $_input = configure_input(array_merge_recursive_preserve($defaultInput, $formatted));

            if (in_array($type, ['divider', 'title'])) {
                $name = $type . '_' . uniqid();
                $_input['name'] ??= $name;
            }
        }

        return (bool) $name ? [$name => $_input] : [];
    }
}

if (! function_exists('modularity_format_inputs')) {
    function modularity_format_inputs(array $inputs, $module = null, $routeName = null, $skipQueries = null)
    {
        return Collection::make($inputs)->mapWithKeys(function ($v, $k) use ($module, $routeName, $skipQueries, $inputs) {
            return modularity_format_input($v, $module, $routeName, $skipQueries, inputs: $inputs);
        })->toArray();
    }
}
