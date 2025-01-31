<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Console\Command;
use Nwidart\Modules\Module;
use Symfony\Component\Console\Input\InputArgument;
use Unusualify\Modularity\Facades\Modularity;

class EnableCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'modularity:route:enable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable the specified module route.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {

        /** @var Module $module */
        $module = Modularity::findOrFail($this->argument('module'));

        $module->setModuleActivator($this->argument('module'));

        $route = $this->argument('route');

        if ($module->isDisabledRoute($route)) {
            $module->enableRoute($route);

            $this->info("Module's Route [{$route}] enabled successful.");
        } else {
            $this->comment("Module's Route [{$route}] has already enabled.");
        }

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
            ['module', InputArgument::REQUIRED, 'Module name.'],
            ['route', InputArgument::REQUIRED, 'Route name.'],
        ];
    }
}
