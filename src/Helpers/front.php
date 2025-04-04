<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

if (! function_exists('getHost')) {
    /**
     * @param string $file
     * @return string
     */
    function getHost()
    {
        return parse_url(config('app.url'))['host'];
    }
}

if (! function_exists('getModularityDefaultUrls')) {
    /**
     * Get the default urls for the modularity
     *
     * @return array
     */
    function getModularityDefaultUrls()
    {
        return [
            'languages' => route(Route::hasAdmin('api.languages.index')),
            'base_permalinks' => Arr::mapWithKeys(getLocales(), function ($locale, $key) {
                extract(parse_url(config('app.url'))); // $scheme, $host

                return [$locale => $host];
            }),
        ];
    }
}
