<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;

class UNavigation extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'unusual.navigation';
        // return \Unusualify\Modularity\Services\View\UNavigation::class;
    }
}
