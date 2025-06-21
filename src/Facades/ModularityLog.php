<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;

/**
 *
 * @see \Illuminate\Log\LogManager
 */
class ModularityLog extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'modularity.log';
    }
}