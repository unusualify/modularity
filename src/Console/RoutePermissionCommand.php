<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Nwidart\Modules\Support\Stub;
use Illuminate\Support\Str;
use Unusualify\Modularity\Support\Decomposers\ValidatorParser;
use Nwidart\Modules\Support\Config\GeneratorPath;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Generators\RouteGenerator;

class RoutePermissionCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'unusual:create:route:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create permissions for routes';

    protected $argumentName = 'permissions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle() : int
    {

        // if (parent::handle() === E_ERROR) {
        //     return E_ERROR;
        // }
        $route = $this->argument('route');
        // dd($route);
        $routeGenerator = new RouteGenerator($route);
        $routeGenerator->createRoutePermissions();
        // dd($routeGenerator->createRoutePermissions());
        $this->info('Permissions created successfully!');

        return 0;
    }
    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['route', InputArgument::REQUIRED, 'The name of the route.'],
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
            ['route', null, InputOption::VALUE_OPTIONAL, 'The validation rules.', null],
        ];
    }



    /**
     * @return string
     */
    private function getRules()
    {
        $rule_schema = $this->option('rules');

        return (new ValidatorParser($rule_schema))->toReplacement();

    }





}
