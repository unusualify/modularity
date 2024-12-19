<?php

namespace Unusualify\Modularity\Console;

class GetVersionCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:get:version
        {--p|package= : The package}';

    protected $aliases = [
        'mod:g:ver',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Version of a Package';

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        $package = $this->option('package');

        $tag = get_package_version($package);

        $this->info("{$package} version is {$tag}");

        return 0;
    }
}
