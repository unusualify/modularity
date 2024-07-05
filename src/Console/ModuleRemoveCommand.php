<?php

namespace Unusualify\Modularity\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Unusualify\Modularity\Facades\Modularity;

class ModuleRemoveCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unusual:remove:module {module}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Remove completely a module.";

    protected $aliases= [
        'u:r:m',
        'modularity:remove:module',
    ];

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle() :int
    {
        $moduleName = $this->argument('module');
        $module = Modularity::find($moduleName);

        // $this->call('optimize:clear');

        $this->call('unusual:migrate:rollback', [
            'module' => $moduleName,
        ]);

        Modularity::deleteModule($moduleName);

        $this->info("Module [{$moduleName}] removed completely!");

        return 0;
    }
}
