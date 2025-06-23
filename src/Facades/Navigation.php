<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;

class Navigation extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'modularity.navigation';
    }
}
