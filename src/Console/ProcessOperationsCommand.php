<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Facades\Artisan;

class ProcessOperationsCommand extends BaseCommand
{
    protected $hidden = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:operations:process
        {--s|sync : run sync operations}
        {--a|async : run async operations}
        {--queue= : run queue operations}
        {--t|test : run operations in test mode}
        {--i|isolated : run operations in isolated mode}
        {--l|local : run local operations}';

    protected $aliases = [
        'mod:operations:process',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process Modularity Operations';

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
        $sync = $this->option('sync');
        $async = $this->option('async');
        $local = $this->option('local');
        $queue = $this->option('queue');
        $test = $this->option('test');
        $isolated = $this->option('isolated');

        $operationArguments = [
            '--tag' => $local
                ? ['modularity:local']
                : ['modularity'],
        ];

        if ($sync) {
            $operationArguments['--sync'] = true;
        } elseif ($async) {
            $operationArguments['--async'] = true;
        }

        if ($queue) {
            $operationArguments['--queue'] = $queue;
        }

        if ($test) {
            $operationArguments['--test'] = true;
        }

        if ($isolated) {
            $operationArguments['--isolated'] = true;
        }

        $this->info($local ? 'Running Modularity local operations' : 'Running Modularity production operations');

        Artisan::call('operations:process', $operationArguments, $this->output);

        $this->info('Modularity operations processed successfully as ' . ($local ? 'local' : 'production'));

        return 0;
    }
}
