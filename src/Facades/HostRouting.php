<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;

class HostRouting extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'unusualify.hosting';
    }
}
