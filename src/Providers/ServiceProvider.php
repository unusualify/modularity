<?php

namespace OoBook\CRM\Base\Providers;

use Illuminate\Support\ServiceProvider as Provider;
use Illuminate\Support\Str;


class ServiceProvider extends Provider
{
    /**
     * @var string $moduleName
     */
    protected $baseName;

    /**
     * @var string $moduleNameLower
     */
    protected $baseKey;

    /**
     * Create a new service provider instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        parent::__construct($app);

        $this->baseName = env('BASE_NAME', 'Base');

        $this->baseKey = Str::snake($this->baseName);
    }
}
