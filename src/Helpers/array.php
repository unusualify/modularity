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

/**
 *
 * @param $expression
 * @param $return type
 *
 * @return boolean
 */
function arrayExport($expression, $return=FALSE, $tab=0 ){
    if (!is_array($expression)) return var_export($expression, $return);

    $export = var_export($expression, TRUE);

    $export = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $export);

    $array = preg_split("/\r\n|\n|\r/", $export);
    $array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);

    $array = preg_replace('/\d\s\=\>\s(?=\[)/', '', $array); // removing index numbers of array.

    $export = join(PHP_EOL, array_filter(["["] + $array));

    if ((bool)$return) return $export; else echo $export;
}

function phpArrayFileContent($expression){

    $export = arrayExport($expression, true);

    return "<?php

return {$export};

    ";
}

function array2Object($arr)
{
    return json_decode( json_encode($arr) );
}

function object2Array($object)
{
    return json_decode( json_encode($object), true);
}

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
