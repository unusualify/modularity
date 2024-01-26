<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;

class Install extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unusual:install {--default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install unusual-modularity into your Laravel application';

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var DatabaseManager
     */
    protected $db;

    /**
     * @param Filesystem $files
     * @param DatabaseManager $db
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
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle():int
    {

        $operations = [
            'checkDbConnection',
            'makeMigrations',
            'seedingData',
            'publishConfig',
            'publishAssets',
            'publishViews',
            'createSuperAdmin',
        ];


        $bar = $this->output->createProgressBar(count($operations));

        $bar->start();

        foreach ($operations as $process) {
            $this->$process();
            $this->newLine();
            $bar->advance();
        }

        $bar->finish();

        $this->info('Installation is done.');

        return 0;
    }



    /**
     * Calls the command responsible for creation of the default superadmin user.
     *
     * @return void
     */
    private function createSuperAdmin()
    {
        $this->info("\t Creating super-admin account");

        if (!$this->option('no-interaction')) {
            $this->call('unusual:superadmin', [
                '--default' => $this->option('default'),
            ]);
        }
    }

    /**
     * Publishes the package configuration files.
     *
     * @return void
     */
    private function publishConfig()
    {
        $this->info("\t Publishing config files");

        $this->call('vendor:publish', [
            '--provider' => 'Unusualify\Modularity\LaravelServiceProvider',
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
        $this->info("\t Publishing default assets");

        $this->call('vendor:publish', [
            '--provider' => 'Unusualify\Modularity\LaravelServiceProvider',
            '--tag' => 'assets',
        ]);
    }

    private function publishViews(){

        $this->info("\t Publishing default views");


        $this->call('vendor:publish', [
            '--provider' => 'Unusualify\Modularity\LaravelServiceProvider',
            '--tag' => 'views'
        ]);
    }

    private function checkDbConnection(){
        $this->newLine();
        $this->info("\t Checking database connection");

        if(!database_exists()){
            $this->error('Could not connect to the database, please check your configuration:' . "\n" . $e);
            return 0;
        }
        $this->line('Database connection is fine.');
    }

    private function makeMigrations(){
        $this->info("\t Making required migrations");
        $this->call('migrate');

    }

    /**
     *
     * Defined Seeders are
     *
     *  - DefaultRolesSeeder
     *  - DefaultPermissionsSeeder
     * */

    private function seedingData(){
        $this->info("\tSeeding required data");
        $this->call('db:seed', [
            '--class' => 'Unusualify\Modularity\Database\Seeders\DefaultDatabaseSeeder',
        ]);
    }
}
