<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Unusualify\Modularity\Entities\Model;

if (! function_exists('lowerName')) {
    function lowerName($string)
    {
        return Str::snake($string);
    }
}

if (! function_exists('studlyName')) {
    function studlyName($string)
    {
        return Str::studly($string);
    }
}

if (! function_exists('camelCase')) {
    function camelCase($string)
    {
        return Str::camel($string);
    }
}

if (! function_exists('kebabCase')) {
    function kebabCase($string)
    {
        return Str::kebab($string);
    }
}

if (! function_exists('snakeCase')) {
    function snakeCase($string)
    {
        return Str::snake($string);
    }
}

if (! function_exists('pluralize')) {
    function pluralize($string)
    {
        return Str::plural($string);
    }
}
if (! function_exists('singularize')) {
    function singularize($string)
    {
        return Str::singular($string);
    }
}

if (! function_exists('headline')) {
    function headline($string)
    {
        return Str::headline($string);
    }
}

if (! function_exists('tableName')) {
    function tableName($string)
    {
        return pluralize(snakeCase($string));
    }
}

if (! function_exists('makeForeignKey')) {
    function makeForeignKey($string)
    {
        return singularize(trim(snakeCase($string), '_-')) . '_id';
    }
}

if (! function_exists('makeMorphName')) {
    function makeMorphName($string, $suffix = 'able')
    {
        return snakeCase(Str::singular($string) . $suffix);
    }
}

if (! function_exists('makeMorphForeignKey')) {
    function makeMorphForeignKey($string, $suffix = 'able')
    {
        return makeForeignKey(makeMorphName($string, $suffix));
    }
}

if (! function_exists('makeMorphForeignType')) {
    function makeMorphForeignType($string, $suffix = 'able')
    {
        return snakeCase(makeMorphName($string, $suffix)) . '_type';
    }
}

if (! function_exists('makeProviderName')) {
    function makeProviderName($string)
    {
        return studlyName($string) . 'ServiceProvider';
    }
}

if (! function_exists('makeMorphToMethodName')) {
    function makeMorphToMethodName($string)
    {
        return makeMorphName(camelCase($string));
    }
}

if (! function_exists('makeMorphPivotTableName')) {
    function makeMorphPivotTableName($string)
    {
        return pluralize(makeMorphName(snakeCase($string)));
    }
}

if (! function_exists('getMorphModelName')) {
    function getMorphModelName($string)
    {
        preg_match("/^(\w+)(able[s]?)$/", $string, $matches);

        if ($matches && count($matches) > 1) {
            return studlyName($matches[1]);
        }

        return Str::studly(Str::singular($string));
    }
}

if (! function_exists('abbreviation')) {
    function abbreviation($string)
    {
        preg_match_all('/\b\w/', Str::headline($string), $matches);

        return implode('', $matches[0]);
    }
}

/**
 * Get the short name of class from class namespace
 *
 * @param string $class
 * @return string
 */
if (! function_exists('get_class_short_name')) {
    function get_class_short_name($class)
    {
        return (new \ReflectionClass($class))->getShortName();
    }
}

if (! function_exists('class_resolution')) {
    function class_resolution($class)
    {
        return '\\' . $class . '::class';
    }
}

if (! function_exists('class_namespace')) {
    /**
     * Get the class "basename" of the given object / class.
     *
     * @param string|object $class
     * @return string
     */
    function class_namespace($class)
    {
        return (new \ReflectionClass($class))->getNamespaceName();
    }
}

if (! function_exists('fileTrace')) {
    function fileTrace($regex)
    {

        $dir = false;

        foreach (debug_backtrace() as $i => $trace) {
            // dd($trace);
            // if( !isset($trace['file']) ){
            //     dd(
            //         $trace, $i, $regex, debug_backtrace()[0]
            //     );
            // }
            if (isset($trace['file']) && preg_match($regex, $trace['file'])) {
                $dir = $trace['file'];

                break;
            } elseif (isset($trace['class']) && preg_match($regex, $trace['class'])) {

                $dir = $trace['class'];

                // dd($dir, $trace, $regex);
                break;
            }
        }
        if (! $dir) {
            dd($regex, debug_backtrace());
        }

        return $dir;
    }
}

