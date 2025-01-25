<?php

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
