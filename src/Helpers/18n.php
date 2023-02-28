<?php


if (!function_exists('unusualTrans')) {
    function unusualTrans($key, $replace = [])
    {
        $locale = config('base.locale', config('base.fallback_locale', 'en'));
        return trans($key, $replace, $locale);
    }
}
