<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;
use function Laravel\Prompts\{text,note,confirm,error,info,password,warning, alert, select};
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Install extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'unusual:install';

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

    protected function getOptions(): array
    {

        return array_merge([
            ['default', '--d', InputOption::VALUE_NONE, 'Use default options for super-admin authentication configuration'],
            // ['vendor-publish', '--vp', InputOption::VALUE_NONE, 'Only publish vendor assets, configurations and views'],
            ['db-process', '--db', InputOption::VALUE_NONE, 'Only handle database configuration processes'],
            // ['complete installment', '--complete', InputOption::VALUE_NONE, 'Complete default installment options'],
        ], unusualTraitOptions());
    }



    /**
     * Executes the console command.
     *
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle():int
    {
        if($this->option('db-process') == null){
            info(
                'Installment process consists of two(2) main operations.
                1. Publishing Config Files: Modularity Config files manages heavily table names, jwt configurations and etc.User should customize them after publishing in order to customize table names and other opeartions
                2. Database Operations and Creating Super Admin. DO NOT select this option if you have not published vendor files to theproject. This option will only dealing with db operations
                3. Complete Installment with default configurations (√ suggested)
                ');
                $operationType = select(
                    label: 'Select Operation',

                    options: [
                        'vp' => 'Only Vendor Publish (Config Files, Assets and Views)',
                        'db' => 'Only Database Operations',
                        'complete' => 'Complete Installment with defaults'
                    ],
                    default: 'complete',

                );
        }else{
            $operationType = 'db';
        }

        $dbOperations = [
            'checkDbConnection',
            'makeMigrations',
            'seedData',
            'createSuperAdmin',
        ];
        $vendorOperations = [
            'publishConfig',
            'publishAssets',
            'publishViews',
        ];



        $operations = array_merge_conditional(
            [],
            [$vendorOperations, $dbOperations,],
            $operationType === 'complete' || $operationType === 'vp',
            $operationType === 'complete' || $operationType === 'db',
        );


        $bar = $this->output->createProgressBar(count($operations));

        $bar->start();

        foreach ($operations as $process) {
            $this->$process();
            $this->newLine();

        }



        $bar->finish();
        $this->newLine();


        if($operationType == 'vp'){
            $this->newLine();
            info('Vendor publish is done √. Config files can be customized now');
            warning('Run php artisan unusual:install --db to run installation with db operations');
        }
        info('Process is done.');
        return 0;
    }



    /**
     * Calls the command responsible for creation of the default superadmin user.
     *
     * @return void
     */
    private function createSuperAdmin()
    {
        info("\t Creating super-admin account");

        if (!$this->option('no-interaction')) {
            $this->call('unusual:create:superadmin', [
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
        info("\t Publishing config files");

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
        info("\t Publishing default assets");

        $this->call('vendor:publish', [
            '--provider' => 'Unusualify\Modularity\LaravelServiceProvider',
            '--tag' => 'assets',
        ]);
    }

    private function publishViews(){

        info("\t Publishing default views");


        $this->call('vendor:publish', [
            '--provider' => 'Unusualify\Modularity\LaravelServiceProvider',
            '--tag' => 'views'
        ]);
    }

    private function checkDbConnection(){
        $this->newLine();
        info("\t Checking database connection");

        if(!database_exists()){
            warning('Could not connect to the database, please check your configuration:' . "\n");
            return 0;
        }
        info('Database connection is fine.');
    }

    private function makeMigrations(){
        info("\t Making required migrations");
        $this->call('migrate');

    }

    /**
     *
     * Defined Seeders are
     *
     *  - DefaultRolesSeeder
     *  - DefaultPermissionsSeeder
     * */

    private function seedData(){
        info("\tSeeding required data");
        $this->call('db:seed', [
            '--class' => 'Unusualify\Modularity\Database\Seeders\DefaultDatabaseSeeder',
        ]);
    }



}
