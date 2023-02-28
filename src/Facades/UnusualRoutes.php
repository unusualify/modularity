<?php

namespace OoBook\CRM\Base\Facades;

use Illuminate\Support\Facades\Facade;

class UnusualRoutes extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        // return 'unusual';
        return \OoBook\CRM\Base\Support\UnusualRoutes::class;
    }
}
