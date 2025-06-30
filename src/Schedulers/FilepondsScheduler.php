<?php

namespace Unusualify\Modularity\Schedulers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Unusualify\Modularity\Facades\Filepond;

class FilepondsScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:fileponds:scheduler
        {--days=7 : The number of days to keep temporary fileponds}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean temporary fileponds';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $days = $this->option('days');
        $temporaryFileponds = Filepond::clearTemporaryFiles($days);

        Filepond::clearFolders();

        Log::channel('scheduler')
            ->info("Modularity: Deleted {$temporaryFileponds->count()} expired temporary fileponds in last {$days} days");

        // $this->info(now()->format('Y-m-d H:i:s') . ' - Modularity: Temporary fileponds cleaned');
    }
}
