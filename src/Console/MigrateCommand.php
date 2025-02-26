<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Console\Command;
use Nwidart\Modules\Module;
use Symfony\Component\Console\Input\InputArgument;
use Unusualify\Modularity\Facades\Modularity;

class MigrateCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'modularity:migrate';

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
        $module = Modularity::findOrFail($this->argument('module'));

        $this->call('migrate', [
            '--path' => $module->getDirectoryPath('Database/Migrations', true),
        ]);
        try {

        } catch (\Throwable $th) {
            $this->comment(" {$module->getStudlyName()} Module cannot migrated.");

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
