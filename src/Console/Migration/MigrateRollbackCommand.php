<?php

namespace Unusualify\Modularous\Console\Migration;

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;
use Nwidart\Modules\Module;
use Unusualify\Modularous\Facades\Modularous;

class MigrateRollbackCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'modularous:migrate:rollback
                            {module : The name of the module to rollback}
                            {--pretend : Dump the SQL queries that would be run.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback migrations of the specified module';

    /**
     * The migrator instance.
     *
     * @var Migrator
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
        $module = Modularous::findOrFail($this->argument('module'));

        $basePattern = preg_quote(base_path('/'), '/');
        $relativeDir = preg_replace('/' . $basePattern . '/', '', $module->getDirectoryPath('Database/Migrations'));

        $migrationFiles = glob($module->getDirectoryPath('Database/Migrations/*.php'));
        $batches = [];

        $this->migrator->usingConnection(null, function () use (&$batches, $migrationFiles) {
            if (! $this->migrator->repositoryExists()) {
                return;
            }

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
                $params = [
                    '--path' => $relativeDir,
                    '--batch' => $batch,
                ];

                if ($this->option('pretend')) {
                    $params['--pretend'] = true;
                }

                $this->call('migrate:rollback', $params);
            }

            $label = $this->option('pretend') ? 'rollback (pretend)' : 'rollbacked';
            $this->comment(" {$module->getStudlyName()} Module was {$label}.");

        } catch (\Throwable $th) {
            $this->comment(" {$module->getStudlyName()} Module cannot be rollbacked.");
        }

        return 0;
    }
}
