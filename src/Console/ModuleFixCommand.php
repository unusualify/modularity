<?php

namespace Unusualify\Modularity\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Unusualify\Modularity\Facades\Modularity;

class ModuleFixCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'modularity:fix:module';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes the un-desired changes on module\'s config file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $moduleName = studlyName($this->argument('module'));
        $routes = Modularity::find($moduleName)->getRoutes();

        foreach ($routes as $key => $routeName) {

            $this->call('modularity:make:route', [
                'module' => $moduleName,
                'route' => $routeName,
                '--fix' => true,
            ]);

        }

        return 0;
    }

    protected function getArguments()
    {
        return [
            ['module', InputArgument::REQUIRED, 'The name of module will be used.'],
            ['route', InputArgument::OPTIONAL, 'The name of the route.', null],
        ];
    }

    protected function getOptions()
    {
        return array_merge([
            ['migration', null, InputOption::VALUE_NONE, 'Fix will create migrations'],

        ], unusualTraitOptions());
    }
}
