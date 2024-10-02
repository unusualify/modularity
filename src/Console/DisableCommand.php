<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Unusualify\Modularity\Facades\Modularity;

class DisableCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'unusual:route:disable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable the specified module\'s route.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /** @var Module $module */
        // $module = $this->laravel['unusual.modularity']->findOrFail($this->argument('module'));
        $module = Modularity::findOrFail($this->argument('module'));

        $module->setModuleActivator($this->argument('module'));

        $route = $this->argument('route');

        if ($module->isEnabledRoute($route)) {
            $module->disableRoute($route);

            $this->info("Module's Route [{$route}] disabled successful.");
        } else {
            $this->comment("Module's Route [{$route}] has already disabled.");
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
