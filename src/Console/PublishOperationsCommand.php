<?php

namespace Unusualify\Modularity\Console;

class PublishOperationsCommand extends BaseCommand
{
    protected $hidden = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:publish:operations';

    protected $aliases = [];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish Operations';

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
        $this->call('vendor:publish', [
            '--provider' => 'Unusualify\\Modularity\\LaravelServiceProvider',
            '--tag' => 'operations',
        ]);

        return 0;
    }
}
