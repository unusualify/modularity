<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;

class SetupCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'modularity:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup system environments';

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var DatabaseManager
     */
    protected $db;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files, DatabaseManager $db)
    {
        parent::__construct();

        $this->files = $files;
        $this->db = $db;
    }

    /**
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        //check the database connection before installing
        try {
            $this->db->connection()->getPdo();
        } catch (\Exception $e) {
            $this->error('Could not connect to the database, please check your configuration:' . "\n" . $e);

            return 0;
        }

        // $this->call('migrate');
        $this->publishConfig();
        $this->publishAssets();
        $this->createAdmin();
        $this->info('All good!');

        return 0;
    }

    /**
     * Publishes the package configuration files.
     *
     * @return void
     */
    private function publishConfig()
    {
        $this->call('vendor:publish', [
            '--provider' => 'Unusualify\Modularity\Providers\UnusualProvider',
            '--tag' => 'config',
        ]);
    }

    /**
     * Publishes the package frontend assets.
     *
     * @return void
     */
    private function publishAssets()
    {
        $this->call('vendor:publish', [
            '--provider' => 'Unusualify\Modularity\Providers\UnusualProvider',
            '--tag' => 'assets',
            '--force' => true,
        ]);
    }

    /**
     * Calls the command responsible for creation of the default superadmin user.
     *
     * @return void
     */
    private function createAdmin()
    {
        if (! $this->option('no-interaction')) {
            $this->call('modularity:superadmin');
        }
    }
}
