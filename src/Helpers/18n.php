<?php


if (!function_exists('unusualTrans')) {
    function unusualTrans($key, $replace = [])
    {
        $locale = config(getUnusualBaseKey() . '.locale', config(getUnusualBaseKey() . '.fallback_locale', 'en'));
        return trans($key, $replace, $locale);
    }
}
