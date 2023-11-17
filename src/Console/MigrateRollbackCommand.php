<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Console\Command;
use Nwidart\Modules\Module;
use Unusualify\Modularity\Facades\Modularity;
use Symfony\Component\Console\Input\InputArgument;

class MigrateRollbackCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'unusual:migrate:rollback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback migrations of the specified module';

    /**
     * Execute the console command.
     */
    public function handle() : int
    {
        /** @var Module $module */
        // $module = $this->laravel['unusual.modularity']->findOrFail($this->argument('module'));
        $module = Modularity::findOrFail($this->argument('module'));

        try {
            $this->call('migrate:rollback', [
                '--path' => config('modules.namespace') . "/{$module->getStudlyName()}/Database/Migrations"
            ]);

        } catch (\Throwable $th) {
            $this->comment(" {$module->getStudlyName()} Module cannot be rollbacked.");

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
        ];
    }
}
