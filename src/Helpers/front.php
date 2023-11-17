<?php

use Unusualify\Modularity\Services\Assets;

if (!function_exists('unusualMix')) {
    /**
     * @param string $file
     * @return string
     */
    function unusualMix($file)
    {
        return app(Assets::class)->asset($file);
    }
}