/**
 * Converts camelCase string to have spaces between each.
 *
 * @param string $camelCaseString
 * @return string (ex.: camel case string)
 */
if (! function_exists('camelCaseToWords')) {
    function camelCaseToWords($camelCaseString)
    {
        $re = '/(?<=[a-z])(?=[A-Z])/x';
        $a = preg_split($re, $camelCaseString);
        $words = implode(' ', $a);

        return ucfirst(mb_strtolower($words));
    }
}

if (! function_exists('parseRulesSchema')) {
    function parseRulesSchema(array $schema)
    {
        return Arr::map($schema, function ($rulesSchema, $input_name) {
            return Arr::mapWithKeys(explode('|', $rulesSchema), function ($ruleSchema) {
                $ruleSchemaExplode = explode(':', $ruleSchema);

                return [array_shift($ruleSchemaExplode) => implode(':', $ruleSchemaExplode)];
            });
        });
    }
}

if (! function_exists('formatRulesSchema')) {
    function formatRulesSchema(array $parsedSchema)
    {
        return Arr::map($parsedSchema, function ($parsedRulesSchema) {
            return implode('|', Arr::map($parsedRulesSchema, function ($ruleParams, $ruleName) {
                return implode(':', array_merge([$ruleName], ($ruleParams ? [$ruleParams] : [])));
            }));
        });
    }
}

/**
 * Checks if the given parameter is an empty array or empty string
 * returns value or null|false whether it is.
 *
 * Return type can be controlled by  @param $bool
 *
 * Can be used for nested arrays
 *
 * @param mixed variable
 * @param string $key
 * @param bool $bool to control return type
 * @return mixed|null based on variable
 */
if (! function_exists('getValueOrNull')) {
    function getValueOrNull(mixed $val = null, $key = null, $bool = false): mixed
    {
        if (is_array($val) && isset($key)) {
            $val = array_key_exists($key, $val) ? getValueOrNull($val[$key]) : null;
        }

        return empty($val) ? ($bool ? false : null) : $val;
    }
}

if (! function_exists('tryOperation')) {
    function tryOperation(callable $callback, $returnValue = false): mixed
    {
        try {
            // if($callback() instanceof Relation){
            //     dd($callback, $callback());
            // }
            return $callback();
        } catch (\Throwable $th) {
            return $returnValue;
        }
    }
}

if (! function_exists('laravelRelationshipMap')) {
    function laravelRelationshipMap(): array
    {
        $hasRelationshipsClass = \Illuminate\Database\Eloquent\Concerns\HasRelationships::class;

        return collect((new \ReflectionClass($hasRelationshipsClass))->getMethods(\ReflectionMethod::IS_PUBLIC))->reduce(function ($carry, \ReflectionMethod $method) {
            if ($method->getNumberOfParameters() > 2) {
                $carry[$method->name] = collect($method->getParameters())->mapWithKeys(function (\ReflectionParameter $parameter) {
                    return [$parameter->name => [
                        'required' => ($required = ! $parameter->isOptional()),
                        'position' => $parameter->getPosition(),
                        ...(! $required ? ['default' => $parameter->getDefaultValue()] : []),
                    ]];
                })->toArray();
            }

            return $carry;
        }, []);
    }
}

if (! function_exists('saveLaravelRelationshipMap')) {
    function saveLaravelRelationshipMap()
    {
        file_put_contents(
            config_path('laravel-relationship-map.php'),
            php_array_file_content(laravelRelationshipMap())
        );
    }
}

if (! function_exists('wrapImplode')) {
    function wrapImplode(string $seperator, array $array, string $prepend, string $append = ''): string
    {
        if (! $array) {
            return '';
        }

        return $prepend . implode($seperator, $array) . $append;
    }
}

