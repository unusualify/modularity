<?php


if (!function_exists('getLocales')) {
    /**
     * @return string[]
     */
    function getLocales()
    {
        $locales = collect(config(getUnusualBaseKey() . '.translatable.locales'))->map(function ($locale, $index) {
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
