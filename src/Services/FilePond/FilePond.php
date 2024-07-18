<?php

namespace Unusualify\Modularity\Services\Filepond;

use Illuminate\Support\Facades\Facade;

class Filepond extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'FilePond';
    }
}
