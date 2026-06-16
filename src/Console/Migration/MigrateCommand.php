<?php

namespace Unusualify\Modularous\Console\Migration;

use Illuminate\Console\Command;
use Nwidart\Modules\Module;
use Symfony\Component\Console\Input\InputArgument;
use Unusualify\Modularous\Facades\Modularous;

class MigrateCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'modularous:migrate
                            {module : The name of the module to migrate}
                            {--pretend : Dump the SQL queries that would be run.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate the specified module';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /** @var Module $module */
        $module = Modularous::findOrFail($this->argument('module'));

        try {
            $params = [
                '--path' => $module->getDirectoryPath('Database/Migrations', true),
            ];

            if ($this->option('pretend')) {
                $params['--pretend'] = true;
            }
            $this->call('migrate', $params);

        } catch (\Throwable $th) {
            $this->comment(" {$module->getStudlyName()} Module cannot migrated.");

        }

        return 0;
    }

}
