<?php

namespace OoBook\CRM\Base\Console;

use Illuminate\Console\Command;
use Nwidart\Modules\Module;
use Symfony\Component\Console\Input\InputArgument;

class MigrateRefreshCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'unusual:migrate:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh migrations of the specified module';

    /**
     * Execute the console command.
     */
    public function handle() : int
    {
        /** @var Module $module */
        $module = $this->laravel['unusual.repository']->findOrFail($this->argument('module'));

        try {
            $this->call('migrate:refresh', [
                '--path' => config('modules.namespace') . "/{$module->getStudlyName()}/Database/Migrations"
            ]);

        } catch (\Throwable $th) {
            $this->comment(" {$module->getStudlyName()} Module cannot be refreshed.");

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
