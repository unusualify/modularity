<?php

namespace Unusualify\Modularous\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

trait ManageTraits
{
    use ManageModuleRoute, Traitify;

    public function inputs($noGroupChunk = false)
    {
        $moduleName = $this->getModuleName();

        $routeName = $this->getRouteName();

        if ($moduleName && $routeName) {
            $module = $this->getModule();
            if ($module) {
                $routeConfig = $module->getRawRouteConfig($routeName);

                return $this->chunkInputs($routeConfig['inputs'], noGroupChunk: $noGroupChunk);
            }
            // return $route_config['inputs'];
        }

        return [];
    }

    public function hasTranslatedInput($schema = [])
    {
        $hasTranslated = false;

        foreach ((count($schema) ? $schema : $this->inputs()) as $input) {
            if (isset($input['translated']) && $input['translated']) {
                $hasTranslated = true;

                break;
            }
        }

        return $hasTranslated;
    }

    public function chunkInputs($schema = null, $all = false, $noGroupChunk = false)
    {
        return Arr::mapWithKeys($schema ?? $this->inputs(), function ($input, $key) use ($all, $noGroupChunk) {
            if (isset($input['type'])) {
                $input = hydrate_input_type($input);

                switch ($input['type']) {
                    case 'group':

                        if ($noGroupChunk) {
                            break;
                        }

                        return Arr::mapWithKeys($this->chunkInputs($input['schema'] ?? []), function ($_input) use ($input) {
                            $name = "{$input['name']}.{$_input['name']}";
                            if (isset($input['name'])) {
                                $_input['parentName'] = $input['name'];
                            }

                            return [$name => array_merge($_input, ['name' => $name])];
                        });

                        return $this->chunkInputs($input['schema'] ?? []);
                    case 'wrap':
                        return Arr::map($this->chunkInputs($input['schema'] ?? []), function ($_input) use ($input) {
                            if (isset($input['name'])) {
                                // $_input['parentName'] = $input['name'];
                            }

                            return $_input;
                        });

                        break;
                    case 'morphTo':
                        if ($all) {
                            return $this->chunkInputs($input['schema']);
                        }

                        return [uniqid() => $input];

                        break;
                    case 'repeater':
                    case 'input-repeater':
                    case 'json-repeater':
                        if ($all) {
                            return Arr::mapWithKeys($this->chunkInputs($input['schema']), function ($item) use ($input) {
                                if (isset($input['translated']) && $input['translated']) {
                                    return Arr::mapWithKeys(getLocales(), function ($locale) use ($item, $input) {
                                        $repeater_input_name = "{$input['name']}.{$locale}.*.{$item['name']}";

                                        return [$repeater_input_name => array_merge($item, ['name' => $repeater_input_name])];
                                    });
                                }
                                $repeater_input_name = $input['name'] . '.*.' . $item['name'];

                                return [$repeater_input_name => array_merge($item, ['name' => $repeater_input_name])];
                            });
                        }

                        break;
                    default:

                        break;
                }

                if (isset($input['name'])) {
                    $_key = $input['name'];

                    return [$_key => $input];
                }
            }

            return [];
        });
    }

    public function model()
    {
        if (! $this->isModuleRouteClass()) {
            return null;
        }

        return $this->getModule()?->getModel($this->getRouteName()) ?? null;
    }

    public function prepareFieldsBeforeSaveManageTraits($object, $fields)
    {

        if (isset($fields['password'])) {
            $fields['password'] = Hash::make($fields['password']);
        }

        // Handle JSON column updates
        $jsonUpdates = [];
        $regularFields = [];
        foreach ($fields as $key => $value) {
            if (str_contains($key, '->') || str_contains($key, '.')) {
                $parts = str_contains($key, '->') ? explode('->', $key) : explode('.', $key);
                $jsonColumn = $parts[0];
                $jsonKey = $parts[1];

                if (! isset($jsonUpdates[$jsonColumn])) {
                    $jsonUpdates[$jsonColumn] = $object->{$jsonColumn} ?? [];
                }

                $jsonUpdates[$jsonColumn][$jsonKey] = $value;
            } else {
                $regularFields[$key] = $value;
            }
        }

        // Merge JSON updates with regular fields
        $fields = array_merge(
            $regularFields,
            $jsonUpdates
        );

        return $fields;
    }
}
