<?php

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
