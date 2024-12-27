<?php

namespace Unusualify\Modularity\Console;

use Unusualify\Modularity\Facades\Modularity;

class ModuleRemoveCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:remove:module {module}
    ';

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
        Modularity::disableCache();

        $moduleName = $this->argument('module');

        $module = Modularity::find($moduleName);

        // $this->call('optimize:clear');

        $this->call('modularity:migrate:rollback', [
            'module' => $moduleName,
        ]);

        Modularity::deleteModule($moduleName);

        $this->info("Module [{$moduleName}] removed completely!");

        return 0;
    }
}
