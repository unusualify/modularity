<?php


if (!function_exists('getLocales')) {
    /**
     * @return string[]
     */
    function getLocales()
    {
        $locales = collect(config('translatable.locales', ['tr']))->map(function ($locale, $index) {
            return collect($locale)->map(function ($country) use ($locale, $index) {
                return is_numeric($index)
                    ? $locale
                    : "$index-$country";
            });
        })->flatten()->toArray();

        if (blank($locales)) {
            $locales = [config('app.locale')];
        }

        return $locales;
    }
}

if (!function_exists('getTimezoneList')) {

    function getTimeZoneList()
    {
        return \Cache::rememberForever('timezones_list_collection', function () {
            $timestamp = time();
            foreach (timezone_identifiers_list(\DateTimeZone::ALL) as $key => $value) {
                date_default_timezone_set($value);
                $timezone[$value] = $value . ' (UTC ' . date('P', $timestamp) . ')';
            }
            return collect($timezone)->sortKeys();
        });
    }
}

if (!function_exists('getInputDraft')) {

    function getInputDraft($name, $overwrites = [], $excludes = [], $preserve = true)
    {

        $draft = $preserve
            ? array_merge_recursive_preserve( unusualConfig("input_drafts.{$name}", []), $overwrites)
            : array_merge( unusualConfig("input_drafts.{$name}", []), $overwrites);

        if(count($excludes)){

            $draft = array_filter($draft, function($value, $key) use($excludes){
                return !in_array($key, $excludes);
            }, ARRAY_FILTER_USE_BOTH);
        }

        return $draft;
    }
}

if (!function_exists('adminRouteNamePrefix')) {

    function adminRouteNamePrefix()
    {
        return rtrim(ltrim(unusualConfig('admin_route_name_prefix', 'admin'), '.'), '.');
    }
}

if (!function_exists('adminRoutePrefix')) {

    function adminRoutePrefix()
    {
        return rtrim(ltrim(unusualConfig('admin_app_path', 'admin'), '/'), '/');
    }
}

