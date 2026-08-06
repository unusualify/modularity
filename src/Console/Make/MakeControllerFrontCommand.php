<?php

namespace Unusualify\Modularous\Console\Make;

use Illuminate\Support\Str;
use Nwidart\Modules\Support\Config\GeneratorPath;
use Nwidart\Modules\Support\Stub;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Unusualify\Modularous\Console\BaseCommand;
use Unusualify\Modularous\Facades\Modularous;

class MakeControllerFrontCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'modularous:make:controller:front';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Front Controller for the specified module route.';

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
            ['addCmr', null, InputOption::VALUE_NONE, 'Extend CmsController (CMR / signed public preview).'],
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite the file if it already exists.'],
            ['test', null, InputOption::VALUE_NONE, 'Print stub contents without writing.'],
        ];
    }

    /**
     * @return mixed
     */
    protected function getTemplateContents()
    {
        $name = $this->argument('name');

        $module = Modularous::findOrFail($this->getModuleName());

        return (new Stub($this->getStubName(), [
            'NAMESPACE' => $this->getClassNamespace($module),
            'STUDLY_MODULE_NAME' => $module->getStudlyName(),
            'LOWER_MODULE_NAME' => $module->getLowerName(),
            'STUDLY_NAME' => $this->getStudlyName($name),
            'LOWER_NAME' => $this->getLowerName($name),
            'CLASS' => $this->getControllerNameWithoutNamespace(),
            'ROUTE_NAME' => $this->getStudlyName($name),
        ]))->render();
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath()
    {
        $path = Modularous::getModulePath($this->getModuleName());

        $controllerPath = new GeneratorPath($this->baseConfig('paths.generator.route-controller-front'));

        return $path . $controllerPath->getPath() . '/' . $this->getFileName() . 'Controller.php';
    }

    /**
     * @return array|string
     */
    protected function getControllerName()
    {
        $controller = Str::studly($this->argument('name'));

        if (Str::contains(mb_strtolower($controller), 'controller') === false) {
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
     * Get head string of path for namespace
     */
    public function getDefaultNamespace(): string
    {
        return $this->baseConfig('paths.generator.route-controller-front.namespace') ?:
            $this->baseConfig('paths.generator.route-controller-front.path', 'Https\Controllers\Front');
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        return Str::studly($this->argument('name'));
    }

    protected function getStubName(): string
    {
        if ($this->hasOption('addCmr') && $this->option('addCmr')) {
            return '/route-controller-front-cms.stub';
        }

        return '/route-controller-front.stub';
    }
}
