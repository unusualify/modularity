<?php

namespace Unusualify\Modularity\Console;

use Unusualify\Modularity\Facades\Modularity;

class FlushCommand extends BaseCommand
{
    protected $hidden = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:flush';

    protected $aliases = [];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush Modularity caches';

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
        Modularity::clearCache();

        $this->call('modularity:cache:versions');

        $this->info('Modularity caches flushed');

        return 0;
    }
}
