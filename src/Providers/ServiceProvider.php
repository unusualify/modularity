<?php

namespace Unusual\CRM\Base\Providers;

use Illuminate\Support\ServiceProvider as Provider;
use Illuminate\Support\Str;


class ServiceProvider extends Provider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName;

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower;

    /**
     * Create a new service provider instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        parent::__construct($app);

        $this->moduleName = env('BASE_NAME', 'Base');

        $this->moduleNameLower = Str::snake($this->moduleName);
    }
}
