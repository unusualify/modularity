<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

if (! function_exists('getLocales')) {
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

if (! function_exists('getTimezoneList')) {

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

if (! function_exists('getFormDraft')) {

    function getFormDraft($name, $overwrites = [], $excludes = [], $preserve = true)
    {

        $draft = $preserve
            ? array_merge_recursive_preserve(modularityConfig("form_drafts.{$name}", []), $overwrites)
            : array_merge(modularityConfig("form_drafts.{$name}", []), $overwrites);

        if (count($excludes)) {

            $draft = array_filter($draft, function ($value, $key) use ($excludes) {
                return ! in_array($key, $excludes);
            }, ARRAY_FILTER_USE_BOTH);
        }

        return $draft;
    }
}

if (! function_exists('adminRouteNamePrefix')) {

    function adminRouteNamePrefix()
    {
        return rtrim(ltrim(modularityConfig('admin_route_name_prefix', 'admin'), '.'), '.');
    }
}

if (! function_exists('adminUrlPrefix')) {

    function adminUrlPrefix()
    {
        return modularityConfig('admin_app_url')
            ? false
            : rtrim(ltrim(modularityConfig('admin_app_path', 'admin'), '/'), '/');
    }
}

if (! function_exists('systemUrlPrefix')) {

    function systemUrlPrefix()
    {
        return modularityConfig('system_prefix', 'system-settings');
    }
}

if (! function_exists('systemRouteNamePrefix')) {

    function systemRouteNamePrefix()
    {
        return snakeCase(studlyName(systemUrlPrefix()));
    }
}

if (! function_exists('builtInModularityThemes')) {

    function builtInModularityThemes()
    {
        return collect(array_filter(
            glob(get_modularity_vendor_path('vue/src/sass/themes/*', GLOB_ONLYDIR)),
            fn ($dir) => File::isDirectory($dir) && ! preg_match('/customs/', $dir)
        ))->mapWithKeys(function ($dir) {
            $info = pathinfo($dir);

            return [$info['filename'] => Str::headline($info['filename'])];
        });
    }
}

if (! function_exists('customModularityThemes')) {

    function customModularityThemes()
    {
        return collect(array_filter(
            glob(resource_path('vendor/modularity/themes/*', GLOB_ONLYDIR)),
            fn ($dir) => File::isDirectory($dir)
        ))->mapWithKeys(function ($dir) {
            $info = pathinfo($dir);

            return [$info['filename'] => Str::headline($info['filename'])];
        });
    }
}

if (! function_exists('get_translations')) {

    function get_translations(): array
    {
        $cache_key = 'modularity-languages';

        $cache = Cache::store('file');

        if ($cache->has($cache_key) && false) {
            return $cache->get($cache_key);
        }

        $translations = app('translator')->getTranslations();

        $cache->set($cache_key, json_encode($translations), 600);

        return $translations;
    }
}

if (! function_exists('clear_translations')) {

    function clear_translations(): void
    {
        $cache_key = 'modularity-languages';

        Cache::forget($cache_key);
    }
}
