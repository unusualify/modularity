<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed process(mixed $file)
 * @method static bool validate(mixed $file)
 * @method static string generateTemporaryUrl(string $path)
 * @method static bool delete(string $path)
 * @method static array getServerConfig()
 *
 * @see \Unusualify\Modularity\Services\Filepond
 */
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
