<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Unusualify\Modularity\Support\RegexReplacement;

class ReplaceRegularExpressionCommand extends BaseCommand
{
    protected $hidden = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:replace:regex
        {path : The path to the files}
        {pattern : The pattern to replace}
        {data : The data to replace}
        {--d|directory= : The directory pattern}
        {--p|pretend : Dump files that would be modified}
    ';

    protected $aliases = [
        'mod:replace:regex'
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Replace matches';

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

        // dd(
        //     get_class($this->output),
        //     $this->output->getVerbosity(),
        //     $this->output->isVerbose(),
        //     $this->output->isQuiet(),
        //     $this->output->isVerbose(),
        //     $this->output->isVeryVerbose(),
        //     $this->output->isDebug(),
        // );
        // // Set verbosity level based on options
        // $verbose = null;
        // if ($this->option('vvv')) {
        //     $this->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        // } elseif ($this->option('vv')) {
        //     $this->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
        // } elseif ($this->option('v')) {
        //     $this->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        // }

        $path = $this->argument('path');
        $pattern = $this->argument('pattern');
        $data = $this->argument('data');

        $directory_pattern = $this->option('directory') ?? '**/*.php';
        $pretend = $this->option('pretend');

        if (!Str::startsWith($path, '/')) {
            $path = base_path($path);
        }

        if (!File::exists($path)) {
            $this->error('The path does not exist: ' . $path);
            return 1;
        }

        if (!is_string($pattern)) {
            $this->error('The pattern is not a string');
            return 1;
        }

        try {
            // $pattern = '/'.preg_quote($pattern).'/';
            $pattern = '/'.$pattern.'/';

            preg_match($pattern, '');
        } catch (\Exception $e) {
            $this->error('Invalid regular expression pattern: ' . $e->getMessage());
            return 1;
        }


        if (!is_string($data)) {
            $this->error('The data is not a string');
            return 1;
        }


        if (!$this->output->isQuiet()) {
            if (!$pretend) {
                $this->info('Starting regex replacement...');
            } else {
                $this->info('Pretending to replace regex...');
            }
        }

        // dd($this->output->getVerbosity());
        $replacement = new RegexReplacement(
            $path,
            $pattern,
            $data,
            $directory_pattern,
            $this->output->isQuiet(),
            $this->output->getVerbosity(),
            $pretend,
        );

        $replacement->run();

        if (!$this->output->isQuiet() && !$pretend) {
            $this->info('Replacement completed.');
        }

        return 0;
    }
}
