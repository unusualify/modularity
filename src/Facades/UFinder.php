<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;

class UFinder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Unusualify\Modularity\Support\Finder::class;
    }
}
