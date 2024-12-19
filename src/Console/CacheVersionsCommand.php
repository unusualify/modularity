<?php

namespace Unusualify\Modularity\Console;

class CacheVersionsCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:cache:version';

    protected $aliases = [
        'mod:cache:ver',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache package versions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        // handle command

        set_env_file('APP_VERSION', get_package_version());
        set_env_file('MODULARITY_VERSION', get_package_version('unusualify/modularity'));
        set_env_file('PAYABLE_VERSION', get_package_version('unusualify/payable'));
        set_env_file('SNAPSHOT_VERSION', get_package_version('oobook/snapshot'));

        $this->info('Package versions cached');

        return 0;

    }
}
