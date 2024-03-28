<?php

namespace Unusualify\Modularity\Generators;

use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Nwidart\Modules\FileRepository;
use Unusualify\Modularity\Module;
use Unusualify\Modularity\Traits\ReplacementTrait;

class StubsGenerator extends Generator
{
    public $exceptStubs = [];

    public $onlyStubs = [];

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

        parent::__construct($name, $config, $filesystem, $console, $module);
    }

    public function setOnly(array $only){
        $this->onlyStubs = $only;

        return $this;
    }

    public function setExcept(array $except){
        $this->exceptStubs = $except;

        return $this;
    }

    /**
     * Generate the module.
     */
    public function generate() : int
    {
        $name = $this->getName();

        if ($this->module->getRouteConfig($name)) {
            if ($this->force) {

            } else if(!$this->fix){
                $this->console->error("Module Route [{$name}] files already exist!");

                return E_ERROR;
            }
        }

        $this->generateFiles();

        $this->console->info("Route [{$name}] stubs " .($this->fix ? 'fixed' : 'created'). " successfully.");

        return 0;
    }

    /**
     * Generate the files.
     */
    public function generateFiles()
    {
        foreach ($this->getFiles() as $stub => $file) {

            $path = $this->module->getPath(). '/' . $file;

            $path = $this->replaceString($path);

            if (!$this->filesystem->isDirectory($dir = dirname($path))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }

            if(!file_exists($path) || $this->forcibleStub($stub)){
                $this->filesystem->put($path, $this->getStubContents($stub));

                $this->console->info("Created : {$path}");
            }
        }
    }

    public function forcibleStub($stub)
    {
        if($this->force)
            return true;

        if($this->fix){

            if(!empty($this->onlyStubs))
                return in_array($stub, $this->onlyStubs);

            if(!empty($this->exceptStubs))
                return !in_array($stub, $this->exceptStubs);
            dd($stub);
            return true;
        }

        return false;
    }


}
