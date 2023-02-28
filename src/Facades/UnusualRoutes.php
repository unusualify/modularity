<?php

namespace Unusual\CRM\Base\Facades;

use Illuminate\Support\Facades\Facade;

class UnusualRoutes extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        // return 'unusual';
        return \Unusual\CRM\Base\Support\UnusualRoutes::class;
    }
}
