<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;

class Filepond extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Filepond';
    }
}
