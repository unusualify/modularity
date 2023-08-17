<?php

namespace OoBook\CRM\Base\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Nwidart\Modules\Support\Stub;
use Illuminate\Support\Str;
use Nwidart\Modules\Support\Config\GeneratorPath;

class ControllerAPIMakeCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'unusual:make:controller:api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create API Controller with repository for specified module.';

    protected $argumentName = 'name';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['module', InputArgument::REQUIRED, 'The name of module will be used.'],
            ['name', InputArgument::REQUIRED, 'The name of the controller class.'],

        ];
    }
    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }

    /**
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $name = $this->argument('name');

        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return (new Stub($this->getStubName(), [
            'NAMESPACE'             => $this->getClassNamespace($module),
            'MODULE_STUDLY_NAME'    => $module->getStudlyName(),
            'MODULE_LOWER_NAME'     => $module->getLowerName(),
            'STUDLY_NAME'           => $this->getStudlyName($name),
            'LOWER_NAME'            => $this->getLowerName($name),
            'CLASS'                 => $this->getControllerNameWithoutNamespace(),
        ]))->render();
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $controllerPath = new GeneratorPath( $this->baseConfig('paths.generator.route-controller-api') );

        return $path . $controllerPath->getPath() . '/' . $this->getFileName() . 'Controller.php';
    }

    /**
     * @return array|string
     */
    protected function getControllerName()
    {
        $controller = Str::studly($this->argument('name'));

        if (Str::contains(strtolower($controller), 'controller') === false) {
            $controller .= 'Controller';
        }

        return $controller;
    }


    /**
     * @return array|string
     */
    private function getControllerNameWithoutNamespace()
    {
        return class_basename($this->getControllerName());
    }

    /**
     *
     * Get head string of path for namespace
     *
     * @return string
     */
    public function getDefaultNamespace() : string
    {
        // dd($this->baseConfig('paths.generator.controller-api.namespace'), $this->baseConfig('paths.generator.controller-api.path', 'Https\Controllers\API'));
        return $this->baseConfig('paths.generator.route-controller-api.namespace') ?:
            $this->baseConfig('paths.generator.route-controller-api.path', 'Https\Controllers\API');
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        return Str::studly($this->argument('name'));
    }


    /**
     * @return string
     */
    protected function getStubName(): string
    {
        return '/route-controller-api.stub';
    }


}
