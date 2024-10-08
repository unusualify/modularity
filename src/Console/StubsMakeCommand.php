<?php

namespace Unusualify\Modularity\Console;

use Nwidart\Modules\Support\Stub;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Unusualify\Modularity\Generators\StubsGenerator;

class StubsMakeCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'modularity:make:stubs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create stub files for route.';

    protected $defaultReject = true;

    protected $isAskable = false;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Stub::setBasePath(dirname(__FILE__) . '/stubs');
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $module = $this->argument('module');
        $route = $this->argument('route');

        $success = true;

        $code = with(new StubsGenerator($route))
            ->setFilesystem($this->laravel['files'])
            ->setConfig($this->laravel['config'])
            ->setConsole($this)
            ->setForce($this->option('force'))
            ->setModule($module)
            ->setFix($this->option('fix'))
            ->setOnly($this->option('only') ? explode(',', $this->option('only')) : [])
            ->setExcept($this->option('except') ? explode(',', $this->option('except')) : [])
            ->generate();

        if ($code === E_ERROR) {
            $success = false;
        }

        return $success ? 0 : E_ERROR;
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
            ['only', null, InputOption::VALUE_OPTIONAL, 'get only stubs'],
            ['except', null, InputOption::VALUE_OPTIONAL, 'get except stubs'],
            ['force', '--f', InputOption::VALUE_NONE, 'Force the operation to run when the route files already exist.'],
            ['fix', null, InputOption::VALUE_NONE, 'Fixes the model config errors'],
        ];
    }
}
