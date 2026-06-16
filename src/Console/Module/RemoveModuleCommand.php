<?php

namespace Unusualify\Modularous\Console\Module;

use Unusualify\Modularous\Console\BaseCommand;
use Unusualify\Modularous\Facades\Modularous;

class RemoveModuleCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularous:remove:module
                            {module : The name of the module to remove}
                            {--force : Force the command to run.}
                            {--dry-run : Dry run the command.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove completely a module.';

    protected $aliases = [
        'm:r:m',
        'mod:r:module',
        'unusual:remove:module',
    ];

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        $isProduction = $this->laravel->isProduction();
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');
        $moduleName = $this->argument('module');

        if ($isProduction && !$force && !$this->confirm('Are you sure you want to remove the module?')) {
            $this->info("Module removal cancelled.");
            return 0;
        }

        Modularous::disableCache();

        $this->call('modularous:migrate:rollback', [
            'module' => $moduleName,
            '--pretend' => $dryRun,
        ]);

        if ($dryRun) {
            $this->info("Module [{$moduleName}] would be removed completely!");
            return 0;
        }

        Modularous::deleteModule($moduleName);

        $this->info("Module [{$moduleName}] removed completely!");

        return 0;
    }
}
