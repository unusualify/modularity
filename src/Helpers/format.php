<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

if (! function_exists('lowerName')) {
    function lowerName($string) {
        return Str::snake($string);
    }
}

if (! function_exists('studlyName')) {
    function studlyName($string) {
        return Str::studly($string);
    }
}

if (! function_exists('camelCase')) {
    function camelCase($string) {
        return Str::camel($string);
    }
}

if (! function_exists('kebabCase')) {
    function kebabCase($string) {
        return Str::kebab($string);
    }
}

if (! function_exists('snakeCase')) {
    function snakeCase($string) {
        return Str::snake($string);
    }
}

if (! function_exists('pluralize')) {
    function pluralize($string) {
        return Str::plural($string);
    }
}
if (! function_exists('singularize')) {
    function singularize($string) {
        return Str::singular($string);
    }
}

if (! function_exists('headline')) {
    function headline($string) {
        return Str::headline($string);
    }
}

if (! function_exists('tableName')) {
    function tableName($string) {
       return pluralize(snakeCase($string));
    }
}

if (! function_exists('makeForeignKey')) {
    function makeForeignKey($string) {
       return singularize( trim(snakeCase($string), '_-') ) . '_id';
    }
}

if (! function_exists('makeMorphName')) {
    function makeMorphName($string) {
       return Str::singular($string) . 'able';
    }
}

if (! function_exists('makeMorphForeignKey')) {
    function makeMorphForeignKey($string) {
       return makeForeignKey(makeMorphName($string));
    }
}

if (! function_exists('makeMorphForeignType')) {
    function makeMorphForeignType($string) {
       return snakeCase(makeMorphName($string)) . '_type';
    }
}

if (! function_exists('makeMorphToMethodName')) {
    function makeMorphToMethodName($string) {
       return makeMorphName(camelCase($string));
    }
}

if (! function_exists('makeMorphToMethodName')) {
    function makeMorphToMethodName($string) {
        return makeMorphName(camelCase($string));
    }
}

if (! function_exists('makeMorphPivotTableName')) {
    function makeMorphPivotTableName($string) {
        return pluralize(makeMorphName(snakeCase($string)));
    }
}

if (! function_exists('getMorphModelNameFromTableName')) {
    function getMorphModelNameFromTableName($string) {
        preg_match("/^(\w+)(ables)$/", $string, $matches);

        return studlyName($matches[1]);
    }
}


if (! function_exists('abbreviation')) {
    function abbreviation($string) {
        preg_match_all('/\b\w/', Str::headline($string), $matches);
        return implode('', $matches[0]);
    }
}

/**
 * Get the short name of class from class namespace
 *
 * @param  string $class
 * @return string
 */
if (! function_exists('get_class_short_name')) {
    function get_class_short_name($class) {
        return (new \ReflectionClass($class))->getShortName();
    }
}

if (! function_exists('class_resolution')) {
    function class_resolution($class) {
        return "\\" . $class ."::class";
    }
}

if (! function_exists('class_namespace')) {
    /**
     * Get the class "basename" of the given object / class.
     *
     * @param  string|object  $class
     * @return string
     */
    function class_namespace($class)
    {
        return (new \ReflectionClass($class))->getNamespaceName();
    }
}

if (! function_exists('fileTrace')) {
    function fileTrace($regex) {

        $dir = false;

        foreach(debug_backtrace() as $i => $trace){
            // dd($trace);
            // if( !isset($trace['file']) ){
            //     dd(
            //         $trace, $i, $regex, debug_backtrace()[0]
            //     );
            // }
            if( isset($trace['file']) && preg_match($regex, $trace['file'])){
                $dir = $trace['file'];
                break;
            }else if( isset($trace['class']) && preg_match($regex, $trace['class'])){

                $dir = $trace['class'];
                // dd($dir, $trace, $regex);
                break;
            }
        }
        if(!$dir){
            dd($regex, debug_backtrace());
        }
        return $dir;
    }
}

/**
 * Converts camelCase string to have spaces between each.
 * @param string $camelCaseString
 * @return string (ex.: camel case string)
 */
if (!function_exists('camelCaseToWords')) {
    function camelCaseToWords($camelCaseString)
    {
        $re = '/(?<=[a-z])(?=[A-Z])/x';
        $a = preg_split($re, $camelCaseString);
        $words = join(" ", $a);
        return ucfirst(strtolower($words));
    }
}

