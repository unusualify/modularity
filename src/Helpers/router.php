<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Arr;

if (! function_exists('previous_route_name')) {
    /**
     * @return string|boolean
     */
    function previous_route_name()
    {
        $previousUrl = url()->previous();
        $referrerRouteName = null;

        try {
            $referrerRouteName = app('router')->getRoutes()->match(
                app('request')->create($previousUrl)
            )->getName();
        } catch (\Exception $e) {
            // Route not found or other error, leave referrerRouteName as null
        }

        return $referrerRouteName;
    }
}

if (! function_exists('resolve_route')) {
    function resolve_route($definition)
    {
        $routeName = $definition;
        $url = $definition;
        $params = [];

        if(is_array($definition)){
            $routeName = $definition[0];
            $params = $definition[1] ?? [];
        }

        if(($routeName = Route::hasAdmin($routeName))){
            $route = Route::getRoutes()->getByName($routeName);

            if(count($route->parameterNames())){
                throw new \Exception('Action route must not have parameters: ' . $routeName);
            }

            $url = route($routeName);

            if (count($params) > 0) {
                // 2) JSON‐encode any value that is an array or object
                $flat = collect($params)
                    ->mapWithKeys(function($value, $key) {
                        return [
                            $key => is_object($value) || (is_array($value) && Arr::isAssoc($value))
                                        ? json_encode($value, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)
                                        : $value
                        ];
                    })
                    ->all();

                // 3) Build the query string (RFC3986 encoding)
                //    e.g. filter={"foo":"bar"}&page=2
                $qs = http_build_query($flat, '', '&', PHP_QUERY_RFC3986);

                // 4) Append "?" + query string to the URL
                $url .= '?' . $qs;
            }
        }

        return $url;
    }
}
