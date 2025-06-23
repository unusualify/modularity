<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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

        if (in_array('Oobook\Priceable\Traits\HasPriceable', class_uses_recursive($model))) {
            $model['prices_show'] = $model->price_formatted;
            $model['price_show'] = $model->price_formatted;
        }

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

if (! function_exists('is_plural')) {
    /**
     * Check if a string is in plural form
     *
     * @param string $string The string to check
     * @return bool Returns true if the string is plural, false otherwise
     */
    function is_plural($string)
    {
        return Str::plural($string) === $string;
    }
}

if (! function_exists('replace_variables_from_haystack')) {
    /**
     * Recursively replace ${variable}$ patterns in array/object values with values from haystack
     *
     * @param mixed $input Array or object to process
     * @param array $haystack Array containing replacement values
     * @return mixed
     */
    function replace_variables_from_haystack($input, array $haystack)
    {
        if (is_string($input)) {
            return preg_replace_callback('/\${([^}]+)}\$/', function ($matches) use ($haystack) {
                $variable = $matches[1];

                // Split for default value using ??
                $variableParts = explode('??', $variable);
                $variableNames = explode('|', $variableParts[0]);
                $defaultValue = $variableParts[1] ?? '';

                // Try each variable name in order
                foreach ($variableNames as $variableName) {
                    $variableName = trim($variableName);
                    if (array_key_exists($variableName, $haystack)) {
                        return $haystack[$variableName];
                    }
                }

                // Return default value if no matches found
                return $defaultValue;
            }, $input);
        }

        if (! is_array($input) && ! is_object($input)) {
            return $input;
        }

        $result = is_object($input) ? clone $input : [];

        foreach ($input as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $result[$key] = replace_variables_from_haystack($value, $haystack);
            } elseif (is_string($value)) {
                $result[$key] = replace_variables_from_haystack($value, $haystack);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}

if (! function_exists('extract_schema_extensions')) {
    /**
     * Extract extension configurations from nested schema arrays
     *
     * @param array $schema The schema to search through
     * @return array Array of [path, property, pattern] tuples
     */
    function extract_schema_extensions($haystack)
    {
        $results = [];

        if (! is_array($haystack)) {
            return $results;
        }

        // Process current level ext configurations
        if (isset($haystack['ext']) && is_array($haystack['ext'])) {

            foreach ($haystack['ext'] as $ext) {
                if (is_array($ext) && count($ext) >= 4 && in_array($ext[0], ['set', 'prependSchema'])) {
                    $format = $ext[0];

                    if ($format == 'set') {
                        $setterKey = explode('.*.', $ext[3])[0];
                        $setterInnerKey = explode('.*.', $ext[3])[1] ?? null;
                        if (isset($haystack[$setterKey])) {
                            $modelNotation = (isset($haystack['parentName']) ? $haystack['parentName'] . '.' : '') . $haystack['name'];
                            $results[] = [
                                'format' => $format,
                                'targetPath' => $ext[1],
                                'property' => $ext[2],
                                'pattern' => $ext[3],
                                'name' => $haystack['name'],
                                'modelNotation' => $modelNotation,
                                'setterKey' => $setterKey,
                                'itemTitle' => $haystack['itemTitle'] ?? null,
                                'itemValue' => $haystack['itemValue'] ?? null,
                                'setterInnerKey' => $setterInnerKey,
                                'setterValues' => $haystack[$setterKey],
                            ];
                        }
                    } elseif ($format == 'prependSchema') {
                        // dd($ext);
                        // $results[] = [
                        //     'format' => $format,
                        //     'targetPath' => $ext[1],
                        //     'property' => $ext[2],
                        // ];
                    }
                }
            }
        }

        // Recursively process nested schemas
        foreach ($haystack as $key => $value) {
            if (is_array($value) && Arr::isAssoc($value)) {
                // Check for schema property
                if (isset($value['schema']) && is_array($value['schema'])) {
                    $results = array_merge($results, extract_schema_extensions($value['schema']));
                }

                // Also check if the current array itself is a schema collection
                foreach ($value as $subKey => $subValue) {
                    if (is_array($subValue)) {

                        if (isset($subValue['schema']) && is_array($subValue['schema'])) {
                            $results = array_merge($results, extract_schema_extensions($subValue['schema']));
                        }

                        if ($subKey == 'schema' && is_array($subValue)) {
                            foreach ($subValue as $subSubKey => $subSubValue) {
                                if (is_array($subSubValue)) {
                                    $results = array_merge($results, extract_schema_extensions($subSubValue));
                                }
                            }
                        }

                    }

                }
            }
        }

        return $results;
    }
}

if (! function_exists('data_get_with_dot_keys')) {
    /**
     * Get an item from an array or object using dot notation, supporting keys with dots
     *
     * @param mixed $target Array or object to search
     * @param string $path Dot notation path
     * @param mixed $default Default value if not found
     * @return mixed
     */
    function data_get_with_dot_keys($target, $path, $default = null)
    {
        // Handle empty cases
        if (is_null($target) || is_null($path)) {
            return $default;
        }

        // Split path by dots, but preserve dots within square brackets
        preg_match_all('/\[[^\]]*\]|[^.]+/', $path, $matches);
        $segments = $matches[0];

        foreach ($segments as $segment) {
            // Remove brackets if present
            $segment = trim($segment, '[]');

            if (is_array($target)) {
                if (! array_key_exists($segment, $target)) {
                    // Try with original dot notation
                    if (array_key_exists(str_replace('\\', '', $segment), $target)) {
                        $segment = str_replace('\\', '', $segment);
                    } else {
                        return $default;
                    }
                }
                $target = $target[$segment];
            } elseif (is_object($target)) {
                if (! isset($target->{$segment})) {
                    return $default;
                }
                $target = $target->{$segment};
            } else {
                return $default;
            }
        }

        return $target;
    }
}

if (! function_exists('data_set_with_dot_keys')) {
    /**
     * Set an item in an array or object using dot notation, supporting keys with dots
     *
     * @param mixed &$target Array or object to modify
     * @param string $path Dot notation path
     * @param mixed $value Value to set
     * @return void
     */
    function data_set_with_dot_keys(&$target, $path, $value)
    {
        // Split path by dots, but preserve dots within square brackets
        preg_match_all('/\[[^\]]*\]|[^.]+/', $path, $matches);
        $segments = $matches[0];

        $current = &$target;

        foreach ($segments as $segment) {
            // Remove brackets if present
            $segment = trim($segment, '[]');

            // Create arrays for missing segments
            if (! is_array($current) && ! is_object($current)) {
                $current = [];
            }

            if (is_array($current)) {
                if (! array_key_exists($segment, $current)) {
                    $current[$segment] = [];
                }
                $current = &$current[$segment];
            } elseif (is_object($current)) {
                if (! isset($current->{$segment})) {
                    $current->{$segment} = [];
                }
                $current = &$current->{$segment};
            }
        }

        $current = $value;
    }
}

if (! function_exists('name_surname_resolver')) {
    function name_surname_resolver($fullName)
    {
        // dd('Name Surname Resolver');
        $trimmedSpaces = trim($fullName);
        $nameArray = explode(' ', $trimmedSpaces);
        $nameWithSurname = [];
        foreach ($nameArray as $name) {
            if (preg_match('/^[\p{L}\'-]+$/u', $name)) {
                $nameWithSurname[] = $name;
            }
        }
        // dd([
        //     count($nameWithSurname),
        //     ...$nameWithSurname,
        // ]);

        return $nameWithSurname;
    }
}
