<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Console\Command;
use Nwidart\Modules\Module;
use Symfony\Component\Console\Input\InputArgument;
use Unusualify\Modularity\Facades\Modularity;

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
     * The migrator instance.
     *
     * @var \Illuminate\Database\Migrations\Migrator
     */
    protected $migrator;

    public function __construct()
    {
        parent::__construct();

        $this->migrator = app('migrator');
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /** @var Module $module */
        // $module = $this->laravel['unusual.modularity']->findOrFail($this->argument('module'));
        $module = Modularity::findOrFail($this->argument('module'));

        $basePattern = preg_quote(base_path('/'), '/');
        $relativeDir = preg_replace('/' . $basePattern . '/', '', $module->getDirectoryPath('Database/Migrations'));

        $migrationFiles = glob($module->getDirectoryPath('Database/Migrations/*.php'));
        $batches = [];

        $this->migrator->usingConnection(null, function () use (&$batches, $migrationFiles) {
            $batches = collect($this->migrator->getRepository()->getMigrationBatches())->reduce(function (array $acc, int $batch, string $migrationName) use ($migrationFiles) {
                foreach ($migrationFiles as $migrationFilePath) {
                    if ($migrationName == basename($migrationFilePath, '.php') && ! in_array($batch, $acc)) {
                        $acc[] = $batch;

                        break;
                    }
                }

                return $acc;
            }, $batches);

            rsort($batches);
        });

        try {
            foreach ($batches as $batch) {
                $this->call('migrate:rollback', [
                    '--path' => $relativeDir,
                    '--batch' => $batch,
                ]);
            }

            $this->comment(" {$module->getStudlyName()} Module was rollbacked.");

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
