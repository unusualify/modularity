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

if (! function_exists('array_to_query_string')) {
    function array_to_query_string(array $data)
    {
        $flat = collect($data)
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
        return $qs;
    }
}

if (! function_exists('merge_url_query')) {
    function merge_url_query(string $url, object|array $data): string
    {
        // Parse existing query parameters
        $url_parts = parse_url($url);
        $base_url = $url_parts['scheme'] . '://' . $url_parts['host'];

        if (isset($url_parts['path'])) {
            $base_url .= $url_parts['path'];
        }

        // Get existing query parameters as array
        $existing_params = [];
        if (isset($url_parts['query'])) {
            parse_str($url_parts['query'], $existing_params);
        }

        // Convert data to array if it's an object
        if (gettype($data) == 'object') {
            $data = (array) $data;
        }

        // Merge existing params with new ones
        $merged_params = array_merge($existing_params, $data);

        // Construct the new URL
        return $base_url . '?' . array_to_query_string($merged_params);



        if (gettype($data) == 'object') {
            $data = object_to_array($data);
        }
        // Parse the URL
        $parsedUrl = parse_url($url);

        // Get the main URL without query parameters
        $mainUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . ($parsedUrl['path'] ?? '');

        // Parse the query string into an array
        $queryParams = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
        }

        if (array_key_exists(array_key_first($data), $queryParams)) {
            unset($queryParams[array_key_first($data)]);
        }

        // Update the query parameters with new ones
        $queryParams = array_merge($queryParams, $data);

        // Convert the updated query parameters back to a string
        $newQueryString = http_build_query($queryParams);

        // Combine the main URL with the new query string
        $finalUrl = $newQueryString ? $mainUrl . '?' . $newQueryString : $mainUrl;

        return $finalUrl;
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

            $routeParameters = [];
            if(count($route->parameterNames())){
                foreach($route->parameterNames() as $parameter){
                    if(isset($params[$parameter])){
                        $routeParameters[$parameter] = $params[$parameter];
                        unset($params[$parameter]);
                    }else{
                        throw new \Exception('Action route must not have parameters: ' . $routeName);
                    }
                }
                // throw new \Exception('Action route must not have parameters: ' . $routeName);
            }

            $url = route($routeName, array_merge($routeParameters, $params));

            if (count($params) > 0) {
                $qs = array_to_query_string($params);

                // 4) Append "?" + query string to the URL
                $url .= '?' . $qs;
            }
        }

        return $url;
    }
}
