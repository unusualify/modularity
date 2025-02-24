<?php

namespace Unusualify\Modularity\Console;

use Unusualify\Modularity\Facades\Filepond;

class FilepondFlushCommand extends BaseCommand
{
    protected $hidden = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:filepond:flush
        {days=7 : The number of days to keep temporary fileponds}
    ';

    protected $aliases = [];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush Fileponds';

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

        $this->info('Flushing Fileponds');

        Filepond::clearTemporaryFiles($this->argument('days'));

        Filepond::clearFolders();

        return 0;
    }
}
