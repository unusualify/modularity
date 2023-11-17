<?php


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
