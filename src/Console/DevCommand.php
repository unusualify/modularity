<?php

namespace Unusualify\Modularity\Console;

class DevCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unusual:dev {--noInstall}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Hot reload unusual assets with custom Vue component, configuration";

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle() :int
    {
        $this->call('unusual:build', [
            '--hot' => true,
            '--noInstall' => $this->option('noInstall'),
        ]);

        return 0;
    }
}
