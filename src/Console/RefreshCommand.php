<?php

namespace OoBook\CRM\Base\Console;

class RefreshCommand extends BaseCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'unusual:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Move new unusual front sources";

    /**
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle() :int
    {
        $this->publishAssets();
        $this->call('cache:clear');
        $this->call('view:clear');

        return 0;
    }

    /**
     * Publishes the package frontend assets.
     *
     * @return void
     */
    private function publishAssets()
    {
        $this->call('vendor:publish', [
            '--provider' => 'OoBook\CRM\Base\LaravelServiceProvider',
            '--tag' => 'assets',
            '--force' => true,
        ]);
    }

}