if (! function_exists('parseRulesSchema')) {
    function parseRulesSchema(array $schema) {
        return Arr::map($schema, function($rulesSchema, $input_name){
            return Arr::mapWithKeys(explode('|', $rulesSchema), function($ruleSchema){
                $ruleSchemaExplode = explode(':',$ruleSchema);
                return [ array_shift($ruleSchemaExplode) => implode(':', $ruleSchemaExplode)];
            });
        });
    }
}

if (! function_exists('formatRulesSchema')) {
    function formatRulesSchema(array $parsedSchema) {
        return Arr::map($parsedSchema, function($parsedRulesSchema){
            return implode('|', Arr::map($parsedRulesSchema, function($ruleParams, $ruleName){
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
 * @param boolean $bool to control return type
 *
 * @return mixed|null based on variable
 */
if(! function_exists('getValueOrNull')){
    function getValueOrNull(mixed $val = null, $key=null, $bool = false): mixed {
        if(is_array($val) && isset($key)){
            $val = array_key_exists($key,$val) ? getValueOrNull($val[$key]) : null;
        }
        return  empty($val) ? ($bool ? false : null) : $val ;
    }
}

/**
 *
 */
if(! function_exists('tryOperation')){
    function tryOperation(callable $callback, $returnValue = false): mixed {
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

/**
 *
 */
if(! function_exists('laravelRelationshipMap')){
    function laravelRelationshipMap(): array {
        $hasRelationshipsClass = \Illuminate\Database\Eloquent\Concerns\HasRelationships::class;

        return collect((new \ReflectionClass($hasRelationshipsClass ))->getMethods(\ReflectionMethod::IS_PUBLIC))->reduce(function($carry, \ReflectionMethod $method){
            if($method->getNumberOfParameters() > 2){
                $carry[$method->name] = collect($method->getParameters())->mapWithKeys(function(\ReflectionParameter $parameter){
                    return [$parameter->name => [
                        'required' => ($required = !$parameter->isOptional()),
                        'position' => $parameter->getPosition(),
                        ...(!$required ? ['default' => $parameter->getDefaultValue()] : [])
                    ]];
                })->toArray();
            }

            return $carry;
        }, []);
    }
}

/**
 *
 */
if(! function_exists('saveLaravelRelationshipMap')){
    function saveLaravelRelationshipMap() {
        file_put_contents(
            config_path('laravel-relationship-map.php'),
            php_array_file_content(laravelRelationshipMap())
        );
    }
}


if( !function_exists('wrapImplode') ){
    function wrapImplode(string $seperator = '|', array $array, string $prepend, string $append = '') : string {
        if(!$array) return '';

        return $prepend . implode($seperator, $array) . $append;
    }
}

if( !function_exists('modelShowFormat') ){
    function modelShowFormat(&$model){

        // if( get_class_short_name($model) == 'Package'){
        //     dd(class_uses_recursive($model));
        // }
        if(in_array('Unusualify\Priceable\Traits\HasPriceable', class_uses_recursive($model))){
            $model['prices_show'] = $model->price_formatted;
            // $model['prices_show'] = "<span class='text-success font-weight-bold'> {$model->price_formatted} </span>";
        }

        if( method_exists($model, 'getShowFormat') )
            return $model->getShowFormat();

        return $model->name;
    }
}

if( !function_exists('nestedRouteNameFormat')) {
    function nestedRouteNameFormat($routeName, $nestedRouteName){
        return snakeCase($routeName) . ".nested." . snakeCase($nestedRouteName);
    }
}

if( !function_exists('get_file_string')) {
    function get_file_string($path){
        $lines = file($path);
        $count = 0;
        $content = "";

        foreach($lines as $line) {
            $count += 1;
            // $content .= str_pad($count, 2, 0, STR_PAD_LEFT).". ".$line . "\n";
            $content .= $line;
        }

        return $content;
    }
}


if( !function_exists('merge_url_query')){
    function merge_url_query(string $url, object|array $data): string
    {
        if(gettype($data) == 'object'){
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

        if(array_key_exists(array_key_first($data), $queryParams)){
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