if (! function_exists('modelShowFormat')) {
    function modelShowFormat(&$model)
    {

        // if( get_class_short_name($model) == 'Package'){
        //     dd(class_uses_recursive($model));
        // }
        if (in_array('Oobook\Priceable\Traits\HasPriceable', class_uses_recursive($model))) {
            // dd($model->priceFormatted);
            $model['prices_show'] = $model->price_formatted;
            $model['price_show'] = $model->price_formatted;
            // $model['prices_show'] = "<span class='text-success font-weight-bold'> {$model->price_formatted} </span>";
        }

        // if(get_class($model) == 'Oobook\Priceable\Models\Price'){
        //     dd($model->price(), $model->pricePrependingCurrencyString());
        //     $model['prices_show'] = $model->price_formatted;
        //     // $model['prices_show'] = "<span class='text-success font-weight-bold'> {$model->price_formatted} </span>";
        // }

        if (method_exists($model, 'getShowFormat')) {
            return $model->getShowFormat();
        }

        return $model->name;
    }
}

if (! function_exists('nestedRouteNameFormat')) {
    function nestedRouteNameFormat($routeName, $nestedRouteName)
    {
        return snakeCase($routeName) . '.nested.' . snakeCase($nestedRouteName);
    }
}

if (! function_exists('get_file_string')) {
    function get_file_string($path)
    {
        $lines = file($path);
        $count = 0;
        $content = '';

        foreach ($lines as $line) {
            $count += 1;
            // $content .= str_pad($count, 2, 0, STR_PAD_LEFT).". ".$line . "\n";
            $content .= $line;
        }

        return $content;
    }
}

if (! function_exists('replace_curly_braces')) {
    function replace_curly_braces($string, $replacements)
    {
        $is_object = is_object($replacements);
        $is_indexed_array = is_array($replacements) && array_keys($replacements) === range(0, count($replacements) - 1);
        // dd($is_indexed_array);
        // Use preg_replace_callback for recursive replacement
        $index = 0;

        return preg_replace_callback('/\{([^}]*)\}/', function ($match) use ($replacements, $is_object, $is_indexed_array, &$index) {
            $key = $match[1];

            if ($is_object) {
                // Replace recursively using key-value matching
                // dd($replacements, $key, $match);
                return isset($replacements->$key) ? replace_curly_braces($replacements->$key, $replacements) : $match[0];
            } elseif ($is_indexed_array) {
                // Replace subsequently using array indexes
                return isset($replacements[$index]) ? $replacements[$index++] : $match[0];
            } else {
                // Replace subsequently using array indexes
                return isset($replacements[$key]) ? $replacements[$key] : $match[0];
            }
        }, $string);
    }
}

if (! function_exists('indent')) {
    function indent($indent = 4, $string = '')
    {
        return str_repeat(' ', $indent) . $string;
    }
}

if (! function_exists('comment_string')) {
    /**
     * comment_string
     *
     * @param mixed $descriptions
     * @param mixed $parameters
     * @param mixed $return_type
     * @param mixed $asArray
     */
    function comment_string(array|string $descriptions, $parameters = [], ?string $returnType = null, ?string $varType = null, $asArray = false): array|string
    {
        $lines = [indent(string: '/**')];

        if (is_string($descriptions)) {
            $descriptions = [$descriptions];
        }
        $lines = array_reduce($descriptions, function ($carry, $line) {
            $carry[] = " * {$line}";

            return $carry;
        }, $lines);
        $lines[] = ' *';
        // dd($lines);

        $lines = array_reduce($parameters, function ($carry, $line) {
            [$name] = explode('=', $line);
            $parts = explode(' ', $name);
            $type = 'mixin';
            $parameter = $name;
            if (count($parts) > 1) {
                $type = array_shift($parts);
                $parameter = array_shift($parts);
            }
            $carry[] = " * @param {$type} {$parameter}";

            return $carry;
        }, $lines);

        if ($returnType) {
            $lines[] = " * @return {$returnType}";
        }

        if ($varType) {
            $lines[] = " * @var {$varType}";
        }

        $lines[] = ' */';

        if ($asArray) {
            return $lines;
        }

        return implode("\n" . indent(), $lines);
    }
}

