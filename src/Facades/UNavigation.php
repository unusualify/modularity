<?php

namespace OoBook\CRM\Base\Facades;

use Illuminate\Support\Facades\Facade;

class UNavigation extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'unusual.navigation';
        // return \OoBook\CRM\Base\Services\View\UNavigation::class;
    }
}
