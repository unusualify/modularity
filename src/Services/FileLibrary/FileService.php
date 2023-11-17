<?php

namespace Unusualify\Modularity\Services\FileLibrary;

use Illuminate\Support\Facades\Facade;

class FileService extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'fileService';
    }
}
