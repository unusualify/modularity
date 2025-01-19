<?php

namespace Unusualify\Modularity\Schedulers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Unusualify\Modularity\Entities\TemporaryFilepond;

class CleanTemporaryFilepondsScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:clean-temporary-fileponds';

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
        $query = TemporaryFilepond::where('created_at', '<', now()->subDay());
        $count = $query->count();
        $query->delete();

        Log::channel('scheduler')
            ->info("Modularity: Deleted {$count} expired temporary fileponds");


        // $this->info(now()->format('Y-m-d H:i:s') . ' - Modularity: Temporary fileponds cleaned');
    }
}
