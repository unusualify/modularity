<?php

if (!function_exists('array_merge_recursive_distinct')) {
    /**
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     */
    function array_merge_recursive_distinct ( array &$array1, array &$array2 )
    {
      $merged = $array1;

      foreach ( $array2 as $key => &$value )
      {
        if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
        {
          $merged [$key] = array_merge_recursive_distinct ( $merged [$key], $value );
        }
        else
        {
          $merged [$key] = $value;
        }
      }

      return $merged;
    }
}

if (!function_exists('array_merge_recursive_preserve')) {

    function array_merge_recursive_preserve($array1, $array2)
    {
        foreach ($array1 as $key => $value) {
            if (array_key_exists($key, $array2)) {
                if (is_array($value) && is_array($array2[$key])) {
                    // dd(
                    //     $value,
                    //     $array2[$key],
                    //     array_merge_recursive_preserve($value, $array2[$key])
                    // );
                    $array2[$key] = array_merge_recursive_preserve($value, $array2[$key]);
                } else {
                    $array1[$key] = $array2[$key];
                }
            }
        }

        // return $array2;
        return array_merge($array1, $array2);
    }
}

if (!function_exists('array_export')) {
    /**
     *
     * @param $expression
     * @param $return type
     *
     * @return boolean
     */
    function array_export($expression, $return=FALSE, $tab=0 ){
        if (!is_array($expression)) return var_export($expression, $return);

        $export = var_export($expression, TRUE);

        $export = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $export);

        $array = preg_split("/\r\n|\n|\r/", $export);
        $array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);

        $array = preg_replace('/\d+\s\=\>\s(?=\[|(\'[A-Za-z_\-]+\'))/', '', $array); // removing index numbers of array.

        $export = join(PHP_EOL, array_filter(["["] + $array));

        if ((bool)$return) return $export; else echo $export;
    }
}

if (!function_exists('php_array_file_content')) {
function php_array_file_content($expression){

    $export = array_export($expression, true);

    return "<?php

return {$export};

    ";
}
}

if (!function_exists('array_to_object')) {
    function array_to_object($arr)
    {
        return json_decode( json_encode($arr) );
    }
}

if (!function_exists('object_to_array')) {
    function object_to_array($object)
    {
        return json_decode( json_encode($object), true);
    }
}

if (!function_exists('nested_array_merge')) {
    function nested_array_merge ( array $array1, array $array2 )
    {
        $merged = $array1;
        foreach ($array2 as $key => $value) {
            if(is_array($value) && array_key_exists($key, $array1)){
                $merged[$key] = nested_array_merge($array1[$key], $value);
            }else{
                $merged[$key] = getValueOrNull($value, bool: false) ?? $merged[$key];
            }
        }
        return $merged;
    }
}

/**
 * @param array1, baseArray
 * @param arrays, Array going to be merged with
 * @param Conditions, The condition will be checked
 *
 *
 * @return array
 */
if(!function_exists('array_merge_conditional'))
{
    function array_merge_conditional(array $array1 = null, array $arrays, ...$conditions): array
    {
        $result = $array1 ?? [];

        foreach ($arrays as $key => $array) {
            if(isset($conditions[$key]) ? $conditions[$key] : true){
                $result = array_merge($result, $array);
            }
        }
        return $result;
    }
}

/**
 * @param $path, path for file content
 * @param $array, replacement array
 *
 *
 * @return string
 */
if(!function_exists('change_array_file_array'))
{
    function change_array_file_array(string $path, array $array): string
    {
        $content = get_file_string($path);

        $export = array_export($array, true);
        // $pattern = "/(\})[^\}]*$/";
        // $pattern = "/^(return\s)\[(\*)/";
        // $pattern = '/(?<=return\s+\[)((.|\n)+)(?=;)/';
        $pattern = '/(return\s+)(\[)[^\}]*$/';

        return preg_replace($pattern, '$1' . $export . ";\n\n", $content);
    }
}

/**
 * @param $path, path for file content
 * @param $routeName, route name
 * @param $array, replacement array
 *
 *
 * @return string
 */
if(!function_exists('add_route_to_config'))
{
    function add_route_to_config(string $path, string $routeName, array $array): string
    {
        $content = get_file_string($path);

        $routeName = snakeCase($routeName);
        $export = array_export(['routes' => [ $routeName => $array] ], true);

        $parts = explode("\n", $export);
        array_shift($parts);
        array_shift($parts);
        array_pop($parts);
        array_pop($parts);
        $export = implode("\n", $parts);

        $pattern = "/(?<='routes'\s\=\>\s\[)([^\}]*)(\s{4}\],)/";

        return preg_replace($pattern,  "$1" . $export . "\n" . '$2', $content);
    }
}

if (!function_exists('array_except')) {
    function array_except(array $array, array $excepts): array
    {
        return array_filter($array,fn($key) => !in_array($key, $excepts),ARRAY_FILTER_USE_KEY);
    }
}