if (! function_exists('method_string')) {
    /**
     * method_string
     *
     * @param mixed $method_name
     * @param mixed $content
     * @param mixed $modifier
     * @param mixed $comment
     * @param mixed $parameters
     * @param mixed $return_type
     */
    function method_string($method_name, $content, $modifier = 'public', $comment = null, $parameters = [], $return_type = null): string
    {
        $lines = comment_string($comment, $parameters, $return_type, asArray: true);

        $parameters = implode(',', $parameters);
        $return_type = $return_type ? ": {$return_type}" : '';
        $lines[] = "{$modifier} function {$method_name}($parameters){$return_type}\n" . indent() . '{';

        if (is_string($content)) {
            $content = [$content];
        }
        $lines = array_reduce($content, function ($carry, $line) {
            $carry[] = indent(string: "{$line}");

            return $carry;
        }, $lines);

        $lines[] = '}';

        return implode("\n" . indent(), $lines);
    }
}

if (! function_exists('attribute_string')) {
    /**
     * method_string
     *
     * @param mixed $method_name
     * @param mixed $content
     * @param mixed $modifier
     * @param mixed $comment
     * @param mixed $parameters
     * @param mixed $return_type
     */
    function attribute_string($attribute_name, $value, $modifier = 'public', $comment = null, $customVarType = null): string
    {
        $varType = gettype($value);
        $lines = comment_string($comment, varType: $customVarType ?? $varType, asArray: true);

        if ($varType == 'array') {
            $value = array_export($value, true);
        } elseif ($varType == 'string') {
            $value = "'{$value}'";
        }

        $lines[] = "{$modifier} \${$attribute_name} = {$value};";

        return implode("\n" . indent(), $lines);
    }
}

if (! function_exists('merge_url_query')) {
    function merge_url_query(string $url, object|array $data): string
    {
        if (gettype($data) == 'object') {
            $data = object_to_array($data);
        }
        // Parse the URL
        $parsedUrl = parse_url($url);

        // Get the main URL without query parameters
        $mainUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . ($parsedUrl['path'] ?? '');

        // Parse the query string into an array
        $queryParams = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
        }

        if (array_key_exists(array_key_first($data), $queryParams)) {
            unset($queryParams[array_key_first($data)]);
        }

        // Update the query parameters with new ones
        $queryParams = array_merge($queryParams, $data);

        // Convert the updated query parameters back to a string
        $newQueryString = http_build_query($queryParams);

        // Combine the main URL with the new query string
        $finalUrl = $newQueryString ? $mainUrl . '?' . $newQueryString : $mainUrl;

        return $finalUrl;
    }
}

if (! function_exists('get_user_profile')) {
    /**
     * get_user_profile
     *
     * @param Model $user
     * @return array
     */
    function get_user_profile($user)
    {
        return $user->only(['id', 'name', 'email']) + [
            'avatar_url' => $user->fileponds()
                ->where('role', 'avatar')
                ->first()
                ?->mediableFormat()['source'] ?? '/vendor/modularity/jpg/anonymous.jpg',
        ];
    }
}

if (! function_exists('concatenate_path')) {
    /**
     * concatenate_path
     *
     * @param string $path
     * @param string $dir
     * @return string
     */
    function concatenate_path($path, $dir)
    {
        $separator = DIRECTORY_SEPARATOR;

        return implode($separator, [rtrim($path, $separator), ltrim($dir, $separator)]);
    }
}

if (! function_exists('concatenate_namespace')) {
    /**
     * concatenate_namespace
     *
     * @param string $namespace
     * @param string $append
     * @return string
     */
    function concatenate_namespace($namespace, $append)
    {
        return implode('\\', [rtrim($namespace, '\\'), ltrim($append, '\\')]);
    }
}

if (! function_exists('get_file_class')) {
    function get_file_class($file)
    {
        $content = file_get_contents($file);

        $namespace = null;
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $namespace = $matches[1];
        }

        $className = null;
        if (preg_match('/class\s+(\w+)(?:\s+extends|\s+implements|\s*{|$)/', $content, $matches)) {
            $className = $matches[1];
        }

        return $namespace ? $namespace . '\\' . $className : $className;
    }
}
