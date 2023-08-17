<?php

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

if (! function_exists('headline')) {
    function headline($string) {
        return Str::headline($string);
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

if (! function_exists('fileTrace')) {
    function fileTrace($regex) {

        $dir = false;

        foreach(debug_backtrace() as $i => $trace){
            // dd($trace);
            if( !isset($trace['file']) ){
                dd(
                    $trace, $i, $regex, debug_backtrace()[0]
                );
            }
            if( preg_match($regex, $trace['file']))
            {
                // dd($trace);
                $dir = $trace['file'];
                break;
            }else if( isset($trace['class']) && preg_match($regex, $trace['class']))
            {

                $dir = $trace['class'];
                // dd($dir, $trace, $regex);
                break;
            }
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


