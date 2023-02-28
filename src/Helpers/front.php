<?php

use Unusual\CRM\Base\Services\Assets;

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
