<?php

namespace Unusualify\Modularity\Generators;

use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Nwidart\Modules\FileRepository;
use Nwidart\Modules\Support\Config\GeneratorPath;
use Illuminate\Container\Container;
use Unusualify\Modularity\Module;
use Unusualify\Modularity\Traits\ReplacementTrait;

use Nwidart\Modules\Generators\Generator as NwidartGenerator;

abstract class Generator extends NwidartGenerator
{
    use ReplacementTrait;

    /**
     * The route name to be created
     *
     * @var string
     */
    protected $name;

    /**
     * The laravel service container.
     *
     * @var Config
     */
    protected $app;

    /**
     * The laravel config instance.
     *
     * @var Config
     */
    protected $config;

    /**
     * The laravel filesystem instance.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * The laravel console instance.
     *
     * @var Console
     */
    protected $console;

    /**
     * The module instance.
     *
     * @var \Unusualify\Modularity\Module
     */
    protected $module;

    /**
     * The module name
     *
     * @var string
     */
    protected $moduleName;

    /**
     * The route name.
     *
     * @var string
     */
    protected $route;

    /**
     * Force status.
     *
     * @var bool
     */
    protected $force = false;

    /**
     * use default inputs and headers
     *
     * @var boolean
     */
    protected $useDefaults;

    protected $fix = false;


    /**
     * The constructor.
     * @param $name
     * @param FileRepository $module
     * @param Config     $config
     * @param Filesystem $filesystem
     * @param Console    $console
     */
    public function __construct(
        $name,
        Config $config = null,
        Filesystem $filesystem = null,
        Console $console = null,
        Module $module = null
    ) {
        $this->name = $name;
        $this->app = Container::getInstance();
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->console = $console;
        $this->module = $module;

        if($module)
            $this->moduleName = $this->module->getName();


        // Stub::setBasePath( config('modules.paths.modules').'/Base/Console/stubs');
    }

    /**
     * Set the laravel config instance.
     *
     * @param Config $config
     *
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get the laravel config instance.
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set the laravel filesystem instance.
     *
     * @param Filesystem $filesystem
     *
     * @return $this
     */
    public function setFilesystem($filesystem)
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Get the laravel filesystem instance.
     *
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * Set the laravel console instance.
     *
     * @param Console $console
     *
     * @return $this
     */
    public function setConsole($console)
    {
        $this->console = $console;

        return $this;
    }

    /**
     * Get the laravel console instance.
     *
     * @return Console
     */
    public function getConsole()
    {
        return $this->console;
    }

    /**
     * Set the Module instance.
     *
     * @param string $module
     *
     * @return $this
     */
    public function setModule($module)
    {
        $modularity = App::makeWith(\Unusualify\Modularity\Modularity::class, ['app' => app()]);

        $this->module = $modularity->find($module);

        $this->moduleName = $this->module->getName();

        return $this;
    }

    /**
     * Get the Module instance.
     *
     * @return Module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set the module instance.
     *
     * @param mixed $module
     *
     * @return $this
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get the module instance.
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set force status.
     *
     * @param bool|int $force
     *
     * @return $this
     */
    public function setForce($force)
    {
        $this->force = $force;

        return $this;
    }

    /**
     * Set the fix attribute
     *
     * @param boolean|int $fix
     *
     * @return $this
     */
    public function setFix($fix){
        $this->fix = $fix;

        return $this;
    }

    /**
     * Get if the configuration is set as fix or not
     *
     * @return boolean|int
     */
    public function getFix(){
        return $this->fix;
    }

    /**
     * Get the name of module will created. By default in studly case.
     *
     * @return string
     */
    public function getName()
    {
        return Str::studly($this->name);
    }

    /**
     * Get Path to be written
     *
     * @return void
     */
    public function getTargetPath() {
        return $this->module?->getPath() ?? false;
    }

    public function generatorConfig($generator)
    {
        return (new GeneratorPath($this->config->get(unusualBaseKey() . '.paths.generator.'.$generator)));
    }

    /**
     * Get the destination file path.
     *
     * @return string
     */
    abstract protected function generate():int;


}
