<?php

namespace Unusualify\Modularous\Console\Migration;

use Illuminate\Console\Command;
use Nwidart\Modules\Module;
use Unusualify\Modularous\Facades\Modularous;

class MigrateRefreshCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'modularous:migrate:refresh
                            {module : The name of the module to refresh}
                            {--pretend : Dump the SQL queries that would be run.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh migrations of the specified module';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /** @var Module $module */
        $module = Modularous::findOrFail($this->argument('module'));

        try {
            $params = [
                'module' => $module->getName(),
                '--pretend' => $this->option('pretend'),
            ];
            $this->call('modularous:migrate:rollback', $params);
            $this->call('modularous:migrate', $params);
        } catch (\Throwable $th) {
            $this->comment(" {$module->getStudlyName()} Module cannot be refreshed.");
        }

        return 0;
    }
}
