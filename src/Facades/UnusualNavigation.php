<?php

namespace OoBook\CRM\Base\Facades;

use Illuminate\Support\Facades\Facade;

class UnusualNavigation extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'unusual.navigation';
        // return \OoBook\CRM\Base\Support\UnusualNavigation::class;
    }
}
