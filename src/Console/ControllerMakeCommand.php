<?php

namespace Unusualify\Modularity\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Nwidart\Modules\Support\Stub;
use Illuminate\Support\Str;
use Nwidart\Modules\Support\Config\GeneratorPath;
use Unusualify\Modularity\Facades\Modularity;

class ControllerMakeCommand extends BaseCommand
{


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'unusual:make:controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Controller with repository for specified module.';

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

        $module = Modularity::findOrFail($this->getModuleName());

        return (new Stub($this->getStubName(), [
            'NAMESPACE'                 => $this->getClassNamespace($module),
            'BASE_CONTROLLER_NAMESPACE' => $this->baseConfig('base_controller'),
            'CLASS'                     => $this->getControllerNameWithoutNamespace(),
            'BASE_CONTROLLER'           => get_class_short_name( $this->baseConfig('base_controller') ),
            'MODULE'                    => $module->getStudlyName(),
            'STUDLY_MODULE_NAME'        => $module->getStudlyName(),
            'ROUTE_NAME'                => $this->getStudlyName($name),
        ]))->render();
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = Modularity::getModulePath($this->getModuleName());

        $controllerPath = new GeneratorPath( $this->baseConfig('paths.generator.route-controller') );

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

    public function getDefaultNamespace() : string
    {
        return $this->baseConfig('paths.generator.route-controller.namespace') ?:
            $this->baseConfig('paths.generator.route-controller.path', 'Http\Controllers');
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
        return '/route-controller.stub';
    }


}
